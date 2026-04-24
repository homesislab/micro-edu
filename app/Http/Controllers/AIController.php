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
        if ($request->boolean('preview')) {
            try {
                $modules = $aiService->generateCurriculumModules($course, $request->input('prompt'));
                return response()->json($modules);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        // Batch generate modules
        $modules = $request->input('modules', []);
        
        foreach ($modules as $mIndex => $moduleData) {
            $module = $course->modules()->create([
                'title' => $moduleData['title'] ?? 'Module ' . ($mIndex + 1),
                'description' => $moduleData['description'] ?? null,
                'order' => $mIndex,
            ]);

            if (isset($moduleData['items']) && is_array($moduleData['items'])) {
                foreach ($moduleData['items'] as $iIndex => $itemData) {
                    $module->curriculumItems()->create([
                        'title' => $itemData['title'] ?? 'Item ' . ($iIndex + 1),
                        'type' => $itemData['type'] ?? 'literal',
                        'content' => is_array($itemData['content']) ? json_encode($itemData['content']) : ($itemData['content'] ?? ''),
                        'rubric_json' => $itemData['rubric_json'] ?? null,
                        'order' => $iIndex,
                    ]);
                }
            }
        }

        return back()->with('success', 'AI Architect has generated ' . count($modules) . ' modules for you!');
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
