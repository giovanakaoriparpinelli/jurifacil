<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrazoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/perfil', [ProfileController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('perfil.update');
    Route::put('/perfil/senha', [ProfileController::class, 'updatePassword'])->name('perfil.senha');

    Route::get('/prazos', [PrazoController::class, 'index'])->name('prazos.index');
    Route::post('/prazos', [PrazoController::class, 'store'])->name('prazos.store');
    Route::patch('/prazos/{prazo}/concluir', [PrazoController::class, 'concluir'])->name('prazos.concluir');
    Route::delete('/prazos/{prazo}', [PrazoController::class, 'destroy'])->name('prazos.destroy');
});
