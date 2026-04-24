<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\TestQuestion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Expert
        $expert = User::create([
            'name' => 'Expert Jhon',
            'email' => 'expert@example.com',
            'password' => Hash::make('password'),
            'role' => 'expert',
        ]);

        // 2. Create Participant (for testing)
        User::create([
            'name' => 'Participant Doe',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'participant',
        ]);

        // 3. Create Course
        $course = Course::create([
            'title' => 'Fundamental Full-Stack Development',
            'description' => 'Mempelajari dasar-dasar pengembangan aplikasi web dari frontend hingga backend.',
            'expert_id' => $expert->id,
            'passing_grade' => 70,
            'status' => 'published',
        ]);

        // 4. Create Pre-test Questions
        $questions = [
            [
                'type' => 'pretest',
                'question_text' => 'Apa kepanjangan dari PHP?',
                'options' => [
                    ['key' => 'A', 'text' => 'Private Home Page'],
                    ['key' => 'B', 'text' => 'PHP: Hypertext Preprocessor'],
                    ['key' => 'C', 'text' => 'Personal Hypertext Processor'],
                    ['key' => 'D', 'text' => 'Program Hypertext Page'],
                ],
                'correct_key' => 'B',
                'order' => 1,
            ],
            [
                'type' => 'pretest',
                'question_text' => 'Framework apa yang digunakan untuk CSS?',
                'options' => [
                    ['key' => 'A', 'text' => 'Laravel'],
                    ['key' => 'B', 'text' => 'Vue.js'],
                    ['key' => 'C', 'text' => 'Tailwind'],
                    ['key' => 'D', 'text' => 'MySQL'],
                ],
                'correct_key' => 'C',
                'order' => 2,
            ],
        ];

        foreach ($questions as $q) {
            $course->testQuestions()->create($q);
        }

        // 5. Create Post-test Questions (Same as pre-test for simplicity, but as post-test type)
        foreach ($questions as $q) {
            $q['type'] = 'posttest';
            $course->testQuestions()->create($q);
        }
    }
}
