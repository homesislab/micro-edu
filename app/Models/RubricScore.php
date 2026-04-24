<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RubricScore extends Model
{
    protected $fillable = ['assignment_id', 'rubric_template_id', 'details_json', 'total_score'];

    protected $casts = [
        'details_json' => 'array',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(EvaluationL3Assignment::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(RubricTemplate::class, 'rubric_template_id');
    }

    /**
     * Calculate weighted total score from criterion scores and template weights.
     */
    public static function calculateTotal(array $scores, array $criteria): int
    {
        $total = 0;
        $totalWeight = 0;

        foreach ($criteria as $criterion) {
            $label = $criterion['label'];
            $weight = $criterion['weight'] ?? 0;
            $score = $scores[$label] ?? 0; // 1-5
            $total += ($score / 5) * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? (int) round($total) : 0;
    }
}
