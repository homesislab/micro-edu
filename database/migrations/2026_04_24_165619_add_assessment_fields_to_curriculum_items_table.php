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
        Schema::table('curriculum_items', function (Blueprint $table) {
            $table->string('assessment_mode')->default('diagnostic')->after('type');
            $table->integer('passing_grade')->nullable()->after('assessment_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curriculum_items', function (Blueprint $table) {
            $table->dropColumn(['assessment_mode', 'passing_grade']);
        });
    }
};
