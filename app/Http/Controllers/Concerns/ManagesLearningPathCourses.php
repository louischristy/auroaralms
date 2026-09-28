<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Course;
use App\Models\LearningPath;
use Illuminate\Http\Request;

trait ManagesLearningPathCourses
{
    protected function pathRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'is_sequential' => 'nullable|boolean',
            'is_mandatory' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'integer|distinct|exists:courses,id',
        ];
    }

    protected function pathAttributes(Request $request, array $validated): array
    {
        return [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'difficulty' => $validated['difficulty'],
            'is_sequential' => $request->boolean('is_sequential'),
            'is_mandatory' => $request->boolean('is_mandatory'),
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    /**
     * Sync courses in submitted order and refresh estimated duration.
     * $allowedIds restricts which courses may be attached.
     */
    protected function syncPathCourses(LearningPath $path, array $courseIds, $allowedIds): void
    {
        $courseIds = array_values(array_unique(array_map('intval', $courseIds)));
        $allowed = collect($allowedIds)->map(fn($i) => (int) $i)->all();
        $courseIds = array_values(array_filter($courseIds, fn($id) => in_array($id, $allowed, true)));

        $sync = [];
        foreach ($courseIds as $i => $id) {
            $sync[$id] = ['sort_order' => $i + 1, 'is_required' => true];
        }
        $path->courses()->sync($sync);

        $path->update([
            'estimated_duration_minutes' => (int) Course::whereIn('id', $courseIds)->sum('duration_minutes'),
        ]);
    }

    protected function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = \Illuminate\Support\Str::slug($title) ?: 'learning-path';
        $slug = $base;
        $n = 2;
        while (LearningPath::withoutTenantScope()->withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $n++;
        }
        return $slug;
    }

    /** Ordered course list for the form: [{id,title,duration,category}] */
    protected function selectedCoursePayload(?LearningPath $path)
    {
        if (!$path) {
            return collect();
        }
        return $path->courses->map(fn($c) => ['id' => $c->id, 'title' => $c->title]);
    }
}
