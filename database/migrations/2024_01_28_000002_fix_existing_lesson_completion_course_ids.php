<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix any lesson_completions where course_id doesn't match the lesson's actual course
        DB::statement('
            UPDATE lesson_completions lc
            JOIN lessons l ON lc.lesson_id = l.id
            SET lc.course_id = l.course_id
            WHERE lc.course_id != l.course_id OR lc.course_id IS NULL
        ');

        // Recalculate progress for all active enrollments
        $enrollments = DB::table('course_enrollments')
            ->whereIn('status', ['not_started', 'in_progress'])
            ->get();

        foreach ($enrollments as $enrollment) {
            $totalLessons = DB::table('lessons')
                ->where('course_id', $enrollment->course_id)
                ->where('is_active', true)
                ->count();

            $completedLessons = DB::table('lesson_completions')
                ->where('user_id', $enrollment->user_id)
                ->where('course_id', $enrollment->course_id)
                ->count();

            $hasQuiz = DB::table('quizzes')
                ->where('course_id', $enrollment->course_id)
                ->exists();

            $quizPassed = DB::table('quiz_attempts')
                ->where('user_id', $enrollment->user_id)
                ->where('course_id', $enrollment->course_id)
                ->where('passed', true)
                ->exists();

            if ($totalLessons === 0 && !$hasQuiz) {
                $progress = 0;
            } elseif ($hasQuiz) {
                $lessonPart = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 80 : 80;
                $progress = (int) round($lessonPart + ($quizPassed ? 20 : 0));
            } else {
                $progress = $totalLessons > 0
                    ? (int) round(($completedLessons / $totalLessons) * 100)
                    : 0;
            }

            $status = $enrollment->status;
            $updates = ['progress_percent' => $progress];

            if ($progress >= 100) {
                $updates['status'] = 'completed';
                $updates['completed_at'] = $enrollment->completed_at ?? now();
            } elseif ($progress > 0 && $status === 'not_started') {
                $updates['status'] = 'in_progress';
                $updates['started_at'] = $enrollment->started_at ?? now();
            }

            DB::table('course_enrollments')
                ->where('id', $enrollment->id)
                ->update($updates);
        }
    }

    public function down(): void
    {
        // Data fix — no rollback needed
    }
};
