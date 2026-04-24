<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluationTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_test_returns_error_when_no_questions_are_available(): void
    {
        $user = User::factory()->create(['role' => 'participant']);

        $course = Course::create([
            'title' => 'Test Course',
            'description' => 'Evaluation test course',
            'expert_id' => User::factory()->create(['role' => 'expert'])->id,
            'passing_grade' => 75,
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'enrolled',
        ]);

        $response = $this->actingAs($user)->post(route('evaluation.submitTest', [
            'enrollment' => $enrollment->id,
            'type' => 'pretest',
        ]), [
            'answers' => ['1' => 'a'],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Tidak ada pertanyaan untuk evaluasi ini. Silakan tambahkan soal terlebih dahulu.');
    }
}
