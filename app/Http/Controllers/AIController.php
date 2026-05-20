<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\QuizTemplate;
use App\Services\AIService;
use Illuminate\Http\Request;

class AIController extends Controller
{
    public function generateProgram(Request $request, AIService $aiService)
    {
        $request->validate(['topic' => 'required|string|max:100']);
        $suggestion = $aiService->generateProgramSuggestion($request->topic);
        return response()->json($suggestion);
    }

    public function generateCurriculum(Request $request, Course $course, AIService $aiService)
    {
        if ($course->expert_id !== auth()->id()) {
            abort(403);
        }

        $prompt = $request->input('prompt');

        if ($request->boolean('preview')) {
            try {
                $modules = $aiService->generateCurriculumModules($course, $prompt);
                return response()->json($modules);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        // If modules were pre-built and passed directly (batch commit from preview)
        $modules = $request->input('modules', []);

        // If no pre-built modules provided, call AI directly
        if (empty($modules)) {
            try {
                $modules = $aiService->generateCurriculumModules($course, $prompt);
            } catch (\Exception $e) {
                return back()->with('error', 'AI generation failed: ' . $e->getMessage());
            }
        }

        $count = 0;
        foreach ($modules as $mIndex => $moduleData) {
            $module = $course->modules()->create([
                'title'       => $moduleData['title'] ?? 'Module ' . ($mIndex + 1),
                'description' => $moduleData['description'] ?? null,
                'order'       => $mIndex,
            ]);

            $items = $moduleData['items'] ?? [];
            foreach ($items as $iIndex => $itemData) {
                $module->curriculumItems()->create([
                    'title'      => $itemData['title'] ?? 'Item ' . ($iIndex + 1),
                    'type'       => $itemData['type'] ?? 'literal',
                    'content'    => is_array($itemData['content']) ? json_encode($itemData['content']) : ($itemData['content'] ?? ''),
                    'rubric_json'=> $itemData['rubric_json'] ?? null,
                    'order'      => $iIndex,
                ]);
            }
            $count++;
        }

        return back()->with('success', "AI Architect has generated {$count} modules for your program!");
    }

    public function generateQuestions(Course $course, AIService $aiService)
    {
        // Batch generate questions
        $questions = $aiService->generateCourseQuestions($course);
        
        foreach ($questions as $q) {
            $course->testQuestions()->create([
                'question_text' => $q['question_text'],
                'type' => $q['type'],
                'options' => $q['options'],
                'correct_key' => $q['correct_answer'],
            ]);
        }

        return back()->with('success', 'AI Forge has created ' . count($questions) . ' assessment items!');
    }

    public function generateQuizTemplate(Request $request, AIService $aiService)
    {
        $request->validate(['prompt' => 'required|string|max:1000']);
        
        if ($request->boolean('preview')) {
            try {
                $quizData = $aiService->generateQuizBankTemplate($request->prompt);
                return response()->json($quizData);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        // Final commit
        try {
            QuizTemplate::create([
                'expert_id' => auth()->id(),
                'title' => $request->input('title', 'AI Template'),
                'sub_type' => $request->input('sub_type', 'quiz'),
                'assessment_mode' => $request->input('assessment_mode', 'diagnostic'),
                'passing_grade' => $request->input('passing_grade', 75),
                'content' => $request->input('content', []),
            ]);

            return back()->with('success', 'AI Template has been committed to your bank!');
        } catch (\Exception $e) {
            return back()->with('error', 'Commit failed: ' . $e->getMessage());
        }
    }
}
