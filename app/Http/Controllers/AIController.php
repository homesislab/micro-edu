<?php

namespace App\Http\Controllers;

use App\Models\Course;
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

    public function generateCurriculum(Course $course, AIService $aiService)
    {
        // Batch generate modules
        $modules = $aiService->generateCurriculumModules($course);
        
        foreach ($modules as $module) {
            $course->materials()->create([
                'title' => $module['title'],
                'type' => $module['type'],
                'content' => $module['content'],
                'order_index' => $module['order_index'] ?? 0,
            ]);
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
}
