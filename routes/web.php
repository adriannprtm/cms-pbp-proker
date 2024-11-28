<?php

use Illuminate\Support\Facades\Route;
use Kreait\Firebase\Contract\Firestore;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OnboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PengelolaController;
use App\Http\Controllers\CategoryeventController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'index']);
Route::post('/login_proses', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::group(['middleware' => 'firebase.auth'], function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    //banner
    Route::get('/banner', [BannerController::class, 'index'])->name('banners.index');
    Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
    Route::put('/banner/{id}', [BannerController::class, 'update'])->name('banners.update');
    Route::delete('/banner/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');
    
    //warna
    Route::get('/warna', [OnboardController::class, 'index'])->name('warna.index');
    Route::put('/warna/{id}', [OnboardController::class, 'update'])->name('warna.update');
    
    //events
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    
    //mahasiswa
    Route::get('/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
    Route::post('/mahasiswa', [MahasiswaController::class, 'store'])->name('mahasiswa.store');
    Route::put('/mahasiswa/{id}', [MahasiswaController::class, 'update'])->name('mahasiswa.update');
    Route::delete('/mahasiswa/{id}', [MahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');
    
    //pengelola
    Route::get('/pengelola', [PengelolaController::class, 'index'])->name('pengelola.index');
    
    //onBoard
    Route::get('/onBoard', [OnboardController::class, 'indexOnBoard'])->name('onBoard.index');
    Route::post('/onBoard', [OnboardController::class, 'storeOnBoard'])->name('onBoard.store');
    Route::put('/onBoard/{id}', [OnboardController::class, 'updateOnBoard'])->name('onBoard.update');

    //category event
    Route::get('/categoryEvent', [CategoryeventController::class, 'index'])->name('categoryEvent.index');
    Route::post('/categoryEvent', [CategoryeventController::class, 'store'])->name('categoryEvent.store');
    Route::put('/categoryEvent/{id}', [CategoryeventController::class, 'update'])->name('categoryEvent.update');
    Route::delete('/categoryEvent/{id}', [CategoryeventController::class, 'destroy'])->name('categoryEvent.destroy');
});

