<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EvaluationL3Assignment;
use App\Models\User;
use App\Services\MediaGeneratorService;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpertReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_approve_l3_assignment_and_generate_certificate(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $participant = User::factory()->create(['role' => 'participant']);

        $course = Course::create([
            'title' => 'Review Course',
            'description' => 'Course for review workflow',
            'expert_id' => $expert->id,
            'passing_grade' => 75,
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $participant->id,
            'course_id' => $course->id,
            'status' => 'l3_submitted',
            'points' => 50,
        ]);

        $assignment = EvaluationL3Assignment::create([
            'enrollment_id' => $enrollment->id,
            'submission_type' => 'link',
            'link_url' => 'https://example.com/submission',
            'description' => 'L3 project submission',
            'status' => 'pending',
        ]);

        app()->instance(MediaGeneratorService::class, new class extends MediaGeneratorService {
            public function generateCertificate($userCertificate)
            {
                return 'certificates/mock.pdf';
            }
        });

        app()->instance(WhatsAppService::class, new class {
            public function sendGraduation($user, $courseTitle)
            {
                return true;
            }
        });

        $response = $this->actingAs($expert)->patch(route('expert.review', $assignment), [
            'status' => 'approved',
            'expert_notes' => 'Great work.',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Assignment reviewed successfully!');

        $this->assertDatabaseHas('evaluation_l3_assignments', [
            'id' => $assignment->id,
            'status' => 'approved',
            'expert_notes' => 'Great work.',
        ]);

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'status' => 'completed',
            'points' => 150,
        ]);

        $this->assertDatabaseHas('user_certificates', [
            'user_id' => $participant->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_non_owner_cannot_review_assignment(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $otherExpert = User::factory()->create(['role' => 'expert']);
        $participant = User::factory()->create(['role' => 'participant']);

        $course = Course::create([
            'title' => 'Review Course',
            'description' => 'Course for review workflow',
            'expert_id' => $expert->id,
            'passing_grade' => 75,
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $participant->id,
            'course_id' => $course->id,
            'status' => 'l3_submitted',
        ]);

        $assignment = EvaluationL3Assignment::create([
            'enrollment_id' => $enrollment->id,
            'submission_type' => 'link',
            'link_url' => 'https://example.com/submission',
            'description' => 'L3 project submission',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($otherExpert)->patch(route('expert.review', $assignment), [
            'status' => 'approved',
            'expert_notes' => 'Not allowed',
        ]);

        $response->assertStatus(403);
    }
}
