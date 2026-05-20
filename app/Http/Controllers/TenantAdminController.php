<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class TenantAdminController extends Controller
{
    public function dashboard()
    {
        $academyId = auth()->user()->academy_id;

        return Inertia::render('TenantAdmin/Dashboard', [
            'stats' => [
                'total_employees'    => User::where('academy_id', $academyId)->count(),
                'active_enrollments' => Enrollment::whereHas('course', function ($q) use ($academyId) {
                    $q->where('academy_id', $academyId);
                })->count(),
                'completion_rate'    => $this->calcCompletionRate($academyId),
                'expert_count'       => User::where('academy_id', $academyId)->where('role', 'expert')->count(),
            ],
            'recent_activity' => Enrollment::with(['user', 'course'])
                ->whereHas('course', function ($q) use ($academyId) {
                    $q->where('academy_id', $academyId);
                })
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function userIndex()
    {
        $academyId = auth()->user()->academy_id;
        $users = User::where('academy_id', $academyId)->latest()->paginate(15);

        return Inertia::render('TenantAdmin/Users/Index', [
            'users' => $users
        ]);
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path   = $request->file('file')->getRealPath();
        $data   = array_map('str_getcsv', file($path));
        $header = array_shift($data); // skip header row

        $academyId = auth()->user()->academy_id;
        $count     = 0;

        foreach ($data as $row) {
            if (count($row) < 2) continue;

            [$name, $email] = $row;

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name'       => $name,
                    'password'   => Hash::make('password123'),
                    'role'       => 'user',
                    'academy_id' => $academyId,
                ]
            );
            $count++;
        }

        return back()->with('success', "Successfully imported {$count} users.");
    }

    private function calcCompletionRate(int $academyId): int
    {
        $total     = Enrollment::whereHas('course', fn($q) => $q->where('academy_id', $academyId))->count();
        $completed = Enrollment::whereHas('course', fn($q) => $q->where('academy_id', $academyId))
            ->where('status', 'completed')->count();

        return $total > 0 ? (int) round(($completed / $total) * 100) : 0;
    }
}
