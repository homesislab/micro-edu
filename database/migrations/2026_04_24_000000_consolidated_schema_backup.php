<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Academies
        Schema::create('academies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->text('branding_settings')->nullable();
            $table->string('status')->default('active');
            $table->integer('user_quota')->default(100);
            $table->boolean('is_public')->default(true);
            $table->boolean('allow_self_enroll')->default(true);
            $table->string('primary_color')->default('#4f46e5');
            $table->string('secondary_color')->default('#06b6d4');
            $table->timestamps();
        });

        // 2. Users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role')->default('participant');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->foreignId('academy_id')->nullable()->constrained('academies')->nullOnDelete();
            $table->string('username')->unique()->nullable();
            $table->text('bio')->nullable();
            $table->string('avatar_url')->nullable();
            $table->timestamps();
        });

        // 3. Academy User Pivot
        Schema::create('academy_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('academy_id')->constrained('academies')->cascadeOnDelete();
            $table->string('role')->default('participant');
            $table->dateTime('last_accessed_at')->nullable();
            $table->timestamps();
        });

        // 4. Courses
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('expert_id')->constrained('users')->noActionOnDelete();
            $table->integer('passing_grade')->default(75);
            $table->string('status')->default('draft');
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('attendance_code')->nullable();
            $table->string('zoom_link')->nullable();
            $table->dateTime('event_time')->nullable();
            $table->boolean('is_recording_available')->default(false);
            $table->string('recording_url')->nullable();
            $table->foreignId('academy_id')->nullable()->constrained('academies')->nullOnDelete();
            $table->boolean('is_premium')->default(false);
            $table->integer('fast_track_threshold')->default(90);
            $table->timestamps();
        });

        // 5. Course Records (Architect Studio)
        Schema::create('course_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')->constrained('academies')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('price')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        // 6. Course Record Instructor Pivot
        Schema::create('course_record_instructor', function (Blueprint $table) {
            $table->foreignId('course_record_id')->constrained('course_records')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['course_record_id', 'user_id']);
            $table->timestamps();
        });

        // 7. Modules
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_record_id')->nullable()->constrained('course_records')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnDelete();
            $table->string('title');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 8. Curriculum Items
        Schema::create('curriculum_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->string('title');
            $table->string('type'); // literal, visual, knowledge, exercise
            $table->text('content_json')->nullable();
            $table->integer('order')->default(0);
            $table->text('rubric_json')->nullable();
            $table->boolean('is_capstone')->default(false);
            $table->timestamps();
        });

        // 9. Enrollments
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('status')->default('enrolled');
            $table->integer('pretest_score')->nullable();
            $table->integer('posttest_score')->nullable();
            $table->integer('score_delta')->nullable();
            $table->boolean('certificate_unlocked')->default(false);
            $table->timestamp('enrolled_at')->useCurrent();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('attended_at')->nullable();
            $table->string('attendance_status')->default('absent');
            $table->integer('points')->default(0);
            $table->timestamps();
        });

        // 10. Evaluation L1 Feedback
        Schema::create('evaluation_l1_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->unique()->constrained('enrollments')->cascadeOnDelete();
            $table->integer('rating');
            $table->text('review')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
        });

        // 11. Evaluation L2 Tests
        Schema::create('evaluation_l2_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->string('type');
            $table->text('answers');
            $table->integer('score');
            $table->timestamp('submitted_at')->useCurrent();
            $table->unique(['enrollment_id', 'type']);
            $table->timestamps();
        });

        // 12. Evaluation L3 Assignments
        Schema::create('evaluation_l3_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->string('submission_type');
            $table->string('file_path')->nullable();
            $table->string('link_url')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('expert_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->text('expert_notes')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
        });

        // 13. Rubric Templates
        Schema::create('rubric_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->string('name');
            $table->text('criteria_json');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 14. Rubric Scores
        Schema::create('rubric_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('evaluation_l3_assignments')->cascadeOnDelete();
            $table->foreignId('rubric_template_id')->nullable()->constrained('rubric_templates')->nullOnDelete();
            $table->text('details_json');
            $table->integer('total_score')->default(0);
            $table->timestamps();
        });

        // 15. Test Questions
        Schema::create('test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('type');
            $table->text('question_text');
            $table->text('options');
            $table->string('correct_key');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 16. Invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('academy_id')->constrained('academies')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->decimal('platform_fee', 10, 2);
            $table->decimal('creator_revenue', 10, 2);
            $table->string('status')->default('pending');
            $table->string('payment_gateway')->nullable();
            $table->string('reference_id')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });

        // 17. Certificate Templates
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('content_html')->nullable();
            $table->string('background_image_path')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 18. User Certificates
        Schema::create('user_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('certificate_templates')->cascadeOnDelete();
            $table->string('certificate_code')->unique();
            $table->string('file_path')->nullable();
            $table->dateTime('issued_at')->nullable();
            $table->timestamps();
        });

        // 19. Course Materials (Legacy/Alternate)
        Schema::create('course_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('type')->default('video');
            $table->string('title');
            $table->text('content');
            $table->integer('order_index')->default(0);
            $table->text('rubric_json')->nullable();
            $table->timestamps();
        });

        // 20. Blogs
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->string('category')->nullable();
            $table->string('status')->default('draft');
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
        });

        // 21. Activity Logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description')->nullable();
            $table->text('properties')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
        });

        // 22. Threads
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('academy_id')->constrained('academies')->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
        });

        // 23. Comments
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('thread_id')->constrained('threads')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
        });

        // 24. Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // 25. Cache & Locks
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // 26. Jobs & Batches
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // 27. Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 28. Password Reset Tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('threads');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('course_materials');
        Schema::dropIfExists('user_certificates');
        Schema::dropIfExists('certificate_templates');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('test_questions');
        Schema::dropIfExists('rubric_scores');
        Schema::dropIfExists('rubric_templates');
        Schema::dropIfExists('evaluation_l3_assignments');
        Schema::dropIfExists('evaluation_l2_tests');
        Schema::dropIfExists('evaluation_l1_feedback');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('curriculum_items');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('course_record_instructor');
        Schema::dropIfExists('course_records');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('academy_user');
        Schema::dropIfExists('users');
        Schema::dropIfExists('academies');
    }
};
