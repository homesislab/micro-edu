<?php

namespace App\Services;

use App\Models\Course;
use App\Models\UserCertificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaGeneratorService
{
    /**
     * Generate a professional PDF certificate.
     */
    public function generateCertificate(UserCertificate $userCertificate)
    {
        $userCertificate->load(['user', 'course', 'template']);
        
        $pdf = Pdf::loadView('templates.certificate', [
            'certificate' => $userCertificate,
            'user' => $userCertificate->user,
            'course' => $userCertificate->course,
            'template' => $userCertificate->template,
        ])->setPaper('a4', 'landscape');

        $fileName = 'certificates/' . $userCertificate->certificate_code . '.pdf';
        Storage::disk('public')->put($fileName, $pdf->output());

        $userCertificate->update([
            'file_path' => $fileName,
            'issued_at' => now(),
        ]);

        return $fileName;
    }

    /**
     * Generate a premium marketing flyer for a course.
     */
    public function generateFlyer(Course $course)
    {
        $course->load('expert');
        $fileName = 'flyers/course_' . $course->id . '_' . Str::random(5) . '.png';
        
        // Ensure directory exists
        Storage::disk('public')->makeDirectory('flyers');

        // Using Browsershot for high-end rendering
        // Note: Browsershot requires Node.js and Puppeteer installed.
        // We'll provide a fallback or ensure paths are correct.
        try {
            $html = view('templates.flyer', [
                'course' => $course,
                'expert' => $course->expert
            ])->render();

            Browsershot::html($html)
                ->windowSize(1080, 1350) // Instagram Portrait size
                ->setScreenshotType('png')
                ->save(storage_path('app/public/' . $fileName));

            return $fileName;
        } catch (\Exception $e) {
            \Log::error('Flyer generation failed: ' . $e->getMessage());
            // Fallback strategy if Browsershot fails (could use Intervention Image)
            return null;
        }
    }
}
