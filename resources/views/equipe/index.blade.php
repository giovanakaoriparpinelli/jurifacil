@extends('layouts.app')

@section('title', 'Equipe')

@section('content')
  <div class="card" style="margin-bottom: 24px;">
    <h2 style="margin: 0 0 14px; font-size: 1rem;">Nome do escritório</h2>
    <form method="POST" action="{{ route('escritorio.update') }}" style="display: flex; gap: 12px; align-items: end; flex-wrap: wrap;">
      @csrf
      @method('PUT')
      <div style="flex: 1; min-width: 220px;">
        <label class="field-label">Nome exibido no sistema</label>
        <input type="text" name="nome" required maxlength="255" class="field" value="{{ old('nome', auth()->user()->tenant->nome) }}">
      </div>
      <button type="submit" class="btn-primary">Salvar</button>
    </form>
  </div>

  <div class="card" style="margin-bottom: 24px;">
    <details>
      <summary style="cursor: pointer; font-size: 1rem; font-weight: 700; list-style: none;">+ Novo usuário</summary>
      <form method="POST" action="{{ route('equipe.store') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-top: 16px; align-items: end;">
        @csrf
        <div>
          <label class="field-label">Nome *</label>
          <input type="text" name="name" required class="field">
        </div>
        <div>
          <label class="field-label">E-mail *</label>
          <input type="email" name="email" required class="field">
        </div>
        <div>
          <label class="field-label">Papel</label>
          <select name="role" class="field">
            <option value="usuario">Usuário</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <button type="submit" class="btn-primary">Cadastrar</button>
      </form>
      <p style="margin: 10px 0 0; font-size: 0.78rem; color: var(--ink-faint);">Uma senha temporária é gerada automaticamente e mostrada uma única vez após o cadastro — repasse à pessoa e peça para trocar em Perfil no primeiro login.</p>
    </details>
  </div>

  <div class="card">
    <h2 style="margin: 0 0 14px; font-size: 1rem;">Usuários do escritório ({{ $usuarios->count() }})</h2>

    @forelse ($usuarios as $usuario)
      <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0; {{ !$loop->last ? 'border-bottom: 1px solid var(--border);' : '' }}">
        <div style="flex: 1;">
          <p style="margin: 0; font-weight: 600;">{{ $usuario->name }}</p>
          <p style="margin: 2px 0 0; font-size: 0.82rem; color: var(--ink-faint);">{{ $usuario->email }}</p>
        </div>

        <span class="badge">{{ $usuario->role === 'admin' ? 'Admin' : 'Usuário' }}</span>

        <button type="button" class="icon-btn abrir-usuario" data-id="{{ $usuario->id }}" title="Editar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m16.5 3.5 4 4L8 20H4v-4Z"/></svg>
        </button>

        @if ($usuario->id !== auth()->id())
          <form method="POST" action="{{ route('equipe.resetar-senha', $usuario) }}" onsubmit="return confirm('Redefinir a senha de {{ $usuario->name }}? Uma nova senha temporária será gerada e a atual deixa de funcionar.');">
            @csrf
            <button type="submit" class="icon-btn" title="Redefinir senha">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M16 7l3 3M14 9l2 2"/></svg>
            </button>
          </form>

          <form method="POST" action="{{ route('equipe.destroy', $usuario) }}" onsubmit="return confirm('Remover {{ $usuario->name }}? Os prazos criados por essa pessoa continuam no escritório.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="icon-btn" title="Excluir">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13"/></svg>
            </button>
          </form>
        @endif
      </div>
    @empty
      <p style="color: var(--ink-faint); margin: 0; font-size: 0.9rem;">Nenhum usuário cadastrado.</p>
    @endforelse
  </div>

  <script type="application/json" id="usuarios-data">
    {!! json_encode($usuarios->keyBy('id')->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'role' => $u->role])) !!}
  </script>

  <div class="modal-backdrop" id="modal-usuario">
    <div class="modal">
      <span class="modal-close" data-close="modal-usuario">&times;</span>
      <h2 style="margin: 0 0 16px; font-size: 1.1rem; font-family: 'Fraunces', Georgia, serif;">Editar usuário</h2>
      <form method="POST" id="form-usuario">
        @csrf
        @method('PUT')
        <label class="field-label">Nome</label>
        <input type="text" name="name" id="u_name" required class="field" style="margin-bottom: 12px;">
        <label class="field-label">E-mail</label>
        <input type="email" name="email" id="u_email" required class="field" style="margin-bottom: 12px;">
        <label class="field-label">Papel</label>
        <select name="role" id="u_role" class="field" style="margin-bottom: 18px;">
          <option value="usuario">Usuário</option>
          <option value="admin">Admin</option>
        </select>
        <button type="submit" class="btn-primary" style="width: 100%;">Salvar</button>
      </form>
    </div>
  </div>

  <script>
    (function () {
      var usuariosData = JSON.parse(document.getElementById('usuarios-data').textContent || '{}');

      function abrirModal(id) { document.getElementById(id).classList.add('open'); }
      function fecharModal(id) { document.getElementById(id).classList.remove('open'); }

      document.querySelectorAll('[data-close="modal-usuario"]').forEach(function (el) {
        el.addEventListener('click', function () { fecharModal('modal-usuario'); });
      });
      document.getElementById('modal-usuario').addEventListener('click', function (e) {
        if (e.target === this) fecharModal('modal-usuario');
      });

      document.querySelectorAll('.abrir-usuario').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var u = usuariosData[btn.getAttribute('data-id')];
          if (!u) return;
          document.getElementById('u_name').value = u.name || '';
          document.getElementById('u_email').value = u.email || '';
          document.getElementById('u_role').value = u.role || 'usuario';
          document.getElementById('form-usuario').action = '/equipe/' + u.id;
          abrirModal('modal-usuario');
        });
      });
    })();
  </script>
@endsection
