<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\RubricScore;
use App\Models\EvaluationL3Assignment;
use App\Models\CourseMaterial;
use App\Models\UserCertificate;
use App\Models\CertificateTemplate;
use App\Services\MediaGeneratorService;

class RubricController extends Controller
{
    /**
     * Store expert's rubric assessment and finalize review
     */
    public function store(Request $request, EvaluationL3Assignment $assignment, MediaGeneratorService $mediaService)
    {
        $validated = $request->validate([
            'scores' => 'required|array', // e.g. ["Ketepatan" => 5, "Kreativitas" => 4, "Dokumentasi" => 5]
            'status' => 'required|in:approved,rejected',
            'expert_notes' => 'nullable|string',
        ]);

        // Automatically calculate total_score
        $totalScore = array_sum($validated['scores']);

        DB::transaction(function () use ($assignment, $validated, $totalScore, $mediaService) {
            // 1. Save Rubric Score
            RubricScore::updateOrCreate(
                ['assignment_id' => $assignment->id],
                [
                    'details_json' => $validated['scores'],
                    'total_score' => $totalScore
                ]
            );

            // 2. Update Assignment status and notes (Replacing the legacy/separate expert_notes system)
            $assignment->update([
                'status' => $validated['status'],
                'expert_notes' => $validated['expert_notes'],
                'reviewed_at' => now(),
                'expert_id' => auth()->id()
            ]);

            // 3. THE CERTIFICATE GATE LOGIC
            if ($validated['status'] === 'approved') {
                $enrollment = $assignment->enrollment;
                $courseId = $enrollment->course_id;

                // Use the new Zero-Leak Gatekeeper logic (Pillar 5)
                if ($enrollment->checkCertificateEligibility()) {
                    // Check if certificate already exists
                    $existingCert = UserCertificate::where('user_id', $enrollment->user_id)
                                        ->where('course_id', $courseId)
                                        ->first();

                    if (!$existingCert) {
                        // Get default or specific template
                        $template = CertificateTemplate::where('is_default', true)->first();

                        $cert = UserCertificate::create([
                            'user_id' => $enrollment->user_id,
                            'course_id' => $courseId,
                            'template_id' => $template ? $template->id : null,
                            'certificate_code' => strtoupper(Str::random(10)),
                            'issued_at' => now(),
                        ]);

                        // Generate the PDF
                        try {
                            $mediaService->generateCertificate($cert);
                        } catch (\Exception $e) {
                            \Log::error('Failed to generate certificate PDF: ' . $e->getMessage());
                        }
                    }
                }
            }
        });

        $message = $validated['status'] === 'approved' 
                    ? 'Review finalized and approved! Total Score: ' . $totalScore 
                    : 'Revision requested! Feedback sent to participant.';

        return back()->with('success', $message);
    }
}
