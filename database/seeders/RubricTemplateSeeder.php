<?php

namespace Database\Seeders;

use App\Models\RubricTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class RubricTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expert = User::where('role', 'admin')->orWhere('role', 'expert')->first();

        if ($expert) {
            RubricTemplate::updateOrCreate(
                ['name' => 'L3 Behavior Assessment Rubric'],
                [
                    'expert_id' => $expert->id,
                    'criteria_json' => [
                        ['label' => 'Ketepatan', 'description' => 'The submission accurately reflects the requested behavior.'],
                        ['label' => 'Kreativitas', 'description' => 'Innovative application of concepts in the real world.'],
                        ['label' => 'Dokumentasi', 'description' => 'Evidence is clear, professional, and well-organized.'],
                    ],
                    'is_default' => true
                ]
            );
        }
    }
}
