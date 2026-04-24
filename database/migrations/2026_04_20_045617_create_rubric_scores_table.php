<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rubric_scores')) {
            Schema::create('rubric_scores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assignment_id')->constrained('evaluation_l3_assignments')->cascadeOnDelete();
                $table->foreignId('rubric_template_id')->nullable()->constrained('rubric_templates')->nullOnDelete();
                $table->json('details_json'); // New name
                $table->integer('total_score')->default(0); 
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_scores');
    }
};
