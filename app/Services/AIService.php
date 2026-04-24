<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    /**
     * Generate program suggestions based on a keyword/topic.
     */
    public function generateProgramSuggestion(string $topic): array
    {
        $apiKey = env('GEMINI_API_KEY');
        
        if ($apiKey) {
            try {
                $response = Http::withoutVerifying()->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => "You are a professional educational curriculum architect. Based on the topic '{$topic}', generate a premium micro-education program. 
                                    Return ONLY a JSON object with the following keys:
                                    - title: A high-impact, professional title.
                                    - description: A compelling 2-3 sentence vision description focused on behavioral transformation.
                                    - passing_grade: A suggested integer passing grade between 70 and 90.
                                    
                                    Example: {\"title\": \"Executive Leadership\", \"description\": \"Master neuro-behavioral frameworks...\", \"passing_grade\": 80}"
                                ]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    
                    // Clean potential markdown code blocks if AI returns them
                    $json = trim(str_replace(['```json', '```'], '', $text));
                    $data = json_decode($json, true);

                    if ($data && isset($data['title'], $data['description'])) {
                        return $data;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Gemini API Error: ' . $e->getMessage());
            }
        }

        // --- FALLBACK MOCK TEMPLATES ---
        $topicClean = strtolower(trim($topic));
        
        $templates = [
            'leadership' => [
                'title' => 'Executive Leadership: Architecting High-Performance Cultures',
                'description' => 'Master the neuro-behavioral frameworks of elite leadership. This program transforms managers into architects who design trust, psychological safety, and radical accountability within their teams.',
                'passing_grade' => 85
            ],
            'coding' => [
                'title' => 'Full-Stack Mastery: Building Scalable Digital Ecosystems',
                'description' => 'A deep dive into the blueprint of modern software engineering. From atomic design patterns to cloud-native resilience, learn to build systems that scale with business velocity.',
                'passing_grade' => 80
            ],
            'marketing' => [
                'title' => 'Growth Engineering: Data-Driven Performance Marketing',
                'description' => 'Beyond creative—this is marketing as a science. Master the intersection of behavioral economics, algorithmic optimization, and data-driven storytelling to drive exponential growth.',
                'passing_grade' => 75
            ],
            'design' => [
                'title' => 'Strategic UI/UX: Crafting Premium Digital Experiences',
                'description' => 'Design as a competitive advantage. Focus on cognitive ergonomics, emotional design, and high-fidelity prototyping to create interfaces that feel alive and premium.',
                'passing_grade' => 80
            ]
        ];

        foreach ($templates as $key => $template) {
            if (str_contains($topicClean, $key)) {
                return $template;
            }
        }

        return [
            'title' => 'Expert Module: ' . ucfirst($topic) . ' Strategic Implementation',
            'description' => 'A comprehensive professional development program focused on the vertical mastery of ' . $topic . '. Participants will achieve advanced competency through structured behavioral application and rigorous assessment.',
            'passing_grade' => 75
        ];
    }

    public function generateCurriculumModules(Course $course): array
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) return [];

        try {
            $response = Http::withoutVerifying()->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "You are a senior curriculum designer. Based on the course title '{$course->title}' and description '{$course->description}', generate 4-5 high-quality learning modules.
                                Return ONLY a JSON array of objects with the following keys:
                                - title: A concise, professional module title.
                                - type: Either 'video' or 'text'.
                                - content: For 'video', provide a relevant YouTube ID or URL. For 'text', provide a 200-word instructional narrative.
                                - order_index: The sequence number (1, 2, 3...)."
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $json = trim(str_replace(['```json', '```'], '', $text));
                $data = json_decode($json, true);
                return is_array($data) ? $data : [];
            }
        } catch (\Exception $e) {
            Log::error('Gemini Curriculum Error: ' . $e->getMessage());
        }

        return [];
    }

    public function generateCourseQuestions(Course $course): array
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) return [];

        try {
            $response = Http::withoutVerifying()->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "You are an assessment specialist. Create 8 professional multiple-choice questions for the course '{$course->title}'.
                                - 4 questions for a 'pretest' (diagnostic).
                                - 4 questions for a 'posttest' (mastery).
                                They should be correlated so that we can measure growth.
                                
                                Return ONLY a JSON array of objects with:
                                - question_text: The question.
                                - type: 'pretest' or 'posttest'.
                                - options: Array of 4 objects with {key: 'a'|'b'|'c'|'d', text: 'Value'}.
                                - correct_answer: The key of the correct option ('a', 'b', 'c', or 'd')."
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $json = trim(str_replace(['```json', '```'], '', $text));
                $data = json_decode($json, true);
                return is_array($data) ? $data : [];
            }
        } catch (\Exception $e) {
            Log::error('Gemini Questions Error: ' . $e->getMessage());
        }

        return [];
    }
}
