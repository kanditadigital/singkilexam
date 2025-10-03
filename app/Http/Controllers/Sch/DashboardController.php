<?php

namespace App\Http\Controllers\Sch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('school.dashboard',[
            'title'     => 'Dashboard'
        ]);
    }
}
