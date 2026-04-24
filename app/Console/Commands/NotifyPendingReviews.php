<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

    #[Signature('app:notify-pending-reviews')]
    #[Description('Notify experts about assignments pending review for more than 48 hours')]
    class NotifyPendingReviews extends Command
    {
        /**
         * Execute the console command.
         */
        public function handle(\App\Services\WhatsAppService $waService)
        {
            $overdueCount = 0;
            
            // Get pending L3 assignments older than 48 hours
            $pendingAssignments = \App\Models\EvaluationL3Assignment::with('enrollment.course.expert')
                ->where('status', 'pending')
                ->where('created_at', '<', now()->subHours(48))
                ->get();

            // Group by expert_id
            $expertsToNotify = $pendingAssignments->groupBy(function ($assignment) {
                return $assignment->enrollment->course->expert_id;
            });

            foreach ($expertsToNotify as $expertId => $assignments) {
                $expert = $assignments->first()->enrollment->course->expert;
                if (!$expert) continue;

                $count = $assignments->count();
                $waService->sendReviewReminder($expert, $count);
                
                $this->info("Notified Expert: {$expert->name} for {$count} assignments.");
                $overdueCount++;
            }

            $this->info("Done! Notified {$overdueCount} experts about overdue reviews.");
        }
    }
