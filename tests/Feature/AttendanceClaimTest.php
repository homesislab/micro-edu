<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClaimTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_claim_succeeds_with_correct_code(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $participant = User::factory()->create(['role' => 'participant']);

        $course = Course::create([
            'title' => 'Attendance Course',
            'description' => 'Course with attendance code',
            'expert_id' => $expert->id,
            'passing_grade' => 75,
            'attendance_code' => 'ZOOM123',
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $participant->id,
            'course_id' => $course->id,
            'status' => 'content_done',
        ]);

        $response = $this->actingAs($participant)->post(route('evaluation.claimAttendance', $enrollment), [
            'code' => 'ZOOM123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Attendance confirmed! You can now proceed.');

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'attendance_status' => 'attended',
        ]);
    }

    public function test_attendance_claim_fails_with_invalid_code(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $participant = User::factory()->create(['role' => 'participant']);

        $course = Course::create([
            'title' => 'Attendance Course',
            'description' => 'Course with attendance code',
            'expert_id' => $expert->id,
            'passing_grade' => 75,
            'attendance_code' => 'ZOOM123',
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $participant->id,
            'course_id' => $course->id,
            'status' => 'content_done',
        ]);

        $response = $this->actingAs($participant)->post(route('evaluation.claimAttendance', $enrollment), [
            'code' => 'WRONGCODE',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Invalid attendance code. Please try again.');

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'attendance_status' => 'absent',
        ]);
    }
}
