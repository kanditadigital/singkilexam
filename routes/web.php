<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Office\CabdinController;
use App\Http\Controllers\Office\UjianController;
use App\Http\Controllers\Office\SekolahController;
use App\Http\Controllers\Office\PegawaiController;
use App\Http\Controllers\Office\SiswaController;
use App\Http\Controllers\Office\SesiController;
use App\Http\Controllers\Office\MapelController;
use App\Http\Controllers\Office\LiveScoreController;
use App\Http\Controllers\Office\SoalController;
use App\Http\Controllers\Office\RankingController;
use App\Http\Controllers\Sch\SchEmployeeController;
use App\Http\Controllers\Sch\SchExamParticipantController;
use App\Http\Controllers\Sch\SchStudentController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\LiveScoreController as SiteLiveScoreController;
use App\Http\Controllers\Std\ExamController;
use Illuminate\Support\Facades\Route;
use Mews\Captcha\CaptchaController;

// Site
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('captcha/{config?}', [CaptchaController::class, 'getCaptcha']);
Route::get('/refresh-captcha', function () {
    return response()->json(['captcha' => captcha_src('flat')]);
});

Route::get('/live-score/stream', [SiteLiveScoreController::class, 'data'])->name('public.live-score');
Route::get('/live-score/stream/schools/{branch}', [SiteLiveScoreController::class, 'schoolsByBranch'])->name('public.live-score.schools');

/**
 * Login Admin
 */
Route::get('/login',[AuthController::class, 'formSignIn'])->name('login');
Route::get('/signin',[AuthController::class, 'formSignIn'])->name('signin.form');
Route::post('/signin',[AuthController::class, 'signIn'])->name('signin');
Route::post('/signout',[AuthController::class, 'signOut'])->name('signout');

/**
 * Login Sekolah
 */
Route::post('/login-sch',[AuthController::class, 'schoolAuth'])->name('login.sch');

/**
 * Login Siswa dan Guru Menggunakan Exambrowser
 */
Route::get('/bro-login',[AuthController::class, 'formSignParticipate'])->name('bro.login');
Route::post('/bro-auth',[AuthController::class, 'signParticipate'])->name('bro.auth');
Route::post('/stdout',[AuthController::class, 'stdOut'])->name('std.out');

Route::get('/dashboard',[DashboardController::class, 'index'])->name('dashboard');

Route::prefix('disdik')->middleware('auth:web')->group(function(){
    Route::resource('cabdin', CabdinController::class);
    Route::resource('sekolah', SekolahController::class);
    Route::resource('pegawai', PegawaiController::class);
    Route::resource('siswa', SiswaController::class);
    Route::resource('exam', UjianController::class);
    Route::resource('sesi-ujian', SesiController::class);
    Route::resource('mapel', MapelController::class);
    Route::resource('soal', SoalController::class);
    Route::get('live-score', [LiveScoreController::class, 'index'])->name('live-score.index');
    Route::get('live-score/data', [LiveScoreController::class, 'data'])->name('live-score.data');
    Route::post('live-score/toggle-public', [LiveScoreController::class, 'togglePublic'])->name('live-score.toggle');
    Route::get('ranking', [RankingController::class, 'index'])->name('ranking.index');
    Route::get('ranking/data', [RankingController::class, 'data'])->name('ranking.data');
    Route::get('ranking/download', [RankingController::class, 'downloadPdf'])->name('ranking.download');

    // get school by branch
    Route::get('/schools/by-branch/{branchId}', [PegawaiController::class, 'getByBranch'])->name('pegawai.getByBranch');
    Route::get('/siswa/by-branch/{branchId}', [SiswaController::class, 'getByBranch'])->name('siswa.getByBranch');
});

Route::prefix('sch')->middleware('auth:schools')->group(function(){
    Route::resource('/student', SchStudentController::class);
    Route::resource('/employee', SchEmployeeController::class);
    Route::get('/exam-participants', [SchExamParticipantController::class, 'index'])->name('exam-participants.index');
    Route::get('/exam-participants/students', [SchExamParticipantController::class, 'students'])->name('exam-participants.students');
    Route::get('/exam-participants/registered', [SchExamParticipantController::class, 'registered'])->name('exam-participants.registered');
    Route::post('/exam-participants', [SchExamParticipantController::class, 'store'])->name('exam-participants.store');
    Route::delete('/exam-participants/{participant}', [SchExamParticipantController::class, 'destroy'])->name('exam-participants.destroy');
});

Route::prefix('std')->middleware(['auth.participant'])->group(function(){
    Route::get('/confirmation',[ExamController::class, 'index'])->name('std.confirmation');
    Route::post('/checktoken', [ExamController::class, 'checkToken'])->name('std.checktoken');
    Route::get('/exam/{token}', [ExamController::class, 'showExam'])->name('std.exam');
    Route::post('/exam/{token}/answer', [ExamController::class, 'saveAnswer'])->name('std.answer');
    Route::post('/exam/{token}/finish', [ExamController::class, 'finish'])->name('std.finish');
    Route::get('/exam/{token}/finished', [ExamController::class, 'finished'])->name('std.finished');
    Route::get('/exam/{token}/question', [ExamController::class, 'getQuestion'])->name('std.question.fetch');
    Route::get('/exam/{token}/statuses', [ExamController::class, 'getStatuses'])->name('std.question.statuses');
});
