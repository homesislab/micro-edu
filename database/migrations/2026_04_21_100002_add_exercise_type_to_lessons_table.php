<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't enforce ENUMs — 'exercise' will work automatically.
        // We only need to add the rubric_json column.
        Schema::table('lessons', function (Blueprint $table) {
            $table->json('rubric_json')->nullable()->after('content_json');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('rubric_json');
        });
    }
};
