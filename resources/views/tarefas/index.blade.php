@extends('layouts.app')

@section('title', 'Tarefas')

@section('content')
  <div class="card" style="margin-bottom: 24px;">
    <details {{ $errors->any() ? 'open' : '' }}>
      <summary style="cursor: pointer; font-size: 1rem; font-weight: 700; list-style: none;">+ Nova tarefa</summary>
      <form method="POST" action="{{ route('tarefas.store') }}" style="margin-top: 16px;">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px;">
          <div style="grid-column: 1 / -1;">
            <label class="field-label">Título *</label>
            <input type="text" name="titulo" required maxlength="255" class="field" value="{{ old('titulo') }}">
          </div>
          <div>
            <label class="field-label">Prazo</label>
            <input type="date" name="prazo" class="field" value="{{ old('prazo') }}">
          </div>
          <div>
            <label class="field-label">Prioridade</label>
            <select name="prioridade" class="field">
              @foreach (\App\Models\Tarefa::PRIORIDADES as $valor => $rotulo)
                <option value="{{ $valor }}" @selected(old('prioridade', 'media') === $valor)>{{ $rotulo }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="field-label">Status</label>
            <select name="status" class="field">
              @foreach (\App\Models\Tarefa::STATUS as $valor => $rotulo)
                <option value="{{ $valor }}" @selected(old('status', 'a_fazer') === $valor)>{{ $rotulo }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="field-label">Responsável</label>
            <select name="responsavel_id" class="field">
              <option value="">— ninguém —</option>
              @foreach ($usuarios as $u)
                <option value="{{ $u->id }}" @selected((string) old('responsavel_id', auth()->id()) === (string) $u->id)>{{ $u->name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="field-label">Caso</label>
            <select name="caso_id" class="field">
              <option value="">— sem caso —</option>
              @foreach ($casos as $c)
                <option value="{{ $c->id }}" @selected((string) old('caso_id') === (string) $c->id)>{{ $c->nome }}</option>
              @endforeach
            </select>
          </div>
          <div style="grid-column: 1 / -1;">
            <label class="field-label">Descrição</label>
            <textarea name="descricao" rows="3" maxlength="5000" class="field">{{ old('descricao') }}</textarea>
          </div>
        </div>
        <button type="submit" class="btn-primary">Adicionar tarefa</button>
      </form>
    </details>
  </div>

  <div class="card" style="margin-bottom: 24px;">
    <form method="GET" action="{{ route('tarefas.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; align-items: end;">
      <div>
        <label class="field-label">Buscar</label>
        <input type="text" name="q" class="field" value="{{ $filtros['q'] }}" placeholder="título ou descrição">
      </div>
      <div>
        <label class="field-label">Status</label>
        <select name="status" class="field">
          <option value="abertas" @selected($filtros['status'] === 'abertas')>Em aberto</option>
          @foreach (\App\Models\Tarefa::STATUS as $valor => $rotulo)
            <option value="{{ $valor }}" @selected($filtros['status'] === $valor)>{{ $rotulo }}</option>
          @endforeach
          <option value="todas" @selected($filtros['status'] === 'todas')>Todas</option>
        </select>
      </div>
      <div>
        <label class="field-label">Prioridade</label>
        <select name="prioridade" class="field">
          <option value="">Todas</option>
          @foreach (\App\Models\Tarefa::PRIORIDADES as $valor => $rotulo)
            <option value="{{ $valor }}" @selected($filtros['prioridade'] === $valor)>{{ $rotulo }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="field-label">Responsável</label>
        <select name="responsavel" class="field">
          <option value="">Todos</option>
          <option value="eu" @selected($filtros['responsavel'] === 'eu')>Só minhas</option>
          @foreach ($usuarios as $u)
            <option value="{{ $u->id }}" @selected($filtros['responsavel'] === (string) $u->id)>{{ $u->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="field-label">Caso</label>
        <select name="caso" class="field">
          <option value="">Todos</option>
          @foreach ($casos as $c)
            <option value="{{ $c->id }}" @selected($filtros['caso'] === (string) $c->id)>{{ $c->nome }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn-ghost">Filtrar</button>
    </form>
  </div>

  <div class="card">
    <h2 style="margin: 0 0 14px; font-size: 1rem;">Tarefas ({{ $tarefas->count() }})</h2>

    @forelse ($tarefas as $tarefa)
      <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0; flex-wrap: wrap; {{ !$loop->last ? 'border-bottom: 1px solid var(--border);' : '' }}">
        <form method="POST" action="{{ route('tarefas.concluir', $tarefa) }}">
          @csrf
          @method('PATCH')
          <button type="submit" title="{{ $tarefa->isConcluida() ? 'Reabrir' : 'Marcar como concluída' }}"
                  style="width: 20px; height: 20px; border-radius: 6px; border: 1px solid var(--border); background: {{ $tarefa->isConcluida() ? 'var(--gradient)' : 'transparent' }}; cursor: pointer;">
          </button>
        </form>

        <div style="flex: 1; min-width: 200px;">
          <p style="margin: 0; font-weight: 600; {{ $tarefa->isConcluida() ? 'text-decoration: line-through; color: var(--ink-faint);' : '' }}">{{ $tarefa->titulo }}</p>
          <p style="margin: 2px 0 0; font-size: 0.8rem; color: var(--ink-faint);">
            @if ($tarefa->caso) {{ $tarefa->caso->nome }} · @endif
            @if ($tarefa->responsavel) {{ $tarefa->responsavel->name }} @else sem responsável @endif
          </p>
        </div>

        <span class="badge" style="
          @if ($tarefa->prioridade === 'alta') color: var(--danger); border-color: rgba(248,113,113,0.4);
          @elseif ($tarefa->prioridade === 'media') color: #fbbf24; border-color: rgba(251,191,36,0.4);
          @endif
        ">{{ \App\Models\Tarefa::PRIORIDADES[$tarefa->prioridade] ?? $tarefa->prioridade }}</span>

        <span class="badge">{{ \App\Models\Tarefa::STATUS[$tarefa->status] ?? $tarefa->status }}</span>

        <span style="font-size: 0.85rem; min-width: 84px; text-align: right; color: {{ $tarefa->isAtrasada() ? 'var(--danger)' : 'var(--ink-soft)' }};">
          {{ $tarefa->prazo?->format('d/m/Y') ?? 'sem prazo' }}
        </span>

        <button type="button" class="icon-btn abrir-tarefa" data-id="{{ $tarefa->id }}" title="Editar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m16.5 3.5 4 4L8 20H4v-4Z"/></svg>
        </button>

        <form method="POST" action="{{ route('tarefas.destroy', $tarefa) }}" onsubmit="return confirm('Remover esta tarefa?');">
          @csrf
          @method('DELETE')
          <button type="submit" class="icon-btn" title="Excluir">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13"/></svg>
          </button>
        </form>
      </div>
    @empty
      <p style="color: var(--ink-faint); margin: 0; font-size: 0.9rem;">Nenhuma tarefa encontrada com esses filtros.</p>
    @endforelse
  </div>

  <script type="application/json" id="tarefas-data">
    {!! json_encode($tarefas->keyBy('id')->map(fn ($t) => [
        'id' => $t->id, 'titulo' => $t->titulo, 'descricao' => $t->descricao,
        'prazo' => $t->prazo?->format('Y-m-d'), 'prioridade' => $t->prioridade, 'status' => $t->status,
        'caso_id' => $t->caso_id, 'responsavel_id' => $t->responsavel_id,
    ]), JSON_HEX_TAG | JSON_HEX_AMP) !!}
  </script>

  <div class="modal-backdrop" id="modal-tarefa">
    <div class="modal">
      <span class="modal-close" data-close="modal-tarefa">&times;</span>
      <h2 style="margin: 0 0 16px; font-size: 1.1rem; font-family: 'Fraunces', Georgia, serif;">Editar tarefa</h2>
      <form method="POST" id="form-tarefa">
        @csrf
        @method('PUT')
        <label class="field-label">Título</label>
        <input type="text" name="titulo" id="t_titulo" required maxlength="255" class="field" style="margin-bottom: 12px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
          <div>
            <label class="field-label">Prazo</label>
            <input type="date" name="prazo" id="t_prazo" class="field">
          </div>
          <div>
            <label class="field-label">Prioridade</label>
            <select name="prioridade" id="t_prioridade" class="field">
              @foreach (\App\Models\Tarefa::PRIORIDADES as $valor => $rotulo)
                <option value="{{ $valor }}">{{ $rotulo }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="field-label">Status</label>
            <select name="status" id="t_status" class="field">
              @foreach (\App\Models\Tarefa::STATUS as $valor => $rotulo)
                <option value="{{ $valor }}">{{ $rotulo }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="field-label">Responsável</label>
            <select name="responsavel_id" id="t_responsavel" class="field">
              <option value="">— ninguém —</option>
              @foreach ($usuarios as $u)
                <option value="{{ $u->id }}">{{ $u->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <label class="field-label">Caso</label>
        <select name="caso_id" id="t_caso" class="field" style="margin-bottom: 12px;">
          <option value="">— sem caso —</option>
          @foreach ($casos as $c)
            <option value="{{ $c->id }}">{{ $c->nome }}</option>
          @endforeach
        </select>
        <label class="field-label">Descrição</label>
        <textarea name="descricao" id="t_descricao" rows="4" maxlength="5000" class="field" style="margin-bottom: 18px;"></textarea>
        <button type="submit" class="btn-primary" style="width: 100%;">Salvar</button>
      </form>
    </div>
  </div>

  <script>
    (function () {
      var dados = JSON.parse(document.getElementById('tarefas-data').textContent || '{}');
      var modal = document.getElementById('modal-tarefa');

      function fechar() { modal.classList.remove('open'); }
      document.querySelector('[data-close="modal-tarefa"]').addEventListener('click', fechar);
      modal.addEventListener('click', function (e) { if (e.target === modal) fechar(); });

      document.querySelectorAll('.abrir-tarefa').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var t = dados[btn.getAttribute('data-id')];
          if (!t) return;
          document.getElementById('t_titulo').value = t.titulo || '';
          document.getElementById('t_descricao').value = t.descricao || '';
          document.getElementById('t_prazo').value = t.prazo || '';
          document.getElementById('t_prioridade').value = t.prioridade;
          document.getElementById('t_status').value = t.status;
          document.getElementById('t_responsavel').value = t.responsavel_id || '';
          document.getElementById('t_caso').value = t.caso_id || '';
          document.getElementById('form-tarefa').action = '/tarefas/' + t.id;
          modal.classList.add('open');
        });
      });
    })();
  </script>
@endsection
