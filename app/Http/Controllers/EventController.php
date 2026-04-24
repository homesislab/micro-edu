<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    public function index()
    {
        $courses = Course::with('expert')->get();
        return Inertia::render('Course/Catalog', [
            'courses' => $courses
        ]);
    }

    public function show(string $slug)
    {
        // For simplicity, we use ID as slug if no slug field exists, or use title slug
        $course = Course::where('id', $slug)->orWhere('title', 'like', '%' . str_replace('-', ' ', $slug) . '%')->with('expert')->firstOrFail();

        return Inertia::render('Event/Landing', [
            'course' => $course,
            'isEnrolled' => auth()->check() ? Enrollment::where('user_id', auth()->id())->where('course_id', $course->id)->exists() : false
        ]);
    }

    public function register(Request $request, Course $course, \App\Services\WhatsAppService $wa)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Branching: Free vs Paid
        if ($course->price > 0) {
            return redirect()->route('payment.checkout', ['course' => $course->id]);
        }

        // Free Course: Direct Enrollment
        $enrollment = Enrollment::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'course_id' => $course->id,
            ],
            [
                'status' => 'enrolled', // Explicitly set the initial status
            ]
        );

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $wa->sendWelcome($user, $course->title);

        return redirect()->route('dashboard')->with('success', 'Registrasi berhasil! Selamat datang di program Gratis ' . $course->title);
    }
}
