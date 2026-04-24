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

    public function l3Assignments()
    {
        return $this->hasMany(EvaluationL3Assignment::class);
    }

    /**
     * Aggregates L2 performance from item-level tests.
     */
    public function calculateDelta()
    {
        $tests = $this->l2Tests()->get();
        if ($tests->isEmpty()) return;

        // Group by curriculum item to find pre/post pairs
        $grouped = $tests->groupBy('curriculum_item_id');
        
        $preSum = 0;
        $postSum = 0;
        $pairsCount = 0;

        foreach ($grouped as $itemId => $itemTests) {
            $pre = $itemTests->where('type', 'pre_test')->first();
            $post = $itemTests->where('type', 'final_exam')->first();

            if ($pre && $post) {
                $preSum += $pre->score;
                $postSum += $post->score;
                $pairsCount++;
            }
        }

        if ($pairsCount > 0) {
            $avgPre = $preSum / $pairsCount;
            $avgPost = $postSum / $pairsCount;
            
            $this->update([
                'pretest_score' => round($avgPre),
                'posttest_score' => round($avgPost),
                'score_delta' => round($avgPost - $avgPre)
            ]);
        }
    }

    /**
     * Strictly validates all L2 and L3 requirements.
     */
    public function checkCertificateEligibility(): bool
    {
        // 1. Check L2 Mastery (Knowledge items marked as mastery)
        $masteryItems = $this->course->modules()
            ->with('items')
            ->get()
            ->flatMap->items
            ->where('type', 'knowledge')
            ->where('assessment_mode', 'mastery');

        foreach ($masteryItems as $item) {
            $bestScore = $this->l2Tests()
                ->where('curriculum_item_id', $item->id)
                ->where('type', 'final_exam')
                ->max('score');

            if (!$bestScore || $bestScore < $this->course->passing_grade) {
                return false;
            }
        }

        // 2. Check L3 Behavior (Exercise items)
        $exerciseItems = $this->course->modules()
            ->with('items')
            ->get()
            ->flatMap->items
            ->where('type', 'exercise');

        foreach ($exerciseItems as $item) {
            $isApproved = $this->l3Assignments()
                ->where('curriculum_item_id', $item->id)
                ->where('status', 'approved')
                ->exists();

            if (!$isApproved) {
                return false;
            }
        }

        // If we reach here, all gates are passed
        $this->update(['certificate_unlocked' => true, 'status' => 'completed']);
        return true;
    }
}
