<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['name' => 'First Steps',       'slug' => 'first-steps',       'icon' => '🎯', 'criteria_type' => 'first_completion',   'criteria_value' => 1,  'description' => 'Complete your first course'],
            ['name' => 'Quick Learner',      'slug' => 'quick-learner',     'icon' => '⚡', 'criteria_type' => 'courses_completed',  'criteria_value' => 3,  'description' => 'Complete 3 courses'],
            ['name' => 'Knowledge Seeker',   'slug' => 'knowledge-seeker',  'icon' => '📚', 'criteria_type' => 'courses_completed',  'criteria_value' => 5,  'description' => 'Complete 5 courses'],
            ['name' => 'Security Champion',  'slug' => 'security-champion', 'icon' => '🏆', 'criteria_type' => 'courses_completed',  'criteria_value' => 10, 'description' => 'Complete 10 courses'],
            ['name' => 'Quiz Master',        'slug' => 'quiz-master',       'icon' => '🧠', 'criteria_type' => 'quizzes_passed',     'criteria_value' => 5,  'description' => 'Pass 5 quizzes'],
            ['name' => 'Perfect Score',      'slug' => 'perfect-score',     'icon' => '💯', 'criteria_type' => 'perfect_scores',     'criteria_value' => 1,  'description' => 'Get 100% on a quiz'],
            ['name' => 'Hat Trick',          'slug' => 'hat-trick',         'icon' => '🎩', 'criteria_type' => 'perfect_scores',     'criteria_value' => 3,  'description' => 'Get 100% on 3 quizzes'],
            ['name' => 'Phishing Spotter',   'slug' => 'phishing-spotter',  'icon' => '🎣', 'criteria_type' => 'phishing_reporter',  'criteria_value' => 1,  'description' => 'Report a phishing simulation'],
            ['name' => 'Cyber Guardian',     'slug' => 'cyber-guardian',    'icon' => '🛡️', 'criteria_type' => 'courses_completed',  'criteria_value' => 15, 'description' => 'Complete 15 courses'],
            ['name' => 'Security Master',    'slug' => 'security-master',   'icon' => '👑', 'criteria_type' => 'courses_completed',  'criteria_value' => 27, 'description' => 'Complete all courses'],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['slug' => $badge['slug']],
                $badge
            );
        }
    }
}
