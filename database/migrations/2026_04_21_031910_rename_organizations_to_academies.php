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
        Schema::rename('organizations', 'academies');
        
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('organization_id', 'academy_id');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->renameColumn('organization_id', 'academy_id');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->renameColumn('academy_id', 'organization_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('academy_id', 'organization_id');
        });

        Schema::rename('academies', 'organizations');
    }
};
