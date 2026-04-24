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
        // 1. Rename sections to modules
        Schema::rename('sections', 'modules');

        // 2. Update modules table
        Schema::table('modules', function (Blueprint $table) {
            // Check if course_id exists, if not add it
            if (!Schema::hasColumn('modules', 'course_id')) {
                $table->foreignId('course_id')->after('id')->nullable()->constrained('courses')->onDelete('cascade');
            }
            // Optional: Drop course_record_id if no longer needed, 
            // but for safety we'll keep it or make it nullable
            $table->foreignId('course_record_id')->nullable()->change();
        });

        // 3. Rename lessons to curriculum_items
        Schema::rename('lessons', 'curriculum_items');

        // 4. Update curriculum_items table
        Schema::table('curriculum_items', function (Blueprint $table) {
            // Rename section_id to module_id
            $table->renameColumn('section_id', 'module_id');
            
            // Enum update for 'type'
            // SQLite doesn't enforce ENUMs, but we'll document it in the migration
            // 'literal', 'visual', 'knowledge', 'exercise'
            
            // Add is_capstone and rubric_json (rubric_json might already exist from previous migrations)
            if (!Schema::hasColumn('curriculum_items', 'is_capstone')) {
                $table->boolean('is_capstone')->default(false)->after('order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curriculum_items', function (Blueprint $table) {
            $table->renameColumn('module_id', 'section_id');
            $table->dropColumn('is_capstone');
        });

        Schema::rename('curriculum_items', 'lessons');

        Schema::table('modules', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');
        });

        Schema::rename('modules', 'sections');
    }
};
