<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExamBrowserController extends Controller
{
    public function index()
    {
        return view('site.exambrowser',[
            'title'     => 'Unduh ExamBrowser '
        ]);
    }
}
