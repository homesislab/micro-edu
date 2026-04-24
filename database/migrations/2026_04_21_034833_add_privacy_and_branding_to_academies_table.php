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
        Schema::table('academies', function (Blueprint $table) {
            $table->boolean('is_public')->default(true)->after('slug');
            $table->boolean('allow_self_enroll')->default(true)->after('is_public');
            $table->string('primary_color')->default('#4f46e5')->after('logo');
            $table->string('secondary_color')->default('#06b6d4')->after('primary_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academies', function (Blueprint $table) {
            $table->dropColumn(['is_public', 'allow_self_enroll', 'primary_color', 'secondary_color']);
        });
    }
};
