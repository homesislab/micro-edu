<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendEventReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send event reminders (Mock WA/Email)';

    public function handle()
    {
        $this->info('Checking for upcoming events...');

        $now = now();
        $tomorrow = now()->addDay();
        $oneHour = now()->addHour();

        // 1. Daily Reminders (H-1)
        $dailyCourses = Course::whereBetween('event_time', [$tomorrow->subMinutes(30), $tomorrow->addMinutes(30)])
            ->with('enrollments.user')
            ->get();

        foreach ($dailyCourses as $course) {
            foreach ($course->enrollments as $enrollment) {
                $this->sendMockWA($enrollment->user, "H-1 Pengingat: '{$course->title}' dimulai besok jam " . $course->event_time->format('H:i') . ". Siapkan diri Anda!");
            }
        }

        // 2. Hourly Reminders (1 Jam Lagi)
        $hourlyCourses = Course::whereBetween('event_time', [$oneHour->subMinutes(15), $oneHour->addMinutes(15)])
            ->with('enrollments.user')
            ->get();

        foreach ($hourlyCourses as $course) {
            foreach ($course->enrollments as $enrollment) {
                $msg = "Akan dimulai dalam 1 jam! 🚀 Bersiaplah untuk '{$course->title}'. Link Zoom: {$course->zoom_link}";
                $this->sendMockWA($enrollment->user, $msg);
            }
        }

        $this->info('Reminders sent successfully!');
    }

    private function sendMockWA($user, $message)
    {
        app(\App\Services\WhatsAppService::class)->sendMessage($user, $message);
        $this->line("Sent to {$user->name}: {$message}");
    }
}
