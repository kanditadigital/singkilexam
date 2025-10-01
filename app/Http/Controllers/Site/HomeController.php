<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Exam;
use App\Models\SiteSetting;

class HomeController extends Controller
{
    public function index()
    {
        $publicLiveScoreEnabled = SiteSetting::getBool('live_score_public_enabled');

        $exams = $publicLiveScoreEnabled
            ? Exam::orderBy('exam_name')->get(['id', 'exam_name', 'exam_code'])
            : collect();

        $branches = $publicLiveScoreEnabled
            ? Branch::orderBy('branch_name')->get(['id', 'branch_name'])
            : collect();

        return view('site.home', [
            'title' => 'Computer Assisted Test',
            'publicLiveScoreEnabled' => $publicLiveScoreEnabled,
            'publicLiveScoreExams' => $exams,
            'publicLiveScoreBranches' => $branches,
        ]);
    }
}
