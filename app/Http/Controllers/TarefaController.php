<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TarefaController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = [
            'status' => $request->get('status', 'abertas'),
            'prioridade' => $request->get('prioridade', ''),
            'responsavel' => $request->get('responsavel', ''),
            'caso' => $request->get('caso', ''),
            'q' => trim((string) $request->get('q', '')),
        ];

        $query = Tarefa::with(['caso', 'responsavel']);

        match ($filtros['status']) {
            'abertas' => $query->where('status', '!=', 'concluida'),
            'todas' => null,
            default => $query->where('status', $filtros['status']),
        };

        if (array_key_exists($filtros['prioridade'], Tarefa::PRIORIDADES)) {
            $query->where('prioridade', $filtros['prioridade']);
        }
        if ($filtros['responsavel'] === 'eu') {
            $query->where('responsavel_id', $request->user()->id);
        } elseif (ctype_digit($filtros['responsavel'])) {
            $query->where('responsavel_id', (int) $filtros['responsavel']);
        }
        if (ctype_digit($filtros['caso'])) {
            $query->where('caso_id', (int) $filtros['caso']);
        }
        if ($filtros['q'] !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\%', '\_'], $filtros['q']).'%';
            $query->where(fn ($q) => $q->where('titulo', 'like', $termo)->orWhere('descricao', 'like', $termo));
        }

        $tarefas = $query
            ->orderByRaw("status = 'concluida'")
            ->orderByRaw('prazo is null')
            ->orderBy('prazo')
            ->orderByRaw("case prioridade when 'alta' then 0 when 'media' then 1 else 2 end")
            ->get();

        return view('tarefas.index', [
            'tarefas' => $tarefas,
            'filtros' => $filtros,
            'casos' => Caso::orderBy('nome')->get(),
            'usuarios' => User::where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);
        $data['criado_por'] = $request->user()->id;

        $tarefa = new Tarefa($data);
        $tarefa->concluida_em = $data['status'] === 'concluida' ? now() : null;
        $tarefa->save();

        return back()->with('status', 'Tarefa adicionada.');
    }

    public function update(Request $request, Tarefa $tarefa): RedirectResponse
    {
        $data = $this->validar($request);
        $status = $data['status'];
        unset($data['status']);

        $tarefa->fill($data);
        $tarefa->definirStatus($status);

        return back()->with('status', 'Tarefa atualizada.');
    }

    public function concluir(Tarefa $tarefa): RedirectResponse
    {
        $tarefa->definirStatus($tarefa->isConcluida() ? 'a_fazer' : 'concluida');

        return back();
    }

    public function destroy(Tarefa $tarefa): RedirectResponse
    {
        $tarefa->delete();

        return back()->with('status', 'Tarefa removida.');
    }

    private function validar(Request $request): array
    {
        $tenantId = $request->user()->tenant_id;

        return $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'prazo' => ['nullable', 'date'],
            'prioridade' => ['required', Rule::in(array_keys(Tarefa::PRIORIDADES))],
            'status' => ['required', Rule::in(array_keys(Tarefa::STATUS))],
            'caso_id' => ['nullable', Rule::exists('casos', 'id')->where('tenant_id', $tenantId)],
            'responsavel_id' => ['nullable', Rule::exists('users', 'id')->where('tenant_id', $tenantId)],
        ]);
    }
}
