@extends('layouts.app')

@section('title', 'Perfil')

@section('content')
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 20px;">
      <h2 style="margin: 0 0 14px; font-size: 1rem;">Dados pessoais</h2>
      <form method="POST" action="{{ route('perfil.update') }}">
        @csrf
        @method('PUT')

        <label style="display: block; font-size: 0.82rem; color: var(--ink-soft); margin-bottom: 6px;">Nome</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
               style="width: 100%; padding: 10px 12px; margin-bottom: 14px; background: var(--surface-2); border: 1px solid var(--border); border-radius: 9px; color: var(--ink); font-family: inherit; font-size: 0.9rem;">

        <label style="display: block; font-size: 0.82rem; color: var(--ink-soft); margin-bottom: 6px;">E-mail</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
               style="width: 100%; padding: 10px 12px; margin-bottom: 18px; background: var(--surface-2); border: 1px solid var(--border); border-radius: 9px; color: var(--ink); font-family: inherit; font-size: 0.9rem;">

        <button type="submit"
                style="padding: 10px 18px; border: none; border-radius: 9px; background: var(--gradient); color: var(--accent-ink); font-weight: 700; font-size: 0.9rem; cursor: pointer;">
          Salvar
        </button>
      </form>
    </div>

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 20px;">
      <h2 style="margin: 0 0 14px; font-size: 1rem;">Alterar senha</h2>
      <form method="POST" action="{{ route('perfil.senha') }}">
        @csrf
        @method('PUT')

        <label style="display: block; font-size: 0.82rem; color: var(--ink-soft); margin-bottom: 6px;">Senha atual</label>
        <input type="password" name="current_password" required
               style="width: 100%; padding: 10px 12px; margin-bottom: 14px; background: var(--surface-2); border: 1px solid var(--border); border-radius: 9px; color: var(--ink); font-family: inherit; font-size: 0.9rem;">

        <label style="display: block; font-size: 0.82rem; color: var(--ink-soft); margin-bottom: 6px;">Nova senha</label>
        <input type="password" name="password" required
               style="width: 100%; padding: 10px 12px; margin-bottom: 14px; background: var(--surface-2); border: 1px solid var(--border); border-radius: 9px; color: var(--ink); font-family: inherit; font-size: 0.9rem;">

        <label style="display: block; font-size: 0.82rem; color: var(--ink-soft); margin-bottom: 6px;">Confirmar nova senha</label>
        <input type="password" name="password_confirmation" required
               style="width: 100%; padding: 10px 12px; margin-bottom: 18px; background: var(--surface-2); border: 1px solid var(--border); border-radius: 9px; color: var(--ink); font-family: inherit; font-size: 0.9rem;">

        <button type="submit"
                style="padding: 10px 18px; border: none; border-radius: 9px; background: transparent; border: 1px solid var(--border); color: var(--ink); font-weight: 600; font-size: 0.9rem; cursor: pointer;">
          Alterar senha
        </button>
      </form>
    </div>
  </div>
@endsection
