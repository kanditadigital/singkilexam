<?php

use Mews\Captcha\CaptchaController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Std\ExamController;

// --- SITE ---
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\ExamBrowserController;

// --- DISDIK ---
use App\Http\Controllers\Office\SesiController;
use App\Http\Controllers\Office\SoalController;
use App\Http\Controllers\Office\MapelController;
use App\Http\Controllers\Office\SiswaController;
use App\Http\Controllers\Office\UjianController;
use App\Http\Controllers\Cabdin\SchoolController;
use App\Http\Controllers\Office\CabdinController;
use App\Http\Controllers\Office\PegawaiController;
use App\Http\Controllers\Office\RankingController;
use App\Http\Controllers\Office\SekolahController;

// --- CABDIN ---
use App\Http\Controllers\Cabdin\CabdinProfileController;
use App\Http\Controllers\Sch\SchStudentController;
use App\Http\Controllers\Sch\SchEmployeeController;
use App\Http\Controllers\Office\LiveScoreController;
use App\Http\Controllers\Office\OfficeHomeController;
// --- SEKOLAH ---
use App\Http\Controllers\Sch\SchExamParticipantController;
use App\Http\Controllers\Sch\SchProfileController;
use App\Http\Controllers\Cabdin\SchoolController as CabdinSchoolController;
use App\Http\Controllers\Cabdin\StudentController as CabdinStudentController;

// --- SISWA ---
use App\Http\Controllers\Site\LiveScoreController as SiteLiveScoreController;
use App\Http\Controllers\Cabdin\DashboardController as CabdinDashboardController;


/*
|--------------------------------------------------------------------------
| PUBLIC / SITE
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

// Captcha
Route::get('captcha/{config?}', [CaptchaController::class, 'getCaptcha']);
Route::get('refresh-captcha', fn() => response()->json(['captcha' => captcha_src('flat')]))
    ->name('captcha.refresh');

// Live Score publik
Route::get('live-score', [SiteLiveScoreController::class, 'index'])->name('live-score');
Route::get('live-score/stream', [SiteLiveScoreController::class, 'data'])->name('public.live-score');
Route::get('live-score/stream/schools/{branch}', [SiteLiveScoreController::class, 'schoolsByBranch'])
    ->name('public.live-score.schools');

// Exam Browser
Route::get('exambrowser', [ExamBrowserController::class, 'index'])->name('exambrowser');


/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/
Route::get('login', [AuthController::class, 'formSignIn'])->name('login');
Route::post('login', [AuthController::class, 'signIn'])->name('login.attempt');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Login sekolah dan cabdin
Route::post('ayo-login', [AuthController::class, 'myAuth'])->name('ayo.login');

// Login siswa/guru via Exambrowser
Route::get('bro-login', [AuthController::class, 'formSignParticipate'])->name('bro.login');
Route::post('bro-auth', [AuthController::class, 'signParticipate'])->name('bro.auth');
Route::post('stdout', [AuthController::class, 'stdOut'])->name('std.out');


/*
|--------------------------------------------------------------------------
| GENERAL DASHBOARD
|--------------------------------------------------------------------------
*/
Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware('auth:web')
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| DISDIK (Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('disdik')->name('disdik.')->middleware('auth:web')->group(function () {
    Route::resource('cabdin', CabdinController::class);
    Route::resource('sekolah', SekolahController::class);
    Route::resource('pegawai', PegawaiController::class);
    Route::resource('siswa', SiswaController::class);
    Route::resource('exam', UjianController::class);
    Route::resource('sesi-ujian', SesiController::class);
    Route::resource('mapel', MapelController::class);
    Route::resource('soal', SoalController::class);

    // Dashboard
    Route::get('/home', [OfficeHomeController::class, 'index'])->name('office.home');

    // Live Score
    Route::get('live-score', [LiveScoreController::class, 'index'])->name('live-score.index');
    Route::get('live-score/data', [LiveScoreController::class, 'data'])->name('live-score.data');
    Route::post('live-score/toggle-public', [LiveScoreController::class, 'togglePublic'])->name('live-score.toggle');

    // Ranking
    Route::get('ranking', [RankingController::class, 'index'])->name('ranking.index');
    Route::get('ranking/data', [RankingController::class, 'data'])->name('ranking.data');
    Route::get('ranking/download', [RankingController::class, 'downloadPdf'])->name('ranking.download');

    // Get school/siswa by branch
    Route::get('schools/by-branch/{branchId}', [PegawaiController::class, 'getByBranch'])->name('pegawai.getByBranch');
    Route::get('siswa/by-branch/{branchId}', [SiswaController::class, 'getByBranch'])->name('siswa.getByBranch');
});


/*
|--------------------------------------------------------------------------
| CABDIN
|--------------------------------------------------------------------------
*/
Route::prefix('cabdin')->name('cabdin.')->middleware('auth:branches')->group(function () {
    Route::get('dashboard', [CabdinDashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('profile', [CabdinProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [CabdinProfileController::class, 'update'])->name('profile.update');

    // Exam Participants
    Route::get('exam-participants', [App\Http\Controllers\Cabdin\DashboardController::class, 'examParticipants'])->name('exam-participants.index');

    Route::resource('schools', CabdinSchoolController::class)->except(['show', 'destroy']);
    Route::post('schools/{school}/toggle-status', [CabdinSchoolController::class, 'toggleStatus'])->name('schools.toggle');
    Route::post('schools/{school}/reset-password', [CabdinSchoolController::class, 'resetPassword'])->name('schools.reset');

    Route::resource('students', CabdinStudentController::class)->except(['show']);
    Route::post('students/{student}/reset-password', [CabdinStudentController::class, 'resetPassword'])
        ->name('students.reset-password');
});


/*
|--------------------------------------------------------------------------
| SEKOLAH
|--------------------------------------------------------------------------
*/
Route::prefix('sch')->name('sch.')->middleware('auth:schools')->group(function () {
    Route::resource('student', SchStudentController::class);
    Route::resource('employee', SchEmployeeController::class);

    // Import routes
    Route::post('employee/import', [SchEmployeeController::class, 'import'])->name('employee.import');
    Route::post('student/import', [SchStudentController::class, 'import'])->name('student.import');

    // Reset password routes
    Route::post('student/{student}/reset-password', [SchStudentController::class, 'resetPassword'])->name('student.reset-password');
    Route::post('employee/{employee}/reset-password', [SchEmployeeController::class, 'resetPassword'])->name('employee.reset-password');

    Route::get('dashboard', [App\Http\Controllers\Sch\DashboardController::class, 'index'])->name('sch.dashboard');
    // Profile
    Route::get('profile', [SchProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [SchProfileController::class, 'update'])->name('profile.update');
    // Exam Participants
    Route::get('exam-participants', [SchExamParticipantController::class, 'index'])->name('exam-participants.index');
    Route::get('exam-participants/students', [SchExamParticipantController::class, 'students'])->name('exam-participants.students');
    Route::get('exam-participants/registered', [SchExamParticipantController::class, 'registered'])->name('exam-participants.registered');
    Route::post('exam-participants', [SchExamParticipantController::class, 'store'])->name('exam-participants.store');
    Route::delete('exam-participants/{participant}', [SchExamParticipantController::class, 'destroy'])->name('exam-participants.destroy');
    Route::delete('exam-participants', [SchExamParticipantController::class, 'bulkDestroy'])->name('exam-participants.bulk-destroy');
    Route::get('exam-participants/print-cards/{exam}', [SchExamParticipantController::class, 'printCards'])->name('exam-participants.print-cards');

    // Participant Cards Management
    Route::get('participant-cards', [SchExamParticipantController::class, 'participantCards'])->name('participant-cards.index');
    Route::get('participant-cards/preview', [SchExamParticipantController::class, 'previewCards'])->name('participant-cards.preview');
    Route::get('participant-cards/download', [SchExamParticipantController::class, 'downloadCards'])->name('participant-cards.download');

    // Exam Monitoring
    Route::get('exam-monitoring', [SchExamParticipantController::class, 'examMonitoring'])->name('exam-monitoring.index');
});


/*
|--------------------------------------------------------------------------
| SISWA / PESERTA UJIAN
|--------------------------------------------------------------------------
*/
Route::prefix('std')->name('std.')->middleware('auth.participant')->group(function () {
    Route::get('confirmation', [ExamController::class, 'index'])->name('confirmation');
    Route::post('checktoken', [ExamController::class, 'checkToken'])->name('checktoken');
    Route::delete('reset-attempt/{attemptId}', [ExamController::class, 'resetAttempt'])->name('reset_attempt');

    Route::get('exam/{token}', [ExamController::class, 'showExam'])->name('exam');
    Route::post('exam/{token}/answer', [ExamController::class, 'saveAnswer'])->name('answer');
    Route::post('exam/{token}/finish', [ExamController::class, 'finish'])->name('finish');
    Route::get('exam/{token}/finished', [ExamController::class, 'finished'])->name('finished');

    Route::get('exam/{token}/question', [ExamController::class, 'getQuestion'])->name('question.fetch');
    Route::get('exam/{token}/statuses', [ExamController::class, 'getStatuses'])->name('question.statuses');
});
