<?php

use App\Http\Controllers\GPIOController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/login', [HomeController::class, 'login'])->name('login');
// Route::get('/register', [HomeController::class, 'register'])->name('register');
// Route::post('/register', [HomeController::class, 'subRegister'])->name('register.submit');
Route::post('/login', [HomeController::class, 'subLogin'])->name('login.submit');
Route::get('/logout', [HomeController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/gpio/{pin}/{state}', [GPIOController::class, 'setGPIO'])->name('gpio');
    Route::get('/delays', [GPIOController::class, 'getDelaysStatus'])->name('delays');
    Route::get('/auto/{state}', [GPIOController::class, 'setAutoMode'])->name('auto');
});
