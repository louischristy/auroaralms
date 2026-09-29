<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', '');
        $this->model = config('services.openai.model', 'gpt-4o-mini');
        $this->baseUrl = config('services.openai.base_url', 'https://api.openai.com/v1');
    }

    /**
     * Send a chat completion request to the AI provider.
     */
    public function chat(string $systemPrompt, string $userPrompt, float $temperature = 0.7, int $maxTokens = 4096): ?string
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('AI API key is not configured. Set OPENAI_API_KEY in your .env file.');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(120)->post($this->baseUrl . '/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error('AI API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('AI API returned error: ' . $response->status());
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('AI API connection error', ['message' => $e->getMessage()]);
            throw new \RuntimeException('Could not connect to AI service. Please check your API configuration.');
        }
    }

    /**
     * Generate course content for a cybersecurity topic.
     */
    public function generateCourseContent(string $topic, string $difficulty, string $targetAudience, int $lessonCount = 4): array
    {
        $systemPrompt = <<<PROMPT
You are an expert cybersecurity training content creator. Generate structured course content for a Learning Management System.

Return valid JSON only (no markdown fences). The JSON must have this structure:
{
    "title": "Course title",
    "description": "2-3 sentence course description",
    "objectives": ["objective1", "objective2", "objective3", "objective4"],
    "category": "One of: Data Protection & Privacy, Social Engineering, Network Security, Incident Response, Compliance & Governance, Cloud Security, Application Security, Physical Security, Identity & Access Management, Emerging Threats",
    "duration_minutes": estimated_total_minutes,
    "lessons": [
        {
            "title": "Lesson title",
            "content": "Full HTML lesson content with <h2>, <h3>, <p>, <ul>, <li>, <strong>, <em> tags. Include real-world examples, best practices, and actionable advice. At least 400 words per lesson.",
            "duration_minutes": estimated_minutes
        }
    ]
}
PROMPT;

        $userPrompt = "Create a {$difficulty} difficulty cybersecurity training course about \"{$topic}\" for {$targetAudience}. Generate exactly {$lessonCount} lessons. Make content practical, engaging, and relevant to workplace security.";

        $result = $this->chat($systemPrompt, $userPrompt, 0.7, 8192);

        return $this->parseJson($result);
    }

    /**
     * Generate quiz questions for a course.
     */
    public function generateQuizQuestions(string $courseTitle, string $courseDescription, array $lessonTitles, int $questionCount = 10): array
    {
        $lessonsText = implode(', ', $lessonTitles);

        $systemPrompt = <<<PROMPT
You are a cybersecurity training assessment specialist. Generate quiz questions that test understanding, not just memorization.

Return valid JSON only (no markdown fences). The JSON must have this structure:
{
    "title": "Quiz title",
    "instructions": "Brief quiz instructions",
    "questions": [
        {
            "question": "The question text",
            "question_type": "multiple_choice",
            "explanation": "Explanation of the correct answer and why other options are wrong",
            "points": 10,
            "answers": [
                {"answer_text": "Option A", "is_correct": false},
                {"answer_text": "Option B", "is_correct": true},
                {"answer_text": "Option C", "is_correct": false},
                {"answer_text": "Option D", "is_correct": false}
            ]
        }
    ]
}

Rules:
- Each question must have exactly 4 answer options
- Exactly 1 answer must be correct per question
- Include scenario-based questions when possible
- Questions should range from recall to application level
- Explanations should be educational
PROMPT;

        $userPrompt = "Generate {$questionCount} quiz questions for the course \"{$courseTitle}\". Course description: {$courseDescription}. Lessons covered: {$lessonsText}. Mix difficulty levels and question styles.";

        $result = $this->chat($systemPrompt, $userPrompt, 0.6, 8192);

        return $this->parseJson($result);
    }

    /**
     * Generate phishing email templates.
     */
    public function generatePhishingTemplates(string $scenarioType, string $difficulty, string $industry = 'general', int $count = 3): array
    {
        $systemPrompt = <<<PROMPT
You are a cybersecurity red team specialist creating phishing simulation templates for security awareness training. These are for EDUCATIONAL purposes only — used in controlled phishing simulations to train employees.

Return valid JSON only (no markdown fences). The JSON must have this structure:
{
    "templates": [
        {
            "name": "Short template name",
            "scenario_type": "{$scenarioType}",
            "difficulty": "{$difficulty}",
            "subject": "Email subject line",
            "sender_name": "Plausible sender name",
            "sender_email": "plausible-sender@example-domain.com",
            "body_html": "Full HTML email body with realistic formatting. Use <p>, <a>, <strong>, <table> tags. Include a convincing call-to-action link (use #PHISHING_LINK# as placeholder). Make it look like a real email.",
            "red_flags": ["red flag 1", "red flag 2", "red flag 3"],
            "landing_page_html": "Simple HTML landing page that appears when the link is clicked. Include a form with #PHISHING_FORM# placeholder. Use realistic branding."
        }
    ]
}

Scenario types: credential_harvest, malware_download, data_exfiltration, business_email_compromise, invoice_fraud, tech_support_scam
Difficulty: easy (obvious red flags), medium (subtle red flags), hard (very convincing, few red flags)

Include realistic red flags employees should learn to spot.
PROMPT;

        $userPrompt = "Generate {$count} {$difficulty} difficulty phishing email templates of type \"{$scenarioType}\" targeting the {$industry} industry. Make them realistic but include identifiable red flags for training purposes.";

        $result = $this->chat($systemPrompt, $userPrompt, 0.8, 8192);

        return $this->parseJson($result);
    }

    /**
     * Calculate a user's security risk score based on their activity data.
     */
    public function calculateRiskScore(array $userData): array
    {
        // This is deterministic — no AI API call needed.
        // Score 0-100 where 100 = highest risk.

        $score = 50; // baseline
        $factors = [];

        // Course completion rate (lower completion = higher risk)
        $completionRate = $userData['course_completion_rate'] ?? 0;
        if ($completionRate >= 90) {
            $score -= 15;
            $factors[] = ['factor' => 'High course completion', 'impact' => -15, 'detail' => "{$completionRate}% completion rate"];
        } elseif ($completionRate >= 70) {
            $score -= 5;
            $factors[] = ['factor' => 'Good course completion', 'impact' => -5, 'detail' => "{$completionRate}% completion rate"];
        } elseif ($completionRate < 50) {
            $score += 15;
            $factors[] = ['factor' => 'Low course completion', 'impact' => 15, 'detail' => "{$completionRate}% completion rate"];
        }

        // Quiz performance
        $avgQuizScore = $userData['avg_quiz_score'] ?? 0;
        if ($avgQuizScore >= 85) {
            $score -= 10;
            $factors[] = ['factor' => 'Strong quiz performance', 'impact' => -10, 'detail' => "{$avgQuizScore}% average"];
        } elseif ($avgQuizScore < 60) {
            $score += 15;
            $factors[] = ['factor' => 'Weak quiz performance', 'impact' => 15, 'detail' => "{$avgQuizScore}% average"];
        }

        // Phishing simulation results
        $phishingClicks = $userData['phishing_clicks'] ?? 0;
        $phishingTotal = $userData['phishing_total'] ?? 0;
        if ($phishingTotal > 0) {
            $clickRate = ($phishingClicks / $phishingTotal) * 100;
            if ($clickRate > 50) {
                $score += 20;
                $factors[] = ['factor' => 'High phishing susceptibility', 'impact' => 20, 'detail' => round($clickRate) . '% click rate'];
            } elseif ($clickRate > 25) {
                $score += 10;
                $factors[] = ['factor' => 'Moderate phishing susceptibility', 'impact' => 10, 'detail' => round($clickRate) . '% click rate'];
            } elseif ($clickRate == 0) {
                $score -= 10;
                $factors[] = ['factor' => 'Zero phishing clicks', 'impact' => -10, 'detail' => '0% click rate'];
            }
        }

        // Policy acknowledgment
        $policyRate = $userData['policy_ack_rate'] ?? 0;
        if ($policyRate >= 100) {
            $score -= 5;
            $factors[] = ['factor' => 'All policies acknowledged', 'impact' => -5, 'detail' => '100% acknowledged'];
        } elseif ($policyRate < 50) {
            $score += 10;
            $factors[] = ['factor' => 'Low policy compliance', 'impact' => 10, 'detail' => "{$policyRate}% acknowledged"];
        }

        // Overdue courses
        $overdueCourses = $userData['overdue_courses'] ?? 0;
        if ($overdueCourses > 3) {
            $score += 15;
            $factors[] = ['factor' => 'Multiple overdue courses', 'impact' => 15, 'detail' => "{$overdueCourses} overdue"];
        } elseif ($overdueCourses > 0) {
            $score += 5;
            $factors[] = ['factor' => 'Overdue courses', 'impact' => 5, 'detail' => "{$overdueCourses} overdue"];
        }

        // Phishing reported
        $phishingReported = $userData['phishing_reported'] ?? 0;
        if ($phishingReported > 0 && $phishingTotal > 0) {
            $reportRate = ($phishingReported / $phishingTotal) * 100;
            if ($reportRate > 50) {
                $score -= 10;
                $factors[] = ['factor' => 'Active phishing reporter', 'impact' => -10, 'detail' => round($reportRate) . '% report rate'];
            }
        }

        // Clamp score
        $score = max(0, min(100, $score));

        // Risk level
        $level = match (true) {
            $score >= 75 => 'critical',
            $score >= 55 => 'high',
            $score >= 35 => 'medium',
            default => 'low',
        };

        return [
            'score' => $score,
            'level' => $level,
            'factors' => $factors,
            'recommendations' => $this->getRiskRecommendations($level, $factors),
        ];
    }

    /**
     * Get risk-based recommendations.
     */
    private function getRiskRecommendations(string $level, array $factors): array
    {
        $recommendations = [];

        foreach ($factors as $factor) {
            if ($factor['impact'] > 0) {
                $recommendations[] = match (true) {
                    str_contains($factor['factor'], 'course completion') => 'Assign mandatory training and set completion deadlines',
                    str_contains($factor['factor'], 'quiz') => 'Provide additional study materials and allow quiz retakes',
                    str_contains($factor['factor'], 'phishing') => 'Enroll in targeted phishing awareness training',
                    str_contains($factor['factor'], 'policy') => 'Send policy acknowledgment reminders',
                    str_contains($factor['factor'], 'overdue') => 'Escalate overdue training to their manager',
                    default => 'Review training engagement',
                };
            }
        }

        if ($level === 'critical') {
            $recommendations[] = 'Schedule a 1:1 security awareness meeting with their manager';
        }

        return array_unique($recommendations);
    }

    /**
     * Parse JSON from AI response, handling markdown code fences.
     */
    private function parseJson(string $raw): array
    {
        // Strip markdown code fences if present
        $raw = trim($raw);
        if (str_starts_with($raw, '```')) {
            $raw = preg_replace('/^```(?:json)?\s*/', '', $raw);
            $raw = preg_replace('/\s*```$/', '', $raw);
        }

        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('AI response JSON parse error', [
                'error' => json_last_error_msg(),
                'raw' => substr($raw, 0, 500),
            ]);
            throw new \RuntimeException('Failed to parse AI response. Please try again.');
        }

        return $data;
    }
}
