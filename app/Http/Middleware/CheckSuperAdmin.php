<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (auth()->check() && in_array($user->role, ['super_admin', 'admin'])) {
            return $next($request);
        }

        abort(403, 'Unauthorized. Super Admin access required.');
    }
}
