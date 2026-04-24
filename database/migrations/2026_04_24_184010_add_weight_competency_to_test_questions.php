<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_questions', function (Blueprint $table) {
            $table->unsignedInteger('weight')->default(1)->after('order');
            $table->json('competency_tags')->nullable()->after('weight');
        });
    }

    public function down(): void
    {
        Schema::table('test_questions', function (Blueprint $table) {
            $table->dropColumn(['weight', 'competency_tags']);
        });
    }
};
