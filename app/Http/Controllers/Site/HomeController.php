<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\School;
use App\Models\Student;

class HomeController extends Controller
{
    public function index()
    {
        // Data for charts
        $studentCount = Student::count();
        $teacherCount = Employee::where('employee_type', 'Guru')->count();
        $cabdinCount = Branch::count();
        $schoolCount = School::count();

        // Hierarchical data for data table
        $branches = Branch::with(['schools' => function($query) {
            $query->with(['employees' => function($q) {
                $q->where('employee_type', 'Guru');
            }, 'students']);
        }])->get()->map(function($branch) {
            $branch->school_count = $branch->schools->count();
            $branch->teacher_count = $branch->schools->sum(function($school) {
                return $school->employees->count();
            });
            $branch->student_count = $branch->schools->sum(function($school) {
                return $school->students->count();
            });
            return $branch;
        });

        return view('site.home', [
            'title' => 'Computer Assisted Test',
            'studentCount' => $studentCount,
            'teacherCount' => $teacherCount,
            'cabdinCount' => $cabdinCount,
            'schoolCount' => $schoolCount,
            'branches' => $branches,
        ]);
    }
}
