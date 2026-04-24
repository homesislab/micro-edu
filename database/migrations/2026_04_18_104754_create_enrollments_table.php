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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->enum('status', [
                'enrolled', 'pre_test_done', 'content_done', 'post_test_done', 
                'l1_done', 'l3_submitted', 'completed'
            ])->default('enrolled');
            $table->unsignedTinyInteger('pretest_score')->nullable();
            $table->unsignedTinyInteger('posttest_score')->nullable();
            $table->smallInteger('score_delta')->nullable();
            $table->boolean('certificate_unlocked')->default(false);
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
