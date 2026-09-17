<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\CourseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseCategoryController extends Controller
{
    public function index()
    {
        $categories = CourseCategory::ordered()
            ->withCount('courses')
            ->get();

        return view('platform.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:course_categories,name',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['sort_order'] = CourseCategory::max('sort_order') + 1;

        CourseCategory::create($validated);

        return back()->with('success', 'Category added.');
    }

    public function update(Request $request, CourseCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:course_categories,name,' . $category->id,
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // If name changed, update courses that use the old name
        if ($category->name !== $validated['name']) {
            \App\Models\Course::where('category', $category->name)
                ->update(['category' => $validated['name']]);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $category->update($validated);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(CourseCategory $category)
    {
        if ($category->courses()->count() > 0) {
            return back()->with('error', 'Cannot delete a category that has courses. Reassign or delete the courses first.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:course_categories,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            CourseCategory::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
