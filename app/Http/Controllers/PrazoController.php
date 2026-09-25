<?php

namespace App\Http\Controllers;

use App\Models\Prazo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrazoController extends Controller
{
    public function index(Request $request): View
    {
        $prazos = $request->user()
            ->prazos()
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

        $request->user()->prazos()->create($data);

        return back()->with('status', 'Prazo adicionado.');
    }

    public function concluir(Request $request, Prazo $prazo): RedirectResponse
    {
        abort_unless($prazo->user_id === $request->user()->id, 403);

        $prazo->update(['concluido' => ! $prazo->concluido]);

        return back();
    }

    public function destroy(Request $request, Prazo $prazo): RedirectResponse
    {
        abort_unless($prazo->user_id === $request->user()->id, 403);

        $prazo->delete();

        return back()->with('status', 'Prazo removido.');
    }
}
