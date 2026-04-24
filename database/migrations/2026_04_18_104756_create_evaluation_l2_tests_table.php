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
        Schema::create('evaluation_l2_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['pretest', 'posttest']);
            $table->json('answers');
            $table->unsignedTinyInteger('score');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            $table->unique(['enrollment_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_l2_tests');
    }
};
