<?php

namespace App\Http\Controllers;

use App\Models\Prazo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrazoController extends Controller
{
    public function index(): View
    {
        $prazos = Prazo::with('user')
            ->orderBy('concluido')
            ->orderBy('vencimento')
            ->get();

        return view('prazos.index', ['prazos' => $prazos]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'vencimento' => ['required', 'date'],
        ]);

        $data['user_id'] = $request->user()->id;

        Prazo::create($data);

        return back()->with('status', 'Prazo adicionado.');
    }

    public function concluir(Prazo $prazo): RedirectResponse
    {
        $prazo->update(['concluido' => ! $prazo->concluido]);

        return back();
    }

    public function destroy(Prazo $prazo): RedirectResponse
    {
        $prazo->delete();

        return back()->with('status', 'Prazo removido.');
    }
}
