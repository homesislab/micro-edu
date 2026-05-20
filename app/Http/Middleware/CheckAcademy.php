<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAcademy
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if ($user) {
            $activeAcademyId = session('active_academy_id');

            // If we have an active academy in session, sync it to the user object
            // so that global scopes pick it up automatically.
            if ($activeAcademyId) {
                $user->academy_id = $activeAcademyId;
            } 
            else {
                // If they don't have an active session but only have 1 academy, auto-select it.
                $userAcademies = $user->academies()->get();
                if ($userAcademies->count() === 1) {
                    $singleAcademy = $userAcademies->first();
                    session(['active_academy_id' => $singleAcademy->id]);
                    $user->academy_id = $singleAcademy->id;
                    
                    // Proceed or bump them to dashboard if they are trying to hit lobby
                    if ($request->routeIs('lobby')) {
                        return redirect()->route('dashboard');
                    }
                } 
                // If No active academy and not on lobby/create page, redirect to lobby
                else if (!$request->routeIs('lobby') && !$request->routeIs('academy.create')) {
                    return redirect()->route('lobby');
                }
            }
        }

        return $next($request);
    }
}
