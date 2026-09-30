<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CasoController extends Controller
{
    public function index(): View
    {
        return view('casos.index', [
            'casos' => Caso::withCount(['tarefas', 'documentos'])->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Caso::create($this->validar($request));

        return back()->with('status', 'Caso cadastrado.');
    }

    public function update(Request $request, Caso $caso): RedirectResponse
    {
        $caso->update($this->validar($request));

        return back()->with('status', 'Caso atualizado.');
    }

    public function destroy(Caso $caso): RedirectResponse
    {
        // Tarefas e documentos do caso continuam existindo, só ficam "sem caso" (FK nullOnDelete).
        $caso->delete();

        return back()->with('status', 'Caso removido. Suas tarefas e documentos foram mantidos, sem caso associado.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cliente' => ['nullable', 'string', 'max:255'],
            'numero_processo' => ['nullable', 'string', 'max:100'],
            'descricao' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
