<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Default Organization
        $org = Organization::create([
            'name' => 'Main Academy',
            'slug' => 'main-academy',
            'branding_settings' => [
                'primary_color' => '#4f46e5',
                'secondary_color' => '#10b981',
            ]
        ]);

        // 2. Assign all existing users and courses to this organization
        // Note: Using withoutGlobalScopes to ensure we can see them for the migration
        User::withoutGlobalScopes()->update(['organization_id' => $org->id]);
        Course::withoutGlobalScopes()->update(['organization_id' => $org->id]);
    }
}
