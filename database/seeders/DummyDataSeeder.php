<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Academy;
use App\Models\Course;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create Users with different roles
        $participant = User::create([
            'name' => 'Participant Doe',
            'email' => 'participant@example.com',
            'password' => Hash::make('password'),
            'role' => 'participant',
        ]);

        $expert = User::create([
            'name' => 'Expert Jhon',
            'email' => 'expert@example.com',
            'password' => Hash::make('password'),
            'role' => 'expert',
        ]);

        $admin = User::create([
            'name' => 'Admin Boss',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Create Academies
        $academy1 = Academy::create([
            'name' => 'Growth Hacking Indonesia',
            'slug' => 'growth-hacking-indonesia',
            'is_public' => true,
        ]);

        $academy2 = Academy::create([
            'name' => 'Digital Marketing Pro',
            'slug' => 'digital-marketing-pro',
            'is_public' => true,
        ]);
        
        $privateAcademy = Academy::create([
            'name' => 'Internal Corporate Training',
            'slug' => 'internal-corp-training',
            'is_public' => false, // Private Academy
        ]);

        // 3. Attach Users to Academies with roles
        $expert->academies()->attach($academy1->id, ['role' => 'owner']);
        $expert->academies()->attach($academy2->id, ['role' => 'expert']);
        $admin->academies()->attach($privateAcademy->id, ['role' => 'owner']);
        $participant->academies()->attach($academy1->id, ['role' => 'participant']);
        $participant->academies()->attach($academy2->id, ['role' => 'participant']);
        $participant->academies()->attach($privateAcademy->id, ['role' => 'participant']);


        // 4. Create Courses within those Academies
        Course::create([
            'academy_id' => $academy1->id,
            'expert_id' => $expert->id,
            'title' => 'Advanced SEO Mastery',
            'description' => 'A deep dive into advanced SEO techniques.',
            'price' => 100,
            'is_premium' => true,
            'passing_grade' => 75,
            'fast_track_threshold' => 95,
        ]);
        
        Course::create([
            'academy_id' => $academy1->id,
            'expert_id' => $expert->id,
            'title' => 'Viral Marketing Fundamentals',
            'description' => 'Learn the basics of creating viral content.',
            'price' => 0,
            'is_premium' => false,
            'passing_grade' => 60,
        ]);

        Course::create([
            'academy_id' => $academy2->id,
            'expert_id' => $expert->id,
            'title' => 'Google Ads for E-Commerce',
            'description' => 'Master Google Ads to boost your e-commerce sales.',
            'price' => 150,
            'is_premium' => true,
            'passing_grade' => 80,
        ]);
        
        Course::create([
            'academy_id' => $privateAcademy->id,
            'expert_id' => $admin->id, // The admin is the expert for internal course
            'title' => 'New Employee Onboarding',
            'description' => 'Mandatory onboarding for all new hires.',
            'price' => 0,
            'is_premium' => false,
            'passing_grade' => 90,
        ]);

        // 5. Enroll the participant in a course
        $firstCourse = Course::first();
        $participant->enrollments()->create([
            'course_id' => $firstCourse->id,
            'status' => 'enrolled',
        ]);
    }
}
