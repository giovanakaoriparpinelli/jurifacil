@extends('layouts.app')

@section('title', 'Prazos')

@section('content')
  <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 20px; margin-bottom: 24px;">
    <h2 style="margin: 0 0 14px; font-size: 1rem;">Adicionar prazo</h2>
    <form method="POST" action="{{ route('prazos.store') }}" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 12px; align-items: end;">
      @csrf
      <div>
        <label style="display: block; font-size: 0.82rem; color: var(--ink-soft); margin-bottom: 6px;">Título</label>
        <input type="text" name="titulo" value="{{ old('titulo') }}" required
               style="width: 100%; padding: 10px 12px; background: var(--surface-2); border: 1px solid var(--border); border-radius: 9px; color: var(--ink); font-family: inherit; font-size: 0.9rem;">
      </div>
      <div>
        <label style="display: block; font-size: 0.82rem; color: var(--ink-soft); margin-bottom: 6px;">Vencimento</label>
        <input type="date" name="vencimento" value="{{ old('vencimento') }}" required
               style="width: 100%; padding: 10px 12px; background: var(--surface-2); border: 1px solid var(--border); border-radius: 9px; color: var(--ink); font-family: inherit; font-size: 0.9rem;">
      </div>
      <button type="submit"
              style="padding: 10px 18px; border: none; border-radius: 9px; background: var(--gradient); color: var(--accent-ink); font-weight: 700; font-size: 0.9rem; cursor: pointer; white-space: nowrap;">
        Adicionar
      </button>
    </form>
  </div>

  <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 20px;">
    <h2 style="margin: 0 0 14px; font-size: 1rem;">Todos os prazos</h2>

    @forelse ($prazos as $prazo)
      <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0; {{ !$loop->last ? 'border-bottom: 1px solid var(--border);' : '' }}">
        <form method="POST" action="{{ route('prazos.concluir', $prazo) }}">
          @csrf
          @method('PATCH')
          <button type="submit" title="{{ $prazo->concluido ? 'Marcar como pendente' : 'Marcar como concluído' }}"
                  style="width: 20px; height: 20px; border-radius: 6px; border: 1px solid var(--border); background: {{ $prazo->concluido ? 'var(--gradient)' : 'transparent' }}; cursor: pointer;">
          </button>
        </form>

        <div style="flex: 1;">
          <p style="margin: 0; {{ $prazo->concluido ? 'text-decoration: line-through; color: var(--ink-faint);' : '' }}">{{ $prazo->titulo }}</p>
          @if ($prazo->descricao)
            <p style="margin: 2px 0 0; font-size: 0.82rem; color: var(--ink-faint);">{{ $prazo->descricao }}</p>
          @endif
        </div>

        <span style="font-size: 0.85rem; color: {{ !$prazo->concluido && $prazo->vencimento->isPast() ? 'var(--danger)' : 'var(--ink-soft)' }};">
          {{ $prazo->vencimento->format('d/m/Y') }}
        </span>

        <form method="POST" action="{{ route('prazos.destroy', $prazo) }}" onsubmit="return confirm('Remover este prazo?');">
          @csrf
          @method('DELETE')
          <button type="submit" title="Remover"
                  style="background: none; border: none; color: var(--ink-faint); cursor: pointer; font-size: 0.9rem;">✕</button>
        </form>
      </div>
    @empty
      <p style="color: var(--ink-faint); margin: 0; font-size: 0.9rem;">Nenhum prazo cadastrado ainda.</p>
    @endforelse
  </div>
@endsection
