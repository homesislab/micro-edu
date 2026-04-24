<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ExpertController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TenantAdminController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\AcademyController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CourseBuilderController;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\PortfolioController;
use Inertia\Inertia;

// --- PUBLIC ROUTES ---
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

Route::get('/catalog', [EventController::class, 'index'])->name('catalog');
Route::get('/c/{slug}', [EventController::class, 'show'])->name('events.show');
Route::post('/c/{course}/register', [EventController::class, 'register'])->name('events.register');
Route::get('/u/{username}', [PortfolioController::class, 'show'])->name('portfolio.show');

// --- AUTHENTICATED GLOBAL ROUTES ---
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/lobby', [AcademyController::class, 'lobby'])->name('lobby');
    Route::get('/academy/{academy}/enter', [AcademyController::class, 'enter'])->name('academy.enter');
    Route::get('/academy/create', [AcademyController::class, 'create'])->name('academy.create');
    Route::post('/academy', [AcademyController::class, 'store'])->name('academy.store');
    
    // --- AUTHENTICATED & ACADEMY SCOPED ROUTES ---
    Route::middleware(['check_academy'])->group(function () {
        
        Route::match(['get', 'post'], '/dashboard', function (Request $request) {
            $user = auth()->user();
            
            // Expert & Global Admin Redirect
            if ($user->role === 'expert' || $user->role === 'admin') {
                return redirect()->route('expert.dashboard');
            }

            // Tenant/Regional Admin Redirect
            if ($user->role === 'tenant_admin' || $user->role === 'organization_admin') {
                return redirect()->route('tenant.dashboard');
            }

            // Simulator Actions (for testing)
            if ($request->isMethod('post') && $request->has('action')) {
                if ($request->action === 'simulate_finish_content') {
                    // Find the first enrollment and update it
                    $enrollment = Enrollment::where('user_id', $user->id)->first();
                    if ($enrollment && $enrollment->status === 'pre_test_done') {
                        $enrollment->update(['status' => 'content_done']);
                    }
                }
                return back();
            }

            // Default: Student Dashboard
            $props = [
                'enrollments' => Enrollment::where('user_id', $user->id)->with('course')->get(),
                'certificates' => \App\Models\UserCertificate::where('user_id', $user->id)->with('course')->get(),
                'availableCourses' => Course::where('is_public', true)->limit(5)->get(),
                'leaderboard' => User::orderBy('points', 'desc')->limit(10)->get(),
            ];

            return Inertia::render('Dashboard', $props);
        })->name('dashboard');

        Route::get('/academy/settings', [AcademyController::class, 'settings'])->name('academy.settings');
        Route::patch('/academy/settings', [AcademyController::class, 'updateSettings'])->name('academy.settings.update');

        Route::post('/courses/{course}/enroll', function (Course $course) {
            $enrollment = Enrollment::firstOrCreate([
                'user_id' => auth()->id(),
                'course_id' => $course->id,
            ]);
            return back();
        })->name('courses.enroll');

        Route::get('/courses/{course}/questions/{type}', function (Course $course, string $type) {
            return response()->json($course->testQuestions()->where('type', $type)->orderBy('order')->get());
        })->name('courses.questions');

        Route::post('/enrollments/{enrollment}/test/{type}', [EvaluationController::class, 'submitTest'])->name('evaluation.submitTest');
        Route::post('/enrollments/{enrollment}/feedback', [EvaluationController::class, 'submitFeedback'])->name('evaluation.submitFeedback');
        Route::post('/enrollments/{enrollment}/assignment', [EvaluationController::class, 'submitAssignment'])->name('evaluation.submitAssignment');
        Route::post('/enrollments/{enrollment}/claim-attendance', [EvaluationController::class, 'claimAttendance'])->name('evaluation.claimAttendance');
        Route::post('/enrollments/{enrollment}/fast-track', [EvaluationController::class, 'fastTrack'])->name('evaluation.fastTrack');

        // Monetization & Payments
        Route::get('/payment/checkout/{course}', [\App\Http\Controllers\PaymentController::class, 'checkout'])->name('payment.checkout');
        Route::post('/payment/process/{course}', [\App\Http\Controllers\PaymentController::class, 'process'])->name('payment.process');

        // Community Feed
        Route::get('/courses/{course}/community', [\App\Http\Controllers\CommunityFeedController::class, 'index'])->name('community.index');
        Route::post('/courses/{course}/threads', [\App\Http\Controllers\CommunityFeedController::class, 'storeThread'])->name('community.threads.store');
        Route::post('/threads/{thread}/comments', [\App\Http\Controllers\CommunityFeedController::class, 'storeComment'])->name('community.comments.store');

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');

        // Expert & Admin Sub-Tiers
        Route::middleware(['auth'])->prefix('expert')->group(function () {
            Route::get('/dashboard', [ExpertController::class, 'index'])->name('expert.dashboard');
            Route::get('/programs', [ExpertController::class, 'programs'])->name('programs.index');
            Route::get('/analytics', [ExpertController::class, 'analytics'])->name('expert.analytics');
            Route::get('/review-queue', [ExpertController::class, 'reviewQueue'])->name('expert.review-queue');
            Route::post('/rubric/{assignment}', [\App\Http\Controllers\RubricController::class, 'store'])->name('expert.rubric.store');
            
            // Super Admin (Regional/Platform Management within Academy context if needed, or global)
            Route::middleware(['super_admin'])->prefix('super')->name('super.')->group(function () {
                Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
            });

            // Tenant Admin (Local HRD)
            Route::middleware(['tenant_admin'])->prefix('admin')->name('tenant.')->group(function () {
                Route::get('/dashboard', [TenantAdminController::class, 'dashboard'])->name('dashboard');
                Route::get('/users', [TenantAdminController::class, 'userIndex'])->name('users.index');
                Route::post('/users/bulk-import', [TenantAdminController::class, 'bulkImport'])->name('users.bulk-import');
            });

            Route::get('/courses/{course}/builder', [ExpertController::class, 'courseBuilder'])->name('expert.courses.builder');
            Route::get('/courses/{course}/report', [ExpertController::class, 'downloadReport'])->name('expert.courses.report');
            Route::post('/courses', [ExpertController::class, 'store'])->name('expert.courses.store');
            Route::post('/expert/ai/generate-program', [AIController::class, 'generateProgram'])->name('expert.ai.generate-program');
            Route::post('/expert/ai/generate-curriculum/{course}', [AIController::class, 'generateCurriculum'])->name('expert.ai.generate-curriculum');
            Route::post('/expert/ai/generate-questions/{course}', [AIController::class, 'generateQuestions'])->name('expert.ai.generate-questions');
            
            Route::post('/courses/{course}/flyer', [ExpertController::class, 'generateFlyer'])->name('expert.courses.flyer');
            Route::patch('/courses/{course}/settings', [ExpertController::class, 'updateSettings'])->name('expert.courses.settings');
            Route::post('/courses/{course}/materials', [ExpertController::class, 'storeMaterial'])->name('expert.materials.store');
            Route::patch('/materials/{material}', [ExpertController::class, 'updateMaterial'])->name('expert.materials.update');
            Route::delete('/materials/{material}', [ExpertController::class, 'deleteMaterial'])->name('expert.materials.delete');

            // Module Routes
            Route::post('/courses/{course}/modules', [ExpertController::class, 'storeModule'])->name('expert.modules.store');
            Route::patch('/modules/{module}', [ExpertController::class, 'updateModule'])->name('expert.modules.update');
            Route::delete('/modules/{module}', [ExpertController::class, 'deleteModule'])->name('expert.modules.delete');

            // Item Routes
            Route::post('/courses/{course}/items', [ExpertController::class, 'storeItem'])->name('expert.items.store');
            Route::patch('/items/{item}', [ExpertController::class, 'updateItem'])->name('expert.items.update');
            Route::delete('/items/{item}', [ExpertController::class, 'deleteItem'])->name('expert.items.delete');
            Route::post('/courses/{course}/questions', [ExpertController::class, 'storeQuestion'])->name('expert.questions.store');
            Route::patch('/questions/{question}', [ExpertController::class, 'updateQuestion'])->name('expert.questions.update');
            Route::delete('/questions/{question}', [ExpertController::class, 'deleteQuestion'])->name('expert.questions.delete');

            Route::resource('blogs', BlogController::class)->names([
                'index' => 'expert.blogs.index',
                'create' => 'expert.blogs.create',
                'store' => 'expert.blogs.store',
                'edit' => 'expert.blogs.edit',
                'update' => 'expert.blogs.update',
                'destroy' => 'expert.blogs.destroy',
            ]);
        });
    });
});

require __DIR__.'/auth.php';
