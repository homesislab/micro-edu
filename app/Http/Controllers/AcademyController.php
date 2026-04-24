<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AcademyController extends Controller
{
    /**
     * Display the Academy Lobby where users choose their workspace.
     */
    public function lobby()
    {
        // Clear active academy context when returning to the lobby
        session()->forget('active_academy_id');

        $user = auth()->user();
        $academies = $user->academies()->get();

        return Inertia::render('Academy/Lobby', [
            'academies' => $academies,
            'user' => $user
        ]);
    }

    /**
     * Context-switch into a specific academy.
     */
    public function enter(Academy $academy)
    {
        $user = auth()->user();

        // Security: Ensure user is a member of this academy
        if (!$user->academies()->where('academy_id', $academy->id)->exists()) {
            return redirect()->route('lobby')->with('error', 'Unauthorized access to this academy.');
        }

        // Set the active session context
        session(['active_academy_id' => $academy->id]);
        
        // Update user state for global scoping & history
        $user->update(['academy_id' => $academy->id]);
        $user->academies()->updateExistingPivot($academy->id, ['last_accessed_at' => now()]);

        return redirect()->route('dashboard');
    }

    public function create()
    {
        return Inertia::render('Academy/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:academies,slug',
        ]);

        $academy = Academy::create([
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
            'is_public' => true,
            'allow_self_enroll' => true,
            'primary_color' => '#4f46e5',
            'secondary_color' => '#06b6d4',
            'status' => 'active',
        ]);

        $user = auth()->user();
        
        // Attach user to the new academy as 'owner'
        $user->academies()->attach($academy->id, [
            'role' => 'owner',
            'last_accessed_at' => now()
        ]);

        // Automatically enter the new academy
        session(['active_academy_id' => $academy->id]);
        $user->update(['academy_id' => $academy->id]);

        return redirect()->route('dashboard')->with('success', 'Academy created and initialized!');
    }

    public function settings()
    {
        $academy = auth()->user()->currentAcademy()->first();
        if (!$academy) return redirect()->route('lobby');

        $users = $academy->users()->latest()->get(); // Simplified for now

        return Inertia::render('Academy/Settings', [
            'academy' => $academy,
            'members' => $users
        ]);
    }

    public function updateSettings(Request $request)
    {
        $academy = auth()->user()->currentAcademy()->first();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'is_public' => 'required|boolean',
            'allow_self_enroll' => 'required|boolean',
            'primary_color' => 'required|string',
            'secondary_color' => 'required|string',
        ]);

        $academy->update($request->only([
            'name', 'is_public', 'allow_self_enroll', 'primary_color', 'secondary_color'
        ]));

        return back()->with('success', 'Academy settings updated successfully.');
    }
}
