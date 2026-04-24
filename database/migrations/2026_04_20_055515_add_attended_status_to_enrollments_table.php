<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * SQLite does not support ALTER COLUMN for CHECK constraints.
 * We recreate the enrollments table with the expanded status allowlist.
 *
 * Existing columns (15 total, verified via PRAGMA table_info):
 *   id, user_id, course_id, status, pretest_score, posttest_score,
 *   score_delta, certificate_unlocked, enrolled_at, completed_at,
 *   created_at, updated_at, attended_at, attendance_status, points
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            DB::statement("ALTER TABLE enrollments MODIFY COLUMN status ENUM('enrolled', 'pre_test_done', 'content_done', 'attended', 'post_test_done', 'l1_done', 'l3_submitted', 'completed', 'fast_tracked') NOT NULL DEFAULT 'enrolled'");
        } else {
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::statement("
                CREATE TABLE enrollments_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    course_id INTEGER NOT NULL,
                    status varchar NOT NULL DEFAULT 'enrolled'
                        CHECK(status IN (
                            'enrolled', 'pre_test_done', 'content_done', 'attended',
                            'post_test_done', 'l1_done', 'l3_submitted', 'completed', 'fast_tracked'
                        )),
                    pretest_score INTEGER,
                    posttest_score INTEGER,
                    score_delta INTEGER,
                    certificate_unlocked tinyint(1) NOT NULL DEFAULT '0',
                    enrolled_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    completed_at datetime,
                    created_at datetime,
                    updated_at datetime,
                    attended_at datetime,
                    attendance_status varchar NOT NULL DEFAULT 'absent',
                    points INTEGER NOT NULL DEFAULT '0',
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
                )
            ");
            DB::statement('INSERT INTO enrollments_new SELECT * FROM enrollments');
            DB::statement('DROP TABLE enrollments');
            DB::statement('ALTER TABLE enrollments_new RENAME TO enrollments');
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            DB::statement("ALTER TABLE enrollments MODIFY COLUMN status ENUM('enrolled', 'pre_test_done', 'content_done', 'post_test_done', 'l1_done', 'l3_submitted', 'completed') NOT NULL DEFAULT 'enrolled'");
        } else {
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::statement("
                CREATE TABLE enrollments_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    course_id INTEGER NOT NULL,
                    status varchar NOT NULL DEFAULT 'enrolled'
                        CHECK(status IN (
                            'enrolled', 'pre_test_done', 'content_done',
                            'post_test_done', 'l1_done', 'l3_submitted', 'completed'
                        )),
                    pretest_score INTEGER,
                    posttest_score INTEGER,
                    score_delta INTEGER,
                    certificate_unlocked tinyint(1) NOT NULL DEFAULT '0',
                    enrolled_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    completed_at datetime,
                    created_at datetime,
                    updated_at datetime,
                    attended_at datetime,
                    attendance_status varchar NOT NULL DEFAULT 'absent',
                    points INTEGER NOT NULL DEFAULT '0',
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
                )
            ");
            DB::statement("
                INSERT INTO enrollments_new SELECT * FROM enrollments
                WHERE status NOT IN ('attended', 'fast_tracked')
            ");
            DB::statement('DROP TABLE enrollments');
            DB::statement('ALTER TABLE enrollments_new RENAME TO enrollments');
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }
};
