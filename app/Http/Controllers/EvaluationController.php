<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\EvaluationL1Feedback;
use App\Models\EvaluationL2Test;
use App\Models\EvaluationL3Assignment;
use App\Models\TestQuestion;
use App\Notifications\AssignmentSubmitted;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EvaluationController extends Controller
{
    /**
     * Submit Pre-test or Post-test
     */
    public function submitTest(Request $request, Enrollment $enrollment, string $type)
    {
        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        if (!in_array($type, ['pretest', 'posttest'])) {
            return back()->with('error', 'Tipe evaluasi tidak valid.');
        }

        $questions = TestQuestion::where('course_id', $enrollment->course_id)
            ->where('type', $type)
            ->get();

        if ($questions->count() === 0) {
            return back()->with('error', 'Tidak ada pertanyaan untuk evaluasi ini. Silakan tambahkan soal terlebih dahulu.');
        }

        $correctCount = 0;
        foreach ($questions as $question) {
            $userAnswer = $validated['answers'][$question->id] ?? null;
            if ($userAnswer === $question->correct_key) {
                $correctCount++;
            }
        }

        $score = round(($correctCount / $questions->count()) * 100);

        // Save evaluation record
        EvaluationL2Test::updateOrCreate(
            ['enrollment_id' => $enrollment->id, 'type' => $type],
            ['answers' => $validated['answers'], 'score' => $score]
        );

        // Process the result using the logic inside the Enrollment model
        $enrollment->processTestResult($type, $score);

        return back()->with('success', ucfirst($type) . ' submitted successfully!');
    }

    /**
     * Submit L1 Feedback (Reaction)
     */
    public function submitFeedback(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ]);

        EvaluationL1Feedback::create([
            'enrollment_id' => $enrollment->id,
            'rating' => $validated['rating'],
            'review' => $validated['review'],
        ]);

        $isFirstTime = $enrollment->status === 'post_test_done';
        $enrollment->update([
            'status' => 'l1_done',
            'points' => $isFirstTime ? ($enrollment->points ?? 0) + 20 : ($enrollment->points ?? 0)
        ]);

        return back()->with('success', 'Thank you for your feedback!');
    }

    /**
     * Submit L3 Assignment (Behavior)
     */
    public function submitAssignment(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'submission_type' => 'required|in:file,link',
            'file' => 'required_if:submission_type,file|file|mimes:pdf,png,jpg,jpeg|max:10240',
            'link_url' => 'required_if:submission_type,link|nullable|url',
            'description' => 'nullable|string',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('assignments', 'public');
        }

        $assignment = EvaluationL3Assignment::create([
            'enrollment_id' => $enrollment->id,
            'submission_type' => $validated['submission_type'],
            'file_path' => $filePath,
            'link_url' => $validated['link_url'] ?? null,
            'description' => $validated['description'],
            'status' => 'pending',
        ]);

        $enrollment->update(['status' => 'l3_submitted']);

        // Notify experts and owners in the academy
        $experts = $enrollment->course->academy->users()
            ->whereIn('role', ['expert', 'owner'])
            ->get();
            
        foreach($experts as $expert) {
            $expert->notify(new AssignmentSubmitted($assignment));
        }

        return back()->with('success', 'Assignment submitted to expert!');
    }

    /**
     * Claim attendance using a session code
     */
    public function claimAttendance(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        if (!$enrollment->course->attendance_code) {
            return back()->with('error', 'No attendance code set for this course. Contact your expert.');
        }

        if (strtoupper(trim($request->code)) === strtoupper(trim($enrollment->course->attendance_code))) {
            $isFirstTime = !$enrollment->attended_at;
            $enrollment->update([
                'attended_at'       => now(),
                'attendance_status' => 'attended',
                'status'            => 'attended', // ← critical: unlock post-test
                'points'            => $isFirstTime ? ($enrollment->points ?? 0) + 30 : ($enrollment->points ?? 0),
            ]);

            return back()->with('success', 'Attendance confirmed! Proceeding to post-test evaluation.');
        }

        return back()->with('error', 'Invalid attendance code. Please check with your expert and try again.');
    }

    /**
     * Fast Track: skip materials for high pre-test scorers (score >= 90)
     */
    public function fastTrack(Enrollment $enrollment)
    {
        $this->authorize('fastTrack', $enrollment);

        if (($enrollment->pretest_score ?? 0) < $enrollment->course->fast_track_threshold) {
            return back()->with('error', 'Fast Track is only available for scores of ' . $enrollment->course->fast_track_threshold . '% or higher.');
        }

        if ($enrollment->status !== 'pre_test_done') {
            return back()->with('error', 'Fast Track is not available at this stage.');
        }

        // Skip content + attendance, go directly to 'attended' so post-test is accessible
        $enrollment->update([
            'status'            => 'attended',
            'attended_at'       => now(),
            'attendance_status' => 'fast_tracked',
        ]);

        return back()->with('success', 'Fast Track activated! Proceeding directly to post-test.');
    }
}
