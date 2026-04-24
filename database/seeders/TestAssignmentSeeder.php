<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EvaluationL3Assignment;
use Illuminate\Database\Seeder;

class TestAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $expert = User::where('role', 'expert')->orWhere('role', 'admin')->first();
        $participant = User::where('role', 'participant')->first();
        $course = Course::first();

        if (!$expert || !$participant || !$course) {
            return;
        }

        // Ensure enrollment exists
        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $participant->id, 'course_id' => $course->id],
            ['status' => 'l2_done', 'points' => 50]
        );

        // Create a pending assignment for Expert Jhon to review
        EvaluationL3Assignment::updateOrCreate(
            ['enrollment_id' => $enrollment->id],
            [
                'submission_type' => 'file',
                'file_path' => 'submissions/test_submission.jpg',
                'status' => 'pending',
                'description' => 'Saya telah menyelesaikan implementasi fitur CRUD sesuai modul.',
                'expert_id' => $expert->id
            ]
        );
        
        $enrollment->update(['status' => 'l3_submitted']);
    }
}
