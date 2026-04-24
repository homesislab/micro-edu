<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => function () use ($request) {
                $user = $request->user();
                if (! $user) {
                    return [
                        'user' => null,
                    ];
                }

                $activeAcademyId = session('active_academy_id');
                $activeAcademy = $activeAcademyId ? \App\Models\Academy::find($activeAcademyId) : null;

                return [
                    'user' => $user,
                    'user_academies' => $user->academies()->get(),
                    'academy' => $activeAcademy,
                ];
            },
        ]);
    }
}
