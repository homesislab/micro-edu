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
        Schema::table('modules', function (Blueprint $table) {
            if (!Schema::hasColumn('modules', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
        });

        Schema::table('curriculum_items', function (Blueprint $table) {
            if (Schema::hasColumn('curriculum_items', 'content_json')) {
                $table->renameColumn('content_json', 'content');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curriculum_items', function (Blueprint $table) {
            if (Schema::hasColumn('curriculum_items', 'content')) {
                $table->renameColumn('content', 'content_json');
            }
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
