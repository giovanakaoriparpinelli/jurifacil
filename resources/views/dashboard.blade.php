@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
  <p style="color: var(--ink-soft); margin-top: 0;">Bem-vindo(a), {{ Auth::user()->name }}.</p>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin: 20px 0 32px;">
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 18px;">
      <p style="margin: 0 0 6px; color: var(--ink-faint); font-size: 0.8rem;">Prazos em aberto</p>
      <p style="margin: 0; font-size: 1.8rem; font-weight: 700;">{{ $totalAbertos }}</p>
    </div>
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 18px;">
      <p style="margin: 0 0 6px; color: var(--ink-faint); font-size: 0.8rem;">Vencendo em 7 dias</p>
      <p style="margin: 0; font-size: 1.8rem; font-weight: 700; color: var(--accent-2);">{{ $totalSemana }}</p>
    </div>
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 18px;">
      <p style="margin: 0 0 6px; color: var(--ink-faint); font-size: 0.8rem;">Vencidos</p>
      <p style="margin: 0; font-size: 1.8rem; font-weight: 700; color: {{ $totalVencidos > 0 ? 'var(--danger)' : 'var(--ink)' }};">{{ $totalVencidos }}</p>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin: -8px 0 32px;">
    <a href="{{ route('tarefas.index', ['responsavel' => 'eu']) }}" style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 18px; display: block;">
      <p style="margin: 0 0 6px; color: var(--ink-faint); font-size: 0.8rem;">Minhas tarefas em aberto</p>
      <p style="margin: 0; font-size: 1.8rem; font-weight: 700;">{{ $minhasTarefasAbertas }}</p>
    </a>
    <a href="{{ route('tarefas.index') }}" style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 18px; display: block;">
      <p style="margin: 0 0 6px; color: var(--ink-faint); font-size: 0.8rem;">Tarefas atrasadas (escritório)</p>
      <p style="margin: 0; font-size: 1.8rem; font-weight: 700; color: {{ $tarefasAtrasadas > 0 ? 'var(--danger)' : 'var(--ink)' }};">{{ $tarefasAtrasadas }}</p>
    </a>
  </div>

  <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 20px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
      <h2 style="margin: 0; font-size: 1rem;">Próximos prazos</h2>
      <a href="{{ route('prazos.index') }}" style="font-size: 0.82rem; color: var(--accent-2);">Ver todos →</a>
    </div>

    @forelse ($proximosPrazos as $prazo)
      <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 0; {{ !$loop->last ? 'border-bottom: 1px solid var(--border);' : '' }}">
        <span>{{ $prazo->titulo }}</span>
        <span style="color: var(--ink-soft); font-size: 0.85rem;">{{ $prazo->vencimento->format('d/m/Y') }}</span>
      </div>
    @empty
      <p style="color: var(--ink-faint); margin: 0; font-size: 0.9rem;">Nenhum prazo cadastrado ainda.</p>
    @endforelse
  </div>
@endsection
