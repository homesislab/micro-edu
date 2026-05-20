<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (auth()->check() && in_array($user->role, ['tenant_admin', 'organization_admin'])) {
            return $next($request);
        }

        abort(403, 'Unauthorized. Tenant Admin access required.');
    }
}
