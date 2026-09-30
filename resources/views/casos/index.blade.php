@extends('layouts.app')

@section('title', 'Casos')

@section('content')
  <div class="card" style="margin-bottom: 24px;">
    <details {{ $errors->any() ? 'open' : '' }}>
      <summary style="cursor: pointer; font-size: 1rem; font-weight: 700; list-style: none;">+ Novo caso</summary>
      <form method="POST" action="{{ route('casos.store') }}" style="margin-top: 16px;">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px;">
          <div>
            <label class="field-label">Nome do caso *</label>
            <input type="text" name="nome" required maxlength="255" class="field" value="{{ old('nome') }}" placeholder="ex.: Silva x Banco Y">
          </div>
          <div>
            <label class="field-label">Cliente</label>
            <input type="text" name="cliente" maxlength="255" class="field" value="{{ old('cliente') }}">
          </div>
          <div>
            <label class="field-label">Nº do processo</label>
            <input type="text" name="numero_processo" maxlength="100" class="field" value="{{ old('numero_processo') }}">
          </div>
          <div style="grid-column: 1 / -1;">
            <label class="field-label">Descrição</label>
            <textarea name="descricao" rows="3" maxlength="5000" class="field">{{ old('descricao') }}</textarea>
          </div>
        </div>
        <button type="submit" class="btn-primary">Cadastrar caso</button>
      </form>
    </details>
  </div>

  <div class="card">
    <h2 style="margin: 0 0 14px; font-size: 1rem;">Casos ({{ $casos->count() }})</h2>

    @forelse ($casos as $caso)
      <div style="display: flex; align-items: center; gap: 12px; padding: 14px 0; flex-wrap: wrap; {{ !$loop->last ? 'border-bottom: 1px solid var(--border);' : '' }}">
        <div style="flex: 1; min-width: 220px;">
          <p style="margin: 0; font-weight: 600;">{{ $caso->nome }}</p>
          <p style="margin: 2px 0 0; font-size: 0.82rem; color: var(--ink-faint);">
            {{ $caso->cliente ?: 'sem cliente informado' }}@if ($caso->numero_processo) · Proc. {{ $caso->numero_processo }}@endif
          </p>
        </div>

        <a href="{{ route('tarefas.index', ['caso' => $caso->id, 'status' => 'todas']) }}" class="badge">{{ $caso->tarefas_count }} tarefa(s)</a>
        <a href="{{ route('documentos.index', ['caso' => $caso->id]) }}" class="badge">{{ $caso->documentos_count }} documento(s)</a>

        <button type="button" class="icon-btn abrir-caso" data-id="{{ $caso->id }}" title="Editar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m16.5 3.5 4 4L8 20H4v-4Z"/></svg>
        </button>

        <form method="POST" action="{{ route('casos.destroy', $caso) }}" onsubmit="return confirm('Remover o caso {{ addslashes($caso->nome) }}? As tarefas e os documentos dele são mantidos, só ficam sem caso.');">
          @csrf
          @method('DELETE')
          <button type="submit" class="icon-btn" title="Excluir">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13"/></svg>
          </button>
        </form>
      </div>
    @empty
      <p style="color: var(--ink-faint); margin: 0; font-size: 0.9rem;">Nenhum caso cadastrado. Crie casos para agrupar tarefas e documentos por cliente ou processo.</p>
    @endforelse
  </div>

  <script type="application/json" id="casos-data">
    {!! json_encode($casos->keyBy('id')->map(fn ($c) => ['id' => $c->id, 'nome' => $c->nome, 'cliente' => $c->cliente, 'numero_processo' => $c->numero_processo, 'descricao' => $c->descricao]), JSON_HEX_TAG | JSON_HEX_AMP) !!}
  </script>

  <div class="modal-backdrop" id="modal-caso">
    <div class="modal">
      <span class="modal-close" data-close="modal-caso">&times;</span>
      <h2 style="margin: 0 0 16px; font-size: 1.1rem; font-family: 'Fraunces', Georgia, serif;">Editar caso</h2>
      <form method="POST" id="form-caso">
        @csrf
        @method('PUT')
        <label class="field-label">Nome do caso</label>
        <input type="text" name="nome" id="c_nome" required maxlength="255" class="field" style="margin-bottom: 12px;">
        <label class="field-label">Cliente</label>
        <input type="text" name="cliente" id="c_cliente" maxlength="255" class="field" style="margin-bottom: 12px;">
        <label class="field-label">Nº do processo</label>
        <input type="text" name="numero_processo" id="c_processo" maxlength="100" class="field" style="margin-bottom: 12px;">
        <label class="field-label">Descrição</label>
        <textarea name="descricao" id="c_descricao" rows="4" maxlength="5000" class="field" style="margin-bottom: 18px;"></textarea>
        <button type="submit" class="btn-primary" style="width: 100%;">Salvar</button>
      </form>
    </div>
  </div>

  <script>
    (function () {
      var dados = JSON.parse(document.getElementById('casos-data').textContent || '{}');
      var modal = document.getElementById('modal-caso');

      function fechar() { modal.classList.remove('open'); }
      document.querySelector('[data-close="modal-caso"]').addEventListener('click', fechar);
      modal.addEventListener('click', function (e) { if (e.target === modal) fechar(); });

      document.querySelectorAll('.abrir-caso').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var c = dados[btn.getAttribute('data-id')];
          if (!c) return;
          document.getElementById('c_nome').value = c.nome || '';
          document.getElementById('c_cliente').value = c.cliente || '';
          document.getElementById('c_processo').value = c.numero_processo || '';
          document.getElementById('c_descricao').value = c.descricao || '';
          document.getElementById('form-caso').action = '/casos/' + c.id;
          modal.classList.add('open');
        });
      });
    })();
  </script>
@endsection
