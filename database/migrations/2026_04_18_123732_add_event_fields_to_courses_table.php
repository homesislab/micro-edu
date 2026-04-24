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
        Schema::table('courses', function (Blueprint $table) {
            $table->string('attendance_code')->nullable();
            $table->string('zoom_link')->nullable();
            $table->dateTime('event_time')->nullable();
            $table->boolean('is_recording_available')->default(false);
            $table->string('recording_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['attendance_code', 'zoom_link', 'event_time', 'is_recording_available', 'recording_url']);
        });
    }
};
