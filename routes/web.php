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
use App\Http\Controllers\Office\SoalController;
use Illuminate\Support\Facades\Route;

Route::get('/',[AuthController::class, 'formSignIn'])->name('home');
Route::get('/signin',[AuthController::class, 'formSignIn'])->name('signin');
Route::post('/signin',[AuthController::class, 'signIn'])->name('signin');
Route::post('/signout',[AuthController::class, 'signOut'])->name('signout');

Route::prefix('disdik')->middleware('auth')->group(function(){
    Route::get('/dashboard',[DashboardController::class, 'index'])->name('dashboard');
    Route::resource('cabdin', CabdinController::class);
    Route::resource('sekolah', SekolahController::class);
    Route::resource('pegawai', PegawaiController::class);
    Route::resource('siswa', SiswaController::class);
    Route::resource('exam', UjianController::class);
    Route::resource('sesi-ujian', SesiController::class);
    Route::resource('mapel', MapelController::class);
    Route::resource('soal', SoalController::class);

    // get school by branch
    Route::get('/schools/by-branch/{branchId}', [PegawaiController::class, 'getByBranch'])->name('pegawai.getByBranch');
    Route::get('/siswa/by-branch/{branchId}', [SiswaController::class, 'getByBranch'])->name('siswa.getByBranch');
});