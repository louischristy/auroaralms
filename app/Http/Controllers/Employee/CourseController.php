<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        // TODO: Load assigned courses with progress
        return view('employee.courses.index', ['courses' => collect()]);
    }

    public function show(int $course)
    {
        // TODO: Load course with modules
        return view('employee.courses.show', ['course' => null]);
    }

    public function completeModule(Request $request, int $course)
    {
        // TODO: Record module completion
        return back()->with('success', 'Module completed.');
    }
}
