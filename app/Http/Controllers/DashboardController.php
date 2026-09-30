<?php

namespace App\Http\Controllers;

use App\Models\Prazo;
use App\Models\Tarefa;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $prazos = Prazo::query();

        return view('dashboard', [
            'totalAbertos' => (clone $prazos)->where('concluido', false)->count(),
            'totalVencidos' => (clone $prazos)->where('concluido', false)->whereDate('vencimento', '<', today())->count(),
            'totalSemana' => (clone $prazos)->where('concluido', false)->whereBetween('vencimento', [today(), today()->addDays(7)])->count(),
            'minhasTarefasAbertas' => Tarefa::where('responsavel_id', $request->user()->id)->where('status', '!=', 'concluida')->count(),
            'tarefasAtrasadas' => Tarefa::where('status', '!=', 'concluida')->whereDate('prazo', '<', today())->count(),
            'proximosPrazos' => (clone $prazos)->where('concluido', false)->orderBy('vencimento')->limit(5)->get(),
        ]);
    }
}
