<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpertAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_owner_cannot_update_course_settings(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $otherExpert = User::factory()->create(['role' => 'expert']);

        $course = Course::create([
            'title' => 'Expert Course',
            'description' => 'A premium course',
            'expert_id' => $expert->id,
            'passing_grade' => 75,
        ]);

        $response = $this->actingAs($otherExpert)->patch(route('expert.courses.settings', $course), [
            'attendance_code' => 'ATTEND123',
            'zoom_link' => 'https://example.com/zoom',
            'event_time' => now()->addDays(1)->toDateTimeString(),
            'passing_grade' => 80,
        ]);

        $response->assertStatus(403);
    }

    public function test_owner_can_update_course_settings(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);

        $course = Course::create([
            'title' => 'Expert Course',
            'description' => 'A premium course',
            'expert_id' => $expert->id,
            'passing_grade' => 75,
        ]);

        $response = $this->actingAs($expert)->patch(route('expert.courses.settings', $course), [
            'attendance_code' => 'OPEN123',
            'zoom_link' => 'https://example.com/zoom',
            'event_time' => now()->addDays(1)->toDateTimeString(),
            'passing_grade' => 90,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Settings updated successfully!');
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'attendance_code' => 'OPEN123',
            'passing_grade' => 90,
        ]);
    }

    public function test_non_owner_cannot_add_material_to_course(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $otherExpert = User::factory()->create(['role' => 'expert']);

        $course = Course::create([
            'title' => 'Expert Course',
            'description' => 'A premium course',
            'expert_id' => $expert->id,
            'passing_grade' => 75,
        ]);

        $response = $this->actingAs($otherExpert)->post(route('expert.materials.store', $course), [
            'title' => 'New Module',
            'type' => 'video',
            'content' => 'https://example.com/video.mp4',
        ]);

        $response->assertStatus(403);
    }

    public function test_non_owner_cannot_update_material(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $otherExpert = User::factory()->create(['role' => 'expert']);

        $course = Course::create([
            'title' => 'Expert Course',
            'description' => 'A premium course',
            'expert_id' => $expert->id,
            'passing_grade' => 75,
        ]);

        $material = $course->materials()->create([
            'title' => 'Module 1',
            'type' => 'video',
            'content' => 'https://example.com/video.mp4',
        ]);

        $response = $this->actingAs($otherExpert)->patch(route('expert.materials.update', $material), [
            'title' => 'Updated Module',
            'type' => 'video',
            'content' => 'https://example.com/video.mp4',
        ]);

        $response->assertStatus(403);
    }

    public function test_non_owner_cannot_delete_material(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $otherExpert = User::factory()->create(['role' => 'expert']);

        $course = Course::create([
            'title' => 'Expert Course',
            'description' => 'A premium course',
            'expert_id' => $expert->id,
            'passing_grade' => 75,
        ]);

        $material = $course->materials()->create([
            'title' => 'Module 1',
            'type' => 'video',
            'content' => 'https://example.com/video.mp4',
        ]);

        $response = $this->actingAs($otherExpert)->delete(route('expert.materials.delete', $material));

        $response->assertStatus(403);
    }

    public function test_non_owner_cannot_add_question(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $otherExpert = User::factory()->create(['role' => 'expert']);

        $course = Course::create([
            'title' => 'Expert Course',
            'description' => 'A premium course',
            'expert_id' => $expert->id,
            'passing_grade' => 75,
        ]);

        $response = $this->actingAs($otherExpert)->post(route('expert.questions.store', $course), [
            'question_text' => 'What is Laravel?',
            'type' => 'pretest',
            'options' => [['key' => 'a', 'text' => 'PHP framework'], ['key' => 'b', 'text' => 'Database']],
            'correct_answer' => 'a',
        ]);

        $response->assertStatus(403);
    }

    public function test_non_owner_cannot_update_question(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $otherExpert = User::factory()->create(['role' => 'expert']);

        $course = Course::create([
            'title' => 'Expert Course',
            'description' => 'A premium course',
            'expert_id' => $expert->id,
            'passing_grade' => 75,
        ]);

        $question = $course->testQuestions()->create([
            'question_text' => 'What is Laravel?',
            'type' => 'pretest',
            'options' => [['key' => 'a', 'text' => 'PHP framework'], ['key' => 'b', 'text' => 'Database']],
            'correct_key' => 'a',
            'order' => 1,
        ]);

        $response = $this->actingAs($otherExpert)->patch(route('expert.questions.update', $question), [
            'question_text' => 'What is Laravel now?',
            'type' => 'pretest',
            'options' => [['key' => 'a', 'text' => 'PHP framework'], ['key' => 'b', 'text' => 'Database']],
            'correct_answer' => 'a',
        ]);

        $response->assertStatus(403);
    }

    public function test_non_owner_cannot_delete_question(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $otherExpert = User::factory()->create(['role' => 'expert']);

        $course = Course::create([
            'title' => 'Expert Course',
            'description' => 'A premium course',
            'expert_id' => $expert->id,
            'passing_grade' => 75,
        ]);

        $question = $course->testQuestions()->create([
            'question_text' => 'What is Laravel?',
            'type' => 'pretest',
            'options' => [['key' => 'a', 'text' => 'PHP framework'], ['key' => 'b', 'text' => 'Database']],
            'correct_key' => 'a',
            'order' => 1,
        ]);

        $response = $this->actingAs($otherExpert)->delete(route('expert.questions.delete', $question));

        $response->assertStatus(403);
    }

    public function test_non_owner_cannot_generate_flyer(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $otherExpert = User::factory()->create(['role' => 'expert']);

        $course = Course::create([
            'title' => 'Expert Course',
            'description' => 'A premium course',
            'expert_id' => $expert->id,
            'passing_grade' => 75,
        ]);

        $response = $this->actingAs($otherExpert)->post(route('expert.courses.flyer', $course));

        $response->assertStatus(403);
    }
}
