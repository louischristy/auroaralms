<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Find courses with duplicate sort_order values and re-number them
        $courses = DB::table('lessons')
            ->select('course_id')
            ->groupBy('course_id')
            ->havingRaw('COUNT(*) != COUNT(DISTINCT sort_order)')
            ->pluck('course_id');

        foreach ($courses as $courseId) {
            $lessons = DB::table('lessons')
                ->where('course_id', $courseId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('id');

            foreach ($lessons as $index => $lessonId) {
                DB::table('lessons')
                    ->where('id', $lessonId)
                    ->update(['sort_order' => $index]);
            }
        }
    }

    public function down(): void
    {
        // No rollback needed
    }
};
