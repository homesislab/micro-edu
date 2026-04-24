<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_templates', function (Blueprint $row) {
            $row->id();
            $row->foreignId('expert_id')->constrained('users')->onDelete('cascade');
            $row->string('title');
            $row->string('sub_type')->default('quiz'); // pre_test, quiz, final_exam
            $row->string('assessment_mode')->default('diagnostic'); // diagnostic, mastery
            $row->integer('passing_grade')->nullable();
            $row->json('content')->nullable();
            $row->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_templates');
    }
};
