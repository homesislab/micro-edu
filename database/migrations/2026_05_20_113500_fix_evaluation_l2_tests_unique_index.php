<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The old unique index on (enrollment_id, type) prevents saving multiple tests
     * for different curriculum items. Replace with a composite that includes curriculum_item_id.
     */
    public function up(): void
    {
        // For MySQL: need to drop foreign key first, then unique, then re-add foreign key
        Schema::table('evaluation_l2_tests', function (Blueprint $table) {
            // Drop the foreign key that depends on the enrollment_id column
            $table->dropForeign(['enrollment_id']);
        });

        Schema::table('evaluation_l2_tests', function (Blueprint $table) {
            // Drop old narrow unique index
            $table->dropUnique('evaluation_l2_tests_enrollment_id_type_unique');

            // Create new composite unique index that allows per-item tests
            $table->unique(
                ['enrollment_id', 'curriculum_item_id', 'type'],
                'eval_l2_enrollment_item_type_unique'
            );

            // Re-add the foreign key
            $table->foreign('enrollment_id')
                ->references('id')
                ->on('enrollments')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_l2_tests', function (Blueprint $table) {
            $table->dropForeign(['enrollment_id']);
            $table->dropUnique('eval_l2_enrollment_item_type_unique');
            $table->unique(['enrollment_id', 'type'], 'evaluation_l2_tests_enrollment_id_type_unique');
            $table->foreign('enrollment_id')
                ->references('id')
                ->on('enrollments')
                ->onDelete('cascade');
        });
    }
};
