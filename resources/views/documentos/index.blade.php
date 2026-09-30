@extends('layouts.app')

@section('title', 'Documentos')

@section('content')
  <div class="card" style="margin-bottom: 24px;">
    <details {{ $errors->any() ? 'open' : '' }}>
      <summary style="cursor: pointer; font-size: 1rem; font-weight: 700; list-style: none;">+ Enviar documento(s)</summary>
      <form method="POST" action="{{ route('documentos.store') }}" enctype="multipart/form-data" style="margin-top: 16px;">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px;">
          <div style="grid-column: 1 / -1;">
            <label class="field-label">Arquivo(s) * — até 20 arquivos, 20 MB cada</label>
            <input type="file" name="arquivos[]" multiple required class="field"
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.odt,.ods,.txt,.rtf,.csv,.jpg,.jpeg,.png,.zip">
          </div>
          <div>
            <label class="field-label">Nome (só para envio de 1 arquivo)</label>
            <input type="text" name="nome" maxlength="255" class="field" value="{{ old('nome') }}" placeholder="padrão: nome do arquivo">
          </div>
          <div>
            <label class="field-label">Categoria</label>
            <select name="categoria" class="field">
              @foreach (\App\Models\Documento::CATEGORIAS as $valor => $rotulo)
                <option value="{{ $valor }}" @selected(old('categoria', 'outros') === $valor)>{{ $rotulo }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="field-label">Caso</label>
            <select name="caso_id" class="field">
              <option value="">— sem caso —</option>
              @foreach ($casos as $c)
                <option value="{{ $c->id }}" @selected((string) old('caso_id', $filtros['caso']) === (string) $c->id)>{{ $c->nome }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <button type="submit" class="btn-primary">Enviar</button>
      </form>
    </details>
  </div>

  <div class="card" style="margin-bottom: 24px;">
    <form method="GET" action="{{ route('documentos.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: end;">
      <div>
        <label class="field-label">Buscar</label>
        <input type="text" name="q" class="field" value="{{ $filtros['q'] }}" placeholder="nome do documento">
      </div>
      <div>
        <label class="field-label">Caso</label>
        <select name="caso" class="field">
          <option value="">Todos</option>
          <option value="sem" @selected($filtros['caso'] === 'sem')>Sem caso</option>
          @foreach ($casos as $c)
            <option value="{{ $c->id }}" @selected($filtros['caso'] === (string) $c->id)>{{ $c->nome }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="field-label">Categoria</label>
        <select name="categoria" class="field">
          <option value="">Todas</option>
          @foreach (\App\Models\Documento::CATEGORIAS as $valor => $rotulo)
            <option value="{{ $valor }}" @selected($filtros['categoria'] === $valor)>{{ $rotulo }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn-ghost">Filtrar</button>
    </form>
  </div>

  <div class="card">
    <h2 style="margin: 0 0 14px; font-size: 1rem;">Documentos ({{ $documentos->count() }})</h2>

    @forelse ($documentos as $doc)
      <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0; flex-wrap: wrap; {{ !$loop->last ? 'border-bottom: 1px solid var(--border);' : '' }}">
        <div style="flex: 1; min-width: 220px;">
          <a href="{{ route('documentos.download', $doc) }}" style="font-weight: 600; color: var(--ink);">{{ $doc->nome }}</a>
          <p style="margin: 2px 0 0; font-size: 0.8rem; color: var(--ink-faint);">
            {{ $doc->arquivo_original }} · {{ $doc->tamanhoLegivel() }} · {{ $doc->created_at->format('d/m/Y') }}@if ($doc->autor) · {{ $doc->autor->name }}@endif
          </p>
        </div>

        <span class="badge">{{ \App\Models\Documento::CATEGORIAS[$doc->categoria] ?? $doc->categoria }}</span>
        <span style="font-size: 0.82rem; color: var(--ink-soft); min-width: 110px;">{{ $doc->caso?->nome ?? 'sem caso' }}</span>

        <a href="{{ route('documentos.download', $doc) }}" class="icon-btn" title="Baixar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v11m0 0-4-4m4 4 4-4M5 20h14"/></svg>
        </a>

        <button type="button" class="icon-btn abrir-documento" data-id="{{ $doc->id }}" title="Editar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m16.5 3.5 4 4L8 20H4v-4Z"/></svg>
        </button>

        <form method="POST" action="{{ route('documentos.destroy', $doc) }}" onsubmit="return confirm('Excluir este documento? O arquivo será apagado e não há como desfazer.');">
          @csrf
          @method('DELETE')
          <button type="submit" class="icon-btn" title="Excluir">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13"/></svg>
          </button>
        </form>
      </div>
    @empty
      <p style="color: var(--ink-faint); margin: 0; font-size: 0.9rem;">Nenhum documento encontrado.</p>
    @endforelse
  </div>

  <script type="application/json" id="documentos-data">
    {!! json_encode($documentos->keyBy('id')->map(fn ($d) => ['id' => $d->id, 'nome' => $d->nome, 'categoria' => $d->categoria, 'caso_id' => $d->caso_id]), JSON_HEX_TAG | JSON_HEX_AMP) !!}
  </script>

  <div class="modal-backdrop" id="modal-documento">
    <div class="modal">
      <span class="modal-close" data-close="modal-documento">&times;</span>
      <h2 style="margin: 0 0 16px; font-size: 1.1rem; font-family: 'Fraunces', Georgia, serif;">Editar documento</h2>
      <form method="POST" id="form-documento">
        @csrf
        @method('PUT')
        <label class="field-label">Nome</label>
        <input type="text" name="nome" id="d_nome" required maxlength="255" class="field" style="margin-bottom: 12px;">
        <label class="field-label">Categoria</label>
        <select name="categoria" id="d_categoria" class="field" style="margin-bottom: 12px;">
          @foreach (\App\Models\Documento::CATEGORIAS as $valor => $rotulo)
            <option value="{{ $valor }}">{{ $rotulo }}</option>
          @endforeach
        </select>
        <label class="field-label">Caso</label>
        <select name="caso_id" id="d_caso" class="field" style="margin-bottom: 18px;">
          <option value="">— sem caso —</option>
          @foreach ($casos as $c)
            <option value="{{ $c->id }}">{{ $c->nome }}</option>
          @endforeach
        </select>
        <button type="submit" class="btn-primary" style="width: 100%;">Salvar</button>
      </form>
    </div>
  </div>

  <script>
    (function () {
      var dados = JSON.parse(document.getElementById('documentos-data').textContent || '{}');
      var modal = document.getElementById('modal-documento');

      function fechar() { modal.classList.remove('open'); }
      document.querySelector('[data-close="modal-documento"]').addEventListener('click', fechar);
      modal.addEventListener('click', function (e) { if (e.target === modal) fechar(); });

      document.querySelectorAll('.abrir-documento').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var d = dados[btn.getAttribute('data-id')];
          if (!d) return;
          document.getElementById('d_nome').value = d.nome || '';
          document.getElementById('d_categoria').value = d.categoria;
          document.getElementById('d_caso').value = d.caso_id || '';
          document.getElementById('form-documento').action = '/documentos/' + d.id;
          modal.classList.add('open');
        });
      });
    })();
  </script>
@endsection
