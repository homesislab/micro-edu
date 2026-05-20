<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'points' to users if not exists
        if (!Schema::hasColumn('users', 'points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('points')->default(0)->after('remember_token');
            });
        }

        // Add 'issued_to' to user_certificates if not exists
        if (Schema::hasTable('user_certificates') && !Schema::hasColumn('user_certificates', 'issued_to')) {
            Schema::table('user_certificates', function (Blueprint $table) {
                $table->string('issued_to')->nullable()->after('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('points');
            });
        }

        if (Schema::hasTable('user_certificates') && Schema::hasColumn('user_certificates', 'issued_to')) {
            Schema::table('user_certificates', function (Blueprint $table) {
                $table->dropColumn('issued_to');
            });
        }
    }
};
