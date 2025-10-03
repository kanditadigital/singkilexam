<?php

namespace App\Http\Controllers\Cabdin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $branch = Auth::guard('branches')->user();

        $schoolsCount = $branch->schools()->count();
        $activeSchools = $branch->schools()->where('is_active', true)->count();
        $studentsCount = $branch->students()->count();

        return view('cabdin.dashboard', [
            'title' => 'Dashboard Cabdin',
            'branch' => $branch,
            'schoolsCount' => $schoolsCount,
            'activeSchools' => $activeSchools,
            'studentsCount' => $studentsCount,
        ]);
    }
}
