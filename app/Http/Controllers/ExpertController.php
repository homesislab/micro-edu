<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\Module;
use App\Models\CurriculumItem;
use App\Models\EvaluationL3Assignment;
use App\Models\QuizTemplate;
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
        $assignments = EvaluationL3Assignment::with(['enrollment.user', 'enrollment.course', 'curriculumItem'])
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

    public function deleteCourse(Course $course)
    {
        $this->assertCourseOwner($course);
        $course->delete();
        return back()->with('success', 'Program deleted successfully.');
    }

    public function updateStatus(Request $request, Course $course)
    {
        $this->assertCourseOwner($course);
        $validated = $request->validate([
            'status' => 'required|string|in:draft,published,archived'
        ]);

        $course->update(['status' => $validated['status']]);
        return back()->with('success', 'Program status updated to ' . $validated['status']);
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
            'sub_type' => 'nullable|string|max:50',
            'content' => 'nullable|string',
            'assessment_mode' => 'nullable|string|in:diagnostic,mastery',
            'passing_grade' => 'nullable|integer|min:0|max:100',
            'is_capstone' => 'boolean',
            'rubric_json' => 'nullable|array',
            'order' => 'nullable|integer'
        ]);

        $rawContent = $request->input('content');
        // If it's already a JSON string (quiz settings), decode it. Otherwise wrap as array.
        $contentValue = null;
        if ($rawContent) {
            $decoded = json_decode($rawContent, true);
            $contentValue = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                ? $decoded
                : ['body' => $rawContent];
        }

        CurriculumItem::create([
            'module_id' => $request->input('module_id'),
            'title' => $request->input('title'),
            'type' => $request->input('type'),
            'sub_type' => $request->input('sub_type'),
            'assessment_mode' => $request->input('assessment_mode') ?? 'diagnostic',
            'passing_grade' => $request->input('passing_grade'),
            'content' => $contentValue,
            'is_capstone' => $request->input('is_capstone') ?? false,
            'rubric_json' => $request->input('rubric_json'),
            'order' => $request->input('order') ?? 0,
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
            'sub_type' => 'nullable|string|max:50',
            'content' => 'nullable|string',
            'assessment_mode' => 'nullable|string|in:diagnostic,mastery',
            'passing_grade' => 'nullable|integer|min:0|max:100',
            'is_capstone' => 'boolean',
            'rubric_json' => 'nullable|array',
        ]);

        $rawContent = $request->input('content');
        $contentValue = null;
        if ($rawContent) {
            $decoded = json_decode($rawContent, true);
            $contentValue = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                ? $decoded
                : ['body' => $rawContent];
        }

        $item->update([
            'title' => $request->input('title'),
            'type' => $request->input('type'),
            'sub_type' => $request->input('sub_type'),
            'assessment_mode' => $request->input('assessment_mode') ?? 'diagnostic',
            'passing_grade' => $request->input('passing_grade'),
            'content' => $contentValue,
            'is_capstone' => $request->input('is_capstone') ?? false,
            'rubric_json' => $request->input('rubric_json'),
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
            'type'          => 'required|in:pretest,posttest',
            'options'       => 'required|array',
            'correct_answer'=> 'required|string',
            'order'         => 'nullable|integer|min:0',
            'weight'        => 'nullable|integer|min:1|max:20',
            'competency_tags' => 'nullable|array',
        ]);

        $course->testQuestions()->create([
            'question_text'   => $request->question_text,
            'type'            => $request->type,
            'options'         => $request->options,
            'correct_key'     => $request->correct_answer,
            'order'           => $request->order,
            'weight'          => $request->weight ?? 1,
            'competency_tags' => $request->competency_tags ?? [],
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
            'type'          => 'required|in:pretest,posttest',
            'options'       => 'required|array',
            'correct_answer'=> 'required|string',
            'order'         => 'nullable|integer|min:0',
            'weight'        => 'nullable|integer|min:1|max:20',
            'competency_tags' => 'nullable|array',
        ]);

        $question->update([
            'question_text'   => $request->question_text,
            'type'            => $request->type,
            'options'         => $request->options,
            'correct_key'     => $request->correct_answer,
            'order'           => $request->order,
            'weight'          => $request->weight ?? $question->weight,
            'competency_tags' => $request->competency_tags ?? $question->competency_tags,
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
            ->with([
                'enrollments.user', 
                'enrollments.l1Feedback', 
                'enrollments.l2Tests.curriculumItem', 
                'enrollments.l3Assignments.curriculumItem'
            ])
            ->get();

        return Inertia::render('Expert/Analytics', [
            'courses' => $courses,
            'selectedCourseId' => $request->query('course_id')
        ]);
    }

    // --- QUIZ BANK ---

    public function quizBank()
    {
        $expert = auth()->user();
        $templates = QuizTemplate::where('expert_id', $expert->id)->latest()->get();

        return Inertia::render('Expert/QuizBank/Index', [
            'templates' => $templates
        ]);
    }

    public function storeQuizTemplate(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'sub_type' => 'required|in:pre_test,quiz,final_exam',
            'assessment_mode' => 'required|in:diagnostic,mastery',
            'passing_grade' => 'nullable|integer|min:0|max:100',
            'content' => 'nullable|array'
        ]);

        QuizTemplate::create([
            'expert_id' => auth()->id(),
            'title' => $request->input('title'),
            'sub_type' => $request->input('sub_type'),
            'assessment_mode' => $request->input('assessment_mode'),
            'passing_grade' => $request->input('passing_grade'),
            'content' => $request->input('content') ?? []
        ]);

        return back();
    }

    public function updateQuizTemplate(Request $request, QuizTemplate $template)
    {
        if ($template->expert_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'sub_type' => 'required|in:pre_test,quiz,final_exam',
            'assessment_mode' => 'required|in:diagnostic,mastery',
            'passing_grade' => 'nullable|integer|min:0|max:100',
            'content' => 'nullable|array'
        ]);

        $template->update($request->only(['title', 'sub_type', 'assessment_mode', 'passing_grade', 'content']));

        return back();
    }

    public function deleteQuizTemplate(QuizTemplate $template)
    {
        if ($template->expert_id !== auth()->id()) {
            abort(403);
        }

        $template->delete();
        return back();
    }

    public function getQuizBank()
    {
        return response()->json(
            QuizTemplate::where('expert_id', auth()->id())->latest()->get()
        );
    }

    // --- Rubric Bank Methods ---

    public function rubricBank()
    {
        $templates = RubricTemplate::where('expert_id', auth()->id())
            ->orderBy('name')
            ->get();

        return Inertia::render('Expert/RubricBank/Index', [
            'templates' => $templates
        ]);
    }

    public function storeRubricTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'criteria_json' => 'required|array',
            'is_default' => 'boolean'
        ]);

        RubricTemplate::create([
            'expert_id' => auth()->id(),
            'name' => $request->name,
            'criteria_json' => $request->criteria_json,
            'is_default' => $request->is_default ?? false,
        ]);

        return back()->with('success', 'Rubric template added to library!');
    }

    public function updateRubricTemplate(Request $request, RubricTemplate $template)
    {
        $this->assertCourseOwner($template->expert_id); // Reusing ownership logic

        $request->validate([
            'name' => 'required|string|max:255',
            'criteria_json' => 'required|array',
            'is_default' => 'boolean'
        ]);

        $template->update($request->only('name', 'criteria_json', 'is_default'));

        return back()->with('success', 'Rubric template updated!');
    }

    public function deleteRubricTemplate(RubricTemplate $template)
    {
        $this->assertCourseOwner($template->expert_id);
        $template->delete();
        return back()->with('success', 'Rubric template removed from library.');
    }

    public function rubricBankApi()
    {
        return response()->json(
            RubricTemplate::where('expert_id', auth()->id())->orderBy('name')->get()
        );
    }

    // --- QUIZ ENGINE: Competency Analytics ---

    /**
     * Aggregate enrollments' quiz results per competency tag.
     * Returns: [ tag => [ total_points, earned_points, pct ] ]
     */
    public function getCompetencyAnalytics(Course $course)
    {
        $this->assertCourseOwner($course);

        $questions = $course->testQuestions()->whereNotNull('competency_tags')->get();

        if ($questions->isEmpty()) {
            return response()->json(['tags' => [], 'difficulty' => []]);
        }

        // Build difficulty index — questions answered wrong most often
        // (Placeholder structure; real data requires quiz_attempts table)
        $difficulty = $questions->map(fn($q) => [
            'id'          => $q->id,
            'text'        => Str::limit($q->question_text, 60),
            'weight'      => $q->weight,
            'tags'        => $q->competency_tags ?? [],
            'wrong_rate'  => 0, // Will be populated from quiz_attempts when available
        ])->values();

        // Aggregate points available per competency tag
        $tagTotals = [];
        foreach ($questions as $q) {
            foreach (($q->competency_tags ?? []) as $tag) {
                if (!isset($tagTotals[$tag])) {
                    $tagTotals[$tag] = ['total_weight' => 0, 'question_count' => 0];
                }
                $tagTotals[$tag]['total_weight']   += $q->weight;
                $tagTotals[$tag]['question_count'] += 1;
            }
        }

        return response()->json([
            'tags'       => $tagTotals,
            'difficulty' => $difficulty,
        ]);
    }

    /**
     * Calculate weighted score for a set of answers.
     * $answers = [ question_id => selected_key ]
     * Returns: [ raw_score, total_weight, percentage ]
     */
    public static function calculateWeightedScore(array $answers, $questions): array
    {
        $earned = 0;
        $total  = 0;

        foreach ($questions as $q) {
            $total += $q->weight;
            if (isset($answers[$q->id]) && $answers[$q->id] === $q->correct_key) {
                $earned += $q->weight;
            }
        }

        return [
            'earned'     => $earned,
            'total'      => $total,
            'percentage' => $total > 0 ? round(($earned / $total) * 100, 1) : 0,
        ];
    }
}
