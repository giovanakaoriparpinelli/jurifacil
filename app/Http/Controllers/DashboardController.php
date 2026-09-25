<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $prazos = $request->user()->prazos();

        return view('dashboard', [
            'totalAbertos' => (clone $prazos)->where('concluido', false)->count(),
            'totalVencidos' => (clone $prazos)->where('concluido', false)->whereDate('vencimento', '<', today())->count(),
            'totalSemana' => (clone $prazos)->where('concluido', false)->whereBetween('vencimento', [today(), today()->addDays(7)])->count(),
            'proximosPrazos' => (clone $prazos)->where('concluido', false)->orderBy('vencimento')->limit(5)->get(),
        ]);
    }
}
