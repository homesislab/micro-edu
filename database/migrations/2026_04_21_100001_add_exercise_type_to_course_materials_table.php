<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support MODIFY COLUMN, but it also doesn't enforce ENUMs —
        // the 'exercise' value will work as a TEXT value automatically.
        // We only need to add the new rubric_json column.
        Schema::table('course_materials', function (Blueprint $table) {
            $table->json('rubric_json')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('course_materials', function (Blueprint $table) {
            $table->dropColumn('rubric_json');
        });
    }
};
