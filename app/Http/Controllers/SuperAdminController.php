<?php

namespace App\Http\Controllers;

use App\Models\Organization;
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
                'total_organizations' => Organization::count(),
                'total_users' => User::count(),
                'active_tenants' => Organization::where('status', 'active')->count(),
            ],
            'organizations' => Organization::withCount('users')->latest()->get()
        ]);
    }

    public function storeOrganization(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_quota' => 'required|integer|min:1',
        ]);

        Organization::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'user_quota' => $validated['user_quota'],
            'status' => 'active',
        ]);

        return back()->with('success', 'Organization created successfully.');
    }

    public function updateOrganization(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_quota' => 'required|integer|min:1',
            'status' => 'required|in:active,suspended',
        ]);

        $organization->update($validated);

        return back()->with('success', 'Organization updated successfully.');
    }
}
