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
        Schema::table('evaluation_l2_tests', function (Blueprint $table) {
            $table->foreignId('curriculum_item_id')->nullable()->constrained('curriculum_items')->nullOnDelete();
        });

        Schema::table('evaluation_l3_assignments', function (Blueprint $table) {
            $table->foreignId('curriculum_item_id')->nullable()->constrained('curriculum_items')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluation_l2_tests', function (Blueprint $table) {
            $table->dropColumn('curriculum_item_id');
        });

        Schema::table('evaluation_l3_assignments', function (Blueprint $table) {
            $table->dropColumn('curriculum_item_id');
        });
    }
};
