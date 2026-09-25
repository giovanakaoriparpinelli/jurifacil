<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Painel') — Jurifácil</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #090b10;
    --surface: rgba(255, 255, 255, 0.045);
    --surface-2: rgba(255, 255, 255, 0.03);
    --border: rgba(255, 255, 255, 0.09);
    --ink: #f3f5f9;
    --ink-soft: #aab1c2;
    --ink-faint: #6c7383;
    --accent: #7c6bff;
    --accent-2: #22d3ee;
    --accent-ink: #0a0c12;
    --danger: #f87171;
    --gradient: linear-gradient(135deg, var(--accent) 0%, var(--accent-2) 100%);
    --sidebar-w: 248px;
    color-scheme: dark;
  }
  * { box-sizing: border-box; }
  body {
    margin: 0;
    min-height: 100vh;
    background: var(--bg);
    color: var(--ink);
    font-family: "Manrope", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  }
  a { color: inherit; text-decoration: none; }
  .shell { display: flex; min-height: 100vh; }

  /* Sidebar */
  .sidebar {
    width: var(--sidebar-w);
    flex-shrink: 0;
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    padding: 22px 16px;
  }
  .brand {
    font-family: "Fraunces", Georgia, serif;
    font-weight: 700;
    font-size: 1.25rem;
    margin: 0 0 28px;
    padding: 0 8px;
    background: var(--gradient);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
  }
  .nav-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--ink-faint);
    padding: 0 10px;
    margin: 18px 0 8px;
  }
  .nav-label:first-of-type { margin-top: 0; }
  .nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 10px;
    border-radius: 10px;
    color: var(--ink-soft);
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 2px;
  }
  .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }
  .nav-item:hover { background: var(--surface); color: var(--ink); }
  .nav-item.active { background: var(--surface); color: var(--ink); box-shadow: inset 2px 0 0 var(--accent); }
  .nav-empty {
    padding: 9px 10px;
    font-size: 0.82rem;
    color: var(--ink-faint);
    font-style: italic;
  }
  .sidebar-foot { margin-top: auto; padding-top: 12px; border-top: 1px solid var(--border); }

  /* Main */
  .main { flex: 1; min-width: 0; }
  .topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 28px;
    border-bottom: 1px solid var(--border);
  }
  .topbar h1 { font-family: "Fraunces", Georgia, serif; font-size: 1.15rem; margin: 0; font-weight: 600; }
  .topbar form button {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--ink-soft);
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-family: inherit;
    font-size: 0.85rem;
  }
  .topbar form button:hover { border-color: var(--accent); color: var(--ink); }
  .content { padding: 28px; width: 100%; }

  .status {
    background: rgba(34, 211, 238, 0.1);
    border: 1px solid rgba(34, 211, 238, 0.3);
    color: var(--accent-2);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.85rem;
    margin-bottom: 20px;
  }
  .errors {
    background: rgba(239, 68, 68, 0.12);
    border: 1px solid rgba(239, 68, 68, 0.35);
    color: #fca5a5;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.85rem;
    margin-bottom: 20px;
  }

  @media (max-width: 820px) {
    .shell { flex-direction: column; }
    .sidebar {
      width: 100%;
      flex-direction: row;
      align-items: center;
      overflow-x: auto;
      padding: 12px 16px;
      border-right: none;
      border-bottom: 1px solid var(--border);
    }
    .brand { margin: 0 12px 0 0; padding: 0; }
    .nav-label, .nav-empty { display: none; }
    .sidebar nav { display: flex; gap: 4px; }
    .sidebar-foot { margin-top: 0; padding-top: 0; border-top: none; margin-left: 4px; }
    .content { padding: 20px; }
  }
</style>
@yield('head')
</head>
<body>
  <div class="shell">
    <aside class="sidebar">
      <p class="brand">Jurifácil</p>

      <nav>
        <p class="nav-label">Geral</p>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9"/></svg>
          Dashboard
        </a>
        <a href="{{ route('prazos.index') }}" class="nav-item {{ request()->routeIs('prazos.*') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>
          Prazos
        </a>

        <p class="nav-label">Ferramentas</p>
        <p class="nav-empty">Em breve</p>
      </nav>

      <div class="sidebar-foot">
        <a href="{{ route('perfil.edit') }}" class="nav-item {{ request()->routeIs('perfil.*') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8.5" r="3.5"/><path d="M4.5 20a7.5 7.5 0 0 1 15 0"/></svg>
          Perfil
        </a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="nav-item" style="width:100%; background:none; border:none; cursor:pointer; font-family:inherit; text-align:left;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 17.5 20 12l-5-5.5"/><path d="M20 12H9"/><path d="M9 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h4"/></svg>
            Sair
          </button>
        </form>
      </div>
    </aside>

    <div class="main">
      <div class="topbar">
        <h1>@yield('title', 'Painel')</h1>
        <span style="color: var(--ink-soft); font-size: 0.85rem;">{{ Auth::user()->name }}</span>
      </div>

      <div class="content">
        @if (session('status'))
          <div class="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
          <div class="errors">
            @foreach ($errors->all() as $error)
              {{ $error }}<br>
            @endforeach
          </div>
        @endif

        @yield('content')
      </div>
    </div>
  </div>
</body>
</html>
