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
        set_time_limit(120);
        $apiKey = env('GEMINI_API_KEY');
        
        if ($apiKey) {
            try {
                $response = Http::timeout(60)->withoutVerifying()->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
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

    public function generateCurriculumModules(Course $course, ?string $prompt = null): array
    {
        set_time_limit(120);
        $apiKey = env('GEMINI_API_KEY');
        
        $additionalPrompt = $prompt ? "\nAdditional Expert Instruction: {$prompt}" : "";

        if ($apiKey) {
            try {
                $response = Http::timeout(60)->withoutVerifying()->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => "You are a senior curriculum designer. Based on the course title '{$course->title}' and description '{$course->description}', generate 3-4 learning modules.{$additionalPrompt}
                                    For each module, generate 2-3 curriculum items.
                                    Item types can be: 'literal' (text), 'visual' (video), 'knowledge' (quiz).
                                    Return ONLY a JSON array of module objects.
                                    Module Object keys:
                                    - title: Module title
                                    - description: Brief description
                                    - items: Array of item objects
                                    
                                    Item Object keys:
                                    - title: Item title
                                    - type: 'literal', 'visual', or 'knowledge'
                                    - content: Provide a 200-word text content for 'literal', a relevant YouTube URL for 'visual'. For 'knowledge', provide a JSON string containing {'question':'...', 'options':{'A':'...', 'B':'...', 'C':'...', 'D':'...'}, 'answer':'A'}.
                                    - rubric_json: null"
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
                    if (is_array($data)) return $data;
                } else {
                    throw new \Exception('API Error: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('Gemini Curriculum Error: ' . $e->getMessage());
                throw new \Exception('Failed to generate curriculum: ' . $e->getMessage());
            }
        }

        // --- FALLBACK MOCK DATA ---
        return [
            [
                'title' => 'Fundamentals & Core Concepts',
                'description' => 'Introduction to the foundational principles of the course.',
                'items' => [
                    [
                        'title' => 'Introduction Video',
                        'type' => 'visual',
                        'content' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                        'rubric_json' => null
                    ],
                    [
                        'title' => 'Core Theory Overview',
                        'type' => 'literal',
                        'content' => 'This is a brief overview of the theoretical framework. It covers the main concepts necessary to build a solid foundation in the topic. Students will learn the key terminology and context.',
                        'rubric_json' => null
                    ],
                    [
                        'title' => 'Concept Check Quiz',
                        'type' => 'knowledge',
                        'content' => json_encode([
                            'question' => 'What is the primary focus of this foundational module?',
                            'options' => [
                                'A' => 'Advanced strategies',
                                'B' => 'Basic terminology and core principles',
                                'C' => 'Case study analysis',
                                'D' => 'Final project evaluation'
                            ],
                            'answer' => 'B'
                        ]),
                        'rubric_json' => null
                    ]
                ]
            ],
            [
                'title' => 'Practical Application',
                'description' => 'Applying the theory into real-world scenarios.',
                'items' => [
                    [
                        'title' => 'Case Study Reading',
                        'type' => 'literal',
                        'content' => 'Read this case study about how the concepts are applied in a modern industry setting. Notice the challenges faced and the solutions implemented based on the core theory.',
                        'rubric_json' => null
                    ],
                    [
                        'title' => 'Application Quiz',
                        'type' => 'knowledge',
                        'content' => json_encode([
                            'question' => 'In the practical scenario, which approach was most effective?',
                            'options' => [
                                'A' => 'Ignoring constraints',
                                'B' => 'Applying the core theory systematically',
                                'C' => 'Guessing without data',
                                'D' => 'Skipping the planning phase'
                            ],
                            'answer' => 'B'
                        ]),
                        'rubric_json' => null
                    ],
                    [
                        'title' => 'Hands-on Exercise',
                        'type' => 'exercise',
                        'content' => 'Complete the following practical mission based on the lessons learned.',
                        'rubric_json' => [
                            ['label' => 'Accuracy', 'points' => 10],
                            ['label' => 'Creativity', 'points' => 5]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function generateCourseQuestions(Course $course): array
    {
        set_time_limit(120);
        $apiKey = env('GEMINI_API_KEY');

        if ($apiKey) {
            try {
                $response = Http::timeout(60)->withoutVerifying()->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
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
                    if (is_array($data)) return $data;
                }
            } catch (\Exception $e) {
                Log::error('Gemini Questions Error: ' . $e->getMessage());
            }
        }

        // --- FALLBACK MOCK DATA ---
        return [
            [
                'question_text' => 'What is the primary objective of this course?',
                'type' => 'pretest',
                'options' => [
                    ['key' => 'a', 'text' => 'Learn basic concepts'],
                    ['key' => 'b', 'text' => 'Master advanced framework architecture'],
                    ['key' => 'c', 'text' => 'Pass a test'],
                    ['key' => 'd', 'text' => 'None of the above']
                ],
                'correct_answer' => 'b'
            ],
            [
                'question_text' => 'Which of the following best describes the course outcome?',
                'type' => 'posttest',
                'options' => [
                    ['key' => 'a', 'text' => 'Theoretical knowledge only'],
                    ['key' => 'b', 'text' => 'Practical mastery of framework architecture'],
                    ['key' => 'c', 'text' => 'Basic reading comprehension'],
                    ['key' => 'd', 'text' => 'Undefined']
                ],
                'correct_answer' => 'b'
            ]
        ];
    }

    public function generateQuizBankTemplate(string $prompt): array
    {
        set_time_limit(120);
        $apiKey = env('GEMINI_API_KEY');

        if ($apiKey) {
            try {
                $response = Http::timeout(60)->withoutVerifying()->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => "You are a senior assessment specialist. Based on this request: '{$prompt}', generate a comprehensive quiz template.
                                    Return ONLY a JSON object with:
                                    - title: Professional title
                                    - sub_type: 'pre_test', 'quiz', or 'final_exam'
                                    - assessment_mode: 'diagnostic' or 'mastery'
                                    - passing_grade: Integer (70-90)
                                    - content: Array of 5-8 question objects, each with:
                                        - question_text: The question
                                        - options: Array of 4 objects {key: 'a'|'b'|'c'|'d', text: 'Value'}
                                        - correct_answer: 'a', 'b', 'c', or 'd'"
                                ]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $json = trim(str_replace(['```json', '```'], '', $text));
                    return json_decode($json, true);
                }
            } catch (\Exception $e) {
                Log::error('Gemini Quiz Template Error: ' . $e->getMessage());
            }
        }

        return [
            'title' => 'AI Generated Assessment: ' . $prompt,
            'sub_type' => 'quiz',
            'assessment_mode' => 'diagnostic',
            'passing_grade' => 75,
            'content' => [
                [
                    'question_text' => 'What is the primary topic of ' . $prompt . '?',
                    'options' => [
                        ['key' => 'a', 'text' => 'Option A'],
                        ['key' => 'b', 'text' => 'Option B'],
                        ['key' => 'c', 'text' => 'Option C'],
                        ['key' => 'd', 'text' => 'Option D']
                    ],
                    'correct_answer' => 'a'
                ]
            ]
        ];
    }
}
