<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

use App\Traits\LogsActivity;

#[Fillable(['user_id', 'course_id', 'status', 'pretest_score', 'posttest_score', 'score_delta', 'certificate_unlocked', 'attended_at', 'attendance_status', 'points'])]
class Enrollment extends Model
{
    use LogsActivity;
    protected $casts = [
        'attended_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function l1Feedback()
    {
        return $this->hasOne(EvaluationL1Feedback::class);
    }

    public function l2Tests()
    {
        return $this->hasMany(EvaluationL2Test::class);
    }

    public function l3Assignment()
    {
        return $this->hasOne(EvaluationL3Assignment::class);
    }

    /**
     * Processes the result of a pre-test or post-test and updates the enrollment status.
     *
     * @param string $type The type of the test ('pretest' or 'posttest').
     * @param int $score The calculated score.
     * @return void
     */
    public function processTestResult(string $type, int $score): void
    {
        if ($type === 'pretest') {
            $isFirstTime = $this->status === 'enrolled';
            $this->update([
                'pretest_score' => $score,
                'status' => 'pre_test_done',
                'points' => $isFirstTime ? ($this->points ?? 0) + 10 : ($this->points ?? 0)
            ]);
        } 
        
        if ($type === 'posttest') {
            $isFirstTime = $this->status === 'l1_done' || $this->status === 'attended';
            $delta = $score - ($this->pretest_score ?? 0);
            $passed = $score >= $this->course->passing_grade;
            
            $this->update([
                'posttest_score' => $score,
                'score_delta' => $delta,
                'certificate_unlocked' => $passed,
                'status' => 'post_test_done',
                'points' => $isFirstTime ? ($this->points ?? 0) + 50 : ($this->points ?? 0)
            ]);
        }
    }
}
