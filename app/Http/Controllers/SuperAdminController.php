<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('SuperAdmin/Dashboard', [
            'stats' => [
                'total_academies' => Academy::count(),
                'total_users'     => User::count(),
                'active_tenants'  => Academy::where('status', 'active')->count(),
            ],
            'academies' => Academy::withCount('users')->latest()->get()
        ]);
    }

    public function storeAcademy(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'user_quota' => 'required|integer|min:1',
        ]);

        Academy::create([
            'name'       => $validated['name'],
            'slug'       => Str::slug($validated['name']),
            'status'     => 'active',
            'is_public'  => true,
        ]);

        return back()->with('success', 'Academy created successfully.');
    }

    public function updateAcademy(Request $request, Academy $academy)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'status' => 'required|in:active,suspended',
        ]);

        $academy->update($validated);

        return back()->with('success', 'Academy updated successfully.');
    }
}
