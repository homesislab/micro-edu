<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\Module;
use App\Models\CurriculumItem;
use App\Models\EvaluationL3Assignment;
use App\Models\TestQuestion;
use App\Models\RubricTemplate;
use App\Models\UserCertificate;
use App\Services\MediaGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ExpertController extends Controller
{
    /**
     * List assignments for review
     */
    public function index()
    {
        $expert = auth()->user();

        // Only fetch pending assignments for courses owned by THIS expert
        $assignments = EvaluationL3Assignment::with(['enrollment.user', 'enrollment.course'])
            ->where('status', 'pending')
            ->whereHas('enrollment.course', function ($q) use ($expert) {
                $q->where('expert_id', $expert->id);
            })
            ->latest()
            ->get();

        $courses = Course::where('expert_id', $expert->id)
            ->withCount('enrollments')
            ->with(['enrollments.user', 'enrollments.l1Feedback'])
            ->get();

        $defaultTemplate = RubricTemplate::where('is_default', true)->first();

        $coursesCount = $courses->count();
        
        // Calculate dynamic stats for the mockup
        $totalStudents = $courses->sum('enrollments_count');
        $certificationsIssued = \App\Models\Enrollment::whereIn('course_id', $courses->pluck('id'))
            ->where('status', 'completed')
            ->count();
            
        // Avg Success Velocity: average of (pretest to posttest delta) or progress
        $avgVelocity = \App\Models\Enrollment::whereIn('course_id', $courses->pluck('id'))
            ->whereNotNull('posttest_score')
            ->avg('score_delta') ?? 85; // Fallback to 85 as per mockup if no data

        return Inertia::render('Expert/Dashboard', [
            'assignments' => $assignments,
            'courses' => $courses,
            'defaultTemplate' => $defaultTemplate,
            'expertStats' => [
                'total_students' => $totalStudents,
                'certifications_issued' => $certificationsIssued,
                'success_velocity' => (int)$avgVelocity,
            ]
        ]);
    }

    /**
     * Display the expert's programs/courses
     */
    public function programs()
    {
        $expert = auth()->user();

        // Fetch expert's courses with enrollment counts and section info
        $courses = Course::where('expert_id', $expert->id)
            ->withCount('enrollments')
            ->with(['section:id,name'])
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'price' => $course->price ?? 0,
                    'enrollments_count' => $course->enrollments_count,
                    'section' => $course->section,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                ];
            });

        // Calculate global statistics
        $stats = [
            'totalStudents' => $courses->sum('enrollments_count'),
            'activeCertifications' => UserCertificate::where('issued_to', $expert->id)->count(),
            'averageSuccessVelocity' => $this->calculateSuccessVelocity($expert->id),
        ];

        return Inertia::render('Expert/MyPrograms', [
            'courses' => $courses,
            'stats' => $stats,
        ]);
    }

    /**
     * Calculate the success velocity for an expert (average completion rate)
     */
    private function calculateSuccessVelocity($expertId)
    {
        $courses = Course::where('expert_id', $expertId)->get();
        
        if ($courses->isEmpty()) {
            return 0;
        }

        $totalEnrollments = 0;
        $completedEnrollments = 0;

        foreach ($courses as $course) {
            $enrollments = $course->enrollments()->get();
            $totalEnrollments += $enrollments->count();
            $completedEnrollments += $enrollments->where('status', 'completed')->count();
        }

        if ($totalEnrollments === 0) {
            return 0;
        }

        return round(($completedEnrollments / $totalEnrollments) * 100);
    }

    /**
     * Display review queue for pending assignments (Master-Detail view)
     */
    public function reviewQueue()
    {
        $expert = auth()->user();
        
        // Get all pending assignments for this expert (across all their courses)
        $assignments = EvaluationL3Assignment::with(['enrollment.user', 'enrollment.course'])
            ->where('status', 'pending')
            ->whereHas('enrollment.course', function ($query) {
                $query->where('expert_id', auth()->id());
            })
            ->latest('created_at')
            ->get();

        // Get default rubric template
        $defaultTemplate = RubricTemplate::where('is_default', true)->first();

        return Inertia::render('Expert/ReviewQueue', [
            'assignments' => $assignments,
            'defaultTemplate' => $defaultTemplate
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'passing_grade' => 'required|integer|min:0|max:100',
        ]);

        $course = Course::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'passing_grade' => $validated['passing_grade'],
            'expert_id' => auth()->id(),
            'organization_id' => auth()->user()->organization_id,
            'status' => 'draft',
        ]);

        return redirect()->route('expert.courses.builder', $course->id)
            ->with('success', 'Program designed successfully! Start building your curriculum.');
    }

    protected function assertCourseOwner(Course $course)
    {
        if ($course->expert_id !== auth()->id()) {
            abort(403);
        }
    }

    public function courseBuilder(Request $request, Course $course)
    {
        $this->assertCourseOwner($course);
        $course->load(['modules.curriculumItems', 'testQuestions']);
        return Inertia::render('Expert/CourseBuilder', [
            'course' => $course,
            'tab' => $request->query('tab', 'curriculum'),
        ]);
    }

    public function storeMaterial(Request $request, Course $course)
    {
        $this->assertCourseOwner($course);

        $request->validate([
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:video,text,document,exercise',
            'content'     => 'nullable|string',
            'order_index' => 'nullable|integer|min:0',
            'rubric_json' => 'nullable|array',
        ]);

        $course->materials()->create($request->only(['title', 'type', 'content', 'order_index', 'rubric_json']));

        return back();
    }

    public function updateMaterial(Request $request, CourseMaterial $material)
    {
        if ($material->course->expert_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:video,text,document,exercise',
            'content'     => 'nullable|string',
            'order_index' => 'nullable|integer|min:0',
            'rubric_json' => 'nullable|array',
        ]);

        $material->update($request->only(['title', 'type', 'content', 'order_index', 'rubric_json']));

        return back();
    }

    public function deleteMaterial(CourseMaterial $material)
    {
        if ($material->course->expert_id !== auth()->id()) {
            abort(403);
        }

        $material->delete();
        return back();
    }

    // --- ARCHITECT STUDIO: MODULES ---

    public function storeModule(Request $request, Course $course)
    {
        $this->assertCourseOwner($course);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $course->modules()->create([
            'title' => $request->title,
            'description' => $request->description,
            'order' => $course->modules()->count(),
        ]);

        return back();
    }

    public function updateModule(Request $request, Module $module)
    {
        if ($module->course->expert_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $module->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return back();
    }

    public function deleteModule(Module $module)
    {
        if ($module->course->expert_id !== auth()->id()) {
            abort(403);
        }

        $module->delete();
        return back();
    }

    // --- ARCHITECT STUDIO: ITEMS ---

    public function storeItem(Request $request, Course $course)
    {
        $this->assertCourseOwner($course);

        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:literal,visual,knowledge,exercise',
            'content' => 'nullable|string',
            'is_capstone' => 'boolean',
            'rubric_json' => 'nullable|array',
            'order' => 'nullable|integer'
        ]);

        CurriculumItem::create([
            'module_id' => $request->module_id,
            'title' => $request->title,
            'type' => $request->type,
            'content_json' => [
                'body' => $request->content,
                'description' => $request->content // compatible with legacy
            ],
            'is_capstone' => $request->is_capstone ?? false,
            'rubric_json' => $request->rubric_json,
            'order' => $request->order ?? 0,
        ]);

        return back();
    }

    public function updateItem(Request $request, CurriculumItem $item)
    {
        if ($item->module->course->expert_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:literal,visual,knowledge,exercise',
            'content' => 'nullable|string',
            'is_capstone' => 'boolean',
            'rubric_json' => 'nullable|array',
        ]);

        $item->update([
            'title' => $request->title,
            'type' => $request->type,
            'content_json' => [
                'body' => $request->content,
                'description' => $request->content
            ],
            'is_capstone' => $request->is_capstone ?? false,
            'rubric_json' => $request->rubric_json,
        ]);

        return back();
    }

    public function deleteItem(CurriculumItem $item)
    {
        if ($item->module->course->expert_id !== auth()->id()) {
            abort(403);
        }

        $item->delete();
        return back();
    }

    public function storeQuestion(Request $request, Course $course)
    {
        $this->assertCourseOwner($course);

        $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|in:pretest,posttest',
            'options' => 'required|array',
            'correct_answer' => 'required|string',
            'order' => 'nullable|integer|min:0',
        ]);

        $course->testQuestions()->create([
            'question_text' => $request->question_text,
            'type' => $request->type,
            'options' => $request->options,
            'correct_key' => $request->correct_answer,
            'order' => $request->order,
        ]);

        return back();
    }

    public function updateQuestion(Request $request, TestQuestion $question)
    {
        if ($question->course->expert_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|in:pretest,posttest',
            'options' => 'required|array',
            'correct_answer' => 'required|string',
            'order' => 'nullable|integer|min:0',
        ]);

        $question->update([
            'question_text' => $request->question_text,
            'type' => $request->type,
            'options' => $request->options,
            'correct_key' => $request->correct_answer,
            'order' => $request->order,
        ]);

        return back();
    }

    public function deleteQuestion(TestQuestion $question)
    {
        if ($question->course->expert_id !== auth()->id()) {
            abort(403);
        }

        $question->delete();
        return back();
    }

    public function generateFlyer(Course $course, MediaGeneratorService $generator)
    {
        $this->assertCourseOwner($course);

        $path = $generator->generateFlyer($course);
        
        if ($path) {
            return back()->with('success', 'Flyer generated successfully!');
        }

        return back()->with('error', 'Failed to generate flyer.');
    }

    public function updateSettings(Request $request, Course $course)
    {
        $this->assertCourseOwner($course);

        $validated = $request->validate([
            'attendance_code' => 'nullable|string|max:50',
            'zoom_link' => 'nullable|url',
            'event_time' => 'nullable|date',
            'passing_grade' => 'required|integer|min:0|max:100',
        ]);

        $course->update($validated);

        return back()->with('success', 'Settings updated successfully!');
    }

    public function downloadReport(Course $course)
    {
        if ($course->expert_id !== auth()->user()->id) abort(403);

        $enrollments = $course->enrollments()->with(['user', 'l1Feedback'])->get();
        
        $filename = "report-" . Str::slug($course->title) . "-" . date('Y-m-d') . ".csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Name', 'Email', 'Attendance Status', 'Attended At', 'Pre-Test', 'Post-Test', 'Delta', 'L1 Rating', 'Current Status'];

        $callback = function() use($enrollments, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($enrollments as $enrollment) {
                fputcsv($file, [
                    $enrollment->user->name,
                    $enrollment->user->email,
                    $enrollment->attendance_status,
                    $enrollment->attended_at ? $enrollment->attended_at->format('Y-m-d H:i') : '-',
                    $enrollment->pretest_score ?? '0',
                    $enrollment->posttest_score ?? '0',
                    $enrollment->score_delta ?? '0',
                    $enrollment->l1Feedback?->rating ?? '-',
                    $enrollment->status || 'unknown'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display a dedicated analytics/tracker page
     */
    public function analytics(Request $request)
    {
        $expert = auth()->user();
        
        $courses = Course::where('expert_id', $expert->id)
            ->withCount('enrollments')
            ->with(['enrollments.user', 'enrollments.l1Feedback'])
            ->get();

        return Inertia::render('Expert/Analytics', [
            'courses' => $courses,
            'selectedCourseId' => $request->query('course_id')
        ]);
    }
}
