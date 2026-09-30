@extends('layouts.app')

@section('title', 'Escritórios')

@section('content')
  <div class="card">
    <h2 style="margin: 0 0 14px; font-size: 1rem;">Escritórios cadastrados ({{ $tenants->count() }})</h2>

    @forelse ($tenants as $tenant)
      <div style="display: flex; align-items: center; gap: 12px; padding: 14px 0; {{ !$loop->last ? 'border-bottom: 1px solid var(--border);' : '' }}">
        <div style="flex: 1;">
          <p style="margin: 0; font-weight: 600;">{{ $tenant->nome }}</p>
          <p style="margin: 2px 0 0; font-size: 0.82rem; color: var(--ink-faint);">
            {{ $tenant->usuarios->count() }} usuário(s) — admin: {{ $tenant->usuarios->firstWhere('role', 'admin')?->email ?? '—' }}
          </p>
          <p style="margin: 2px 0 0; font-size: 0.78rem; color: var(--ink-faint);">Cadastrado em {{ $tenant->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <span class="badge" style="
          @if ($tenant->status === 'aprovado') color: #6ee7b7; border-color: rgba(110,231,183,0.4);
          @elseif ($tenant->status === 'rejeitado') color: var(--danger); border-color: rgba(248,113,113,0.4);
          @else color: #fbbf24; border-color: rgba(251,191,36,0.4);
          @endif
        ">
          {{ ucfirst($tenant->status) }}
        </span>

        @if ($tenant->status !== 'aprovado')
          <form method="POST" action="{{ route('superadmin.escritorios.aprovar', $tenant) }}">
            @csrf
            <button type="submit" class="btn-primary">Aprovar</button>
          </form>
        @endif

        @if ($tenant->status !== 'rejeitado')
          <form method="POST" action="{{ route('superadmin.escritorios.rejeitar', $tenant) }}" onsubmit="return confirm('Rejeitar o cadastro de {{ $tenant->nome }}? Ninguém desse escritório vai conseguir logar.');">
            @csrf
            <button type="submit" class="btn-ghost">Rejeitar</button>
          </form>
        @endif

        @if (! $tenant->usuarios->contains('is_super_admin', true))
          <form method="POST" action="{{ route('superadmin.escritorios.resetar-senha', $tenant) }}" onsubmit="return confirm('Redefinir a senha do admin de {{ $tenant->nome }}? Uma nova senha temporária será gerada e a atual deixa de funcionar.');">
            @csrf
            <button type="submit" class="icon-btn" title="Redefinir senha do admin">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M16 7l3 3M14 9l2 2"/></svg>
            </button>
          </form>

          <form method="POST" action="{{ route('superadmin.escritorios.excluir', $tenant) }}" onsubmit="return confirm('EXCLUIR {{ $tenant->nome }}? Todos os {{ $tenant->usuarios->count() }} usuário(s) e todos os prazos desse escritório serão apagados. Não há como desfazer.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="icon-btn" title="Excluir escritório">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13"/></svg>
            </button>
          </form>
        @endif
      </div>
    @empty
      <p style="color: var(--ink-faint); margin: 0; font-size: 0.9rem;">Nenhum escritório cadastrado ainda.</p>
    @endforelse
  </div>
@endsection
