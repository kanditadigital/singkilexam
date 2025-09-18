<?php

namespace App\Http\Controllers\Std;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        return view('std.confirmation',[
            'title' => 'Exam Confirmation'
        ]);
    }
}
