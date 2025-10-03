<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class OfficeHomeController extends Controller
{
    public function index()
    {
        $examcount      = Exam::all()->count();
        $subjectcount   = Subject::all()->count();
        $examsession    = ExamSession::all()->count();
        $branchcount    = Branch::all()->count();
        $schcount       = School::all()->count();
        $employeecount       = Employee::all()->count();
        $stdcount       = Student::all()->count();
        return view('office.dashboard',[
            'title'     => 'Dashboard',
            'examcount' => $examcount,
            'subjectcount'  => $subjectcount,
            'examsession'   => $examsession,
            'branchcount'   => $branchcount,
            'schcount'      => $schcount,
            'employeecount' => $employeecount,
            'stdcount'      => $stdcount
        ]);
    }
}
