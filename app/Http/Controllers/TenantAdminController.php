<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantAdminController extends Controller
{
    public function dashboard()
    {
        $orgId = auth()->user()->organization_id;

        return Inertia::render('TenantAdmin/Dashboard', [
            'stats' => [
                'total_employees' => User::where('organization_id', $orgId)->count(),
                'active_enrollments' => Enrollment::whereHas('course', function($q) use ($orgId) {
                    $q->where('organization_id', $orgId);
                })->count(),
                'completion_rate' => 78, // Placeholder for aggregation logic
                'expert_count' => User::where('organization_id', $orgId)->where('role', 'expert')->count(),
            ],
            'recent_activity' => Enrollment::with(['user', 'course'])
                ->whereHas('course', function($q) use ($orgId) {
                    $q->where('organization_id', $orgId);
                })
                ->latest()
                ->limit(5)
                ->get()
        ]);
    }

    public function userIndex()
    {
        $orgId = auth()->user()->organization_id;
        $users = User::where('organization_id', $orgId)->latest()->paginate(10);

        return Inertia::render('TenantAdmin/Users/Index', [
            'users' => $users
        ]);
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('file')->getRealPath();
        $data = array_map('str_getcsv', file($path));
        
        $header = array_shift($data);
        $orgId = auth()->user()->organization_id;
        $count = 0;

        foreach ($data as $row) {
            if (count($row) < 2) continue;
            
            $name = $row[0];
            $email = $row[1];

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password123'),
                    'role' => 'user',
                    'organization_id' => $orgId
                ]
            );
            $count++;
        }

        return back()->with('success', "Successfully imported $count users.");
    }
}
