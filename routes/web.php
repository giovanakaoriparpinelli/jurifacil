<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FerramentaController;
use App\Http\Controllers\PrazoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/registro', [RegisterController::class, 'show'])->name('register');
    Route::post('/registro', [RegisterController::class, 'store']);
});

Route::get('/clientes/intimacoes', [FerramentaController::class, 'areaCliente'])->name('clientes.intimacoes');

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

    Route::get('/ferramentas/movimentacoes', [FerramentaController::class, 'movimentacoes'])->name('ferramentas.movimentacoes');
    Route::get('/ferramentas/movimentacoes/consulta', [FerramentaController::class, 'movimentacoesConsulta'])->name('ferramentas.movimentacoes.consulta');
    Route::get('/ferramentas/intimacoes', [FerramentaController::class, 'intimacoes'])->name('ferramentas.intimacoes');

    Route::get('/equipe', [TeamController::class, 'index'])->name('equipe.index');
    Route::post('/equipe', [TeamController::class, 'store'])->name('equipe.store');
    Route::put('/escritorio', [TeamController::class, 'atualizarEscritorio'])->name('escritorio.update');
    Route::post('/equipe/{usuario}/resetar-senha', [TeamController::class, 'resetarSenha'])->name('equipe.resetar-senha');
    Route::put('/equipe/{usuario}', [TeamController::class, 'update'])->name('equipe.update');
    Route::delete('/equipe/{usuario}', [TeamController::class, 'destroy'])->name('equipe.destroy');

    Route::get('/admin/escritorios', [SuperAdminController::class, 'index'])->name('superadmin.escritorios');
    Route::post('/admin/escritorios/{tenant}/aprovar', [SuperAdminController::class, 'aprovar'])->name('superadmin.escritorios.aprovar');
    Route::post('/admin/escritorios/{tenant}/rejeitar', [SuperAdminController::class, 'rejeitar'])->name('superadmin.escritorios.rejeitar');
    Route::post('/admin/escritorios/{tenant}/resetar-senha', [SuperAdminController::class, 'resetarSenhaAdmin'])->name('superadmin.escritorios.resetar-senha');
    Route::delete('/admin/escritorios/{tenant}', [SuperAdminController::class, 'excluir'])->name('superadmin.escritorios.excluir');
});
