<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $badges = [
            ['name' => 'First Steps',       'slug' => 'first-steps',       'icon' => '🎯', 'criteria_type' => 'first_completion',   'criteria_value' => 1,  'description' => 'Complete your first course', 'is_active' => true],
            ['name' => 'Quick Learner',      'slug' => 'quick-learner',     'icon' => '⚡', 'criteria_type' => 'courses_completed',  'criteria_value' => 3,  'description' => 'Complete 3 courses', 'is_active' => true],
            ['name' => 'Knowledge Seeker',   'slug' => 'knowledge-seeker',  'icon' => '📚', 'criteria_type' => 'courses_completed',  'criteria_value' => 5,  'description' => 'Complete 5 courses', 'is_active' => true],
            ['name' => 'Security Champion',  'slug' => 'security-champion', 'icon' => '🏆', 'criteria_type' => 'courses_completed',  'criteria_value' => 10, 'description' => 'Complete 10 courses', 'is_active' => true],
            ['name' => 'Quiz Master',        'slug' => 'quiz-master',       'icon' => '🧠', 'criteria_type' => 'quizzes_passed',     'criteria_value' => 5,  'description' => 'Pass 5 quizzes', 'is_active' => true],
            ['name' => 'Perfect Score',      'slug' => 'perfect-score',     'icon' => '💯', 'criteria_type' => 'perfect_scores',     'criteria_value' => 1,  'description' => 'Get 100% on a quiz', 'is_active' => true],
            ['name' => 'Hat Trick',          'slug' => 'hat-trick',         'icon' => '🎩', 'criteria_type' => 'perfect_scores',     'criteria_value' => 3,  'description' => 'Get 100% on 3 quizzes', 'is_active' => true],
            ['name' => 'Phishing Spotter',   'slug' => 'phishing-spotter',  'icon' => '🎣', 'criteria_type' => 'phishing_reporter',  'criteria_value' => 1,  'description' => 'Report a phishing simulation', 'is_active' => true],
            ['name' => 'Cyber Guardian',     'slug' => 'cyber-guardian',    'icon' => '🛡️', 'criteria_type' => 'courses_completed',  'criteria_value' => 15, 'description' => 'Complete 15 courses', 'is_active' => true],
            ['name' => 'Security Master',    'slug' => 'security-master',   'icon' => '👑', 'criteria_type' => 'courses_completed',  'criteria_value' => 27, 'description' => 'Complete all courses', 'is_active' => true],
        ];

        $now = now();

        foreach ($badges as $badge) {
            DB::table('badges')->updateOrInsert(
                ['slug' => $badge['slug']],
                array_merge($badge, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }

    public function down(): void
    {
        DB::table('badges')->whereIn('slug', [
            'first-steps', 'quick-learner', 'knowledge-seeker', 'security-champion',
            'quiz-master', 'perfect-score', 'hat-trick', 'phishing-spotter',
            'cyber-guardian', 'security-master',
        ])->delete();
    }
};
