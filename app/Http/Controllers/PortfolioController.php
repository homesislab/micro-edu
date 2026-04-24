<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Enrollment;
use App\Models\Academy;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PortfolioController extends Controller
{
    /**
     * Display the global professional portfolio for a user.
     */
    public function show($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        // Fetch all academies the user is a member of
        $academies = $user->academies()
            ->where('is_public', true) // Only show public academies on public profile
            ->get();

        // Fetch global achievements (Completed Courses) across ALL academies
        // We bypass the AcademyScope to show the user's full history
        $achievements = Enrollment::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->with(['course' => function($query) {
                $query->withoutGlobalScopes();
            }])
            ->get();

        // Fetch Proof of Work (Approved Kirkpatrick L3 Assignments)
        $proofs = Enrollment::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->whereHas('l3Assignment', function($query) {
                $query->where('status', 'approved');
            })
            ->with(['course' => function($query) {
                $query->withoutGlobalScopes();
            }, 'l3Assignment'])
            ->get();

        return Inertia::render('Social/Portfolio', [
            'profile' => [
                'name' => $user->name,
                'username' => $user->username,
                'bio' => $user->bio,
                'avatar_url' => $user->avatar_url,
                'joined_at' => $user->created_at->format('M Y'),
            ],
            'academies' => $academies,
            'achievements' => $achievements,
            'proofs' => $proofs,
        ]);
    }
}
