<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Academy;
use App\Models\Course;
use App\Models\Module;
use App\Models\CurriculumItem;
use App\Models\Enrollment;
use App\Models\Blog;
use App\Models\Thread;
use App\Models\Comment;
use App\Models\Invoice;
use App\Models\TestQuestion;
use App\Models\RubricTemplate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Users
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'username' => 'admin',
        ]);

        $expert = User::create([
            'name' => 'Expert Jhon',
            'email' => 'expert@example.com',
            'password' => Hash::make('password'),
            'role' => 'expert',
            'username' => 'expert_jhon',
            'bio' => 'Digital Marketing Consultant',
        ]);

        $participant = User::create([
            'name' => 'Participant Doe',
            'email' => 'participant@example.com',
            'password' => Hash::make('password'),
            'role' => 'participant',
            'username' => 'participant_doe',
        ]);

        // 2. Create Academies
        $academy1 = Academy::create([
            'name' => 'Growth Hacking Indonesia',
            'slug' => 'ghi',
            'status' => 'active',
            'user_quota' => 1000,
            'is_public' => true,
            'primary_color' => '#4f46e5',
            'secondary_color' => '#06b6d4',
        ]);

        $academy2 = Academy::create([
            'name' => 'Internal Training',
            'slug' => 'internal',
            'status' => 'active',
            'user_quota' => 100,
            'is_public' => false,
        ]);

        // Connect users to academies
        $expert->academies()->attach($academy1->id, ['role' => 'owner']);
        $admin->academies()->attach($academy1->id, ['role' => 'admin']);
        $admin->academies()->attach($academy2->id, ['role' => 'owner']);
        $participant->academies()->attach($academy1->id, ['role' => 'participant']);

        // 3. Create Courses
        $course = Course::create([
            'academy_id' => $academy1->id,
            'expert_id' => $expert->id,
            'title' => 'Mastering SEO 2026',
            'description' => 'The ultimate guide to SEO.',
            'price' => 1500000,
            'is_premium' => true,
            'status' => 'published',
            'passing_grade' => 75,
        ]);

        // 4. Create Modules & Curriculum Items
        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Fundamentals',
            'order' => 1,
        ]);

        CurriculumItem::create([
            'module_id' => $module->id,
            'title' => 'Welcome Video',
            'type' => 'video',
            'content' => ['url' => 'https://vimeo.com/12345', 'duration' => '10:00'],
            'order' => 1,
        ]);

        CurriculumItem::create([
            'module_id' => $module->id,
            'title' => 'Reading Material',
            'type' => 'literal',
            'content' => ['body' => 'SEO stands for Search Engine Optimization...'],
            'order' => 2,
        ]);

        CurriculumItem::create([
            'module_id' => $module->id,
            'title' => 'Practice Quiz',
            'type' => 'knowledge',
            'content' => ['questions_count' => 5],
            'order' => 3,
        ]);

        // 5. Community
        Blog::create([
            'expert_id' => $expert->id,
            'title' => 'The Future of Search',
            'slug' => 'future-of-search',
            'content' => 'AI is changing everything...',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $thread = Thread::create([
            'user_id' => $participant->id,
            'course_id' => $course->id,
            'academy_id' => $academy1->id,
            'title' => 'Help with Keyword Research',
            'content' => 'What tools do you recommend?',
        ]);

        Comment::create([
            'user_id' => $expert->id,
            'thread_id' => $thread->id,
            'content' => 'Ahrefs and SEMRush are great.',
        ]);

        // 6. Enrollment & Invoices
        $enrollment = Enrollment::create([
            'user_id' => $participant->id,
            'course_id' => $course->id,
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        Invoice::create([
            'user_id' => $participant->id,
            'course_id' => $course->id,
            'academy_id' => $academy1->id,
            'amount' => 1500000,
            'platform_fee' => 150000,
            'creator_revenue' => 1350000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // 7. Test Questions
        TestQuestion::create([
            'course_id' => $course->id,
            'type' => 'pretest',
            'question_text' => 'What is LCP?',
            'options' => ['A' => 'Largest Contentful Paint', 'B' => 'Low Core Port'],
            'correct_key' => 'A',
            'order' => 1,
        ]);

        // 8. Rubric Template
        RubricTemplate::create([
            'expert_id' => $expert->id,
            'course_id' => $course->id,
            'name' => 'SEO Audit Rubric',
            'criteria_json' => [
                ['name' => 'Technical Audit', 'max_score' => 50],
                ['name' => 'Keyword Strategy', 'max_score' => 50],
            ],
        ]);
    }
}
