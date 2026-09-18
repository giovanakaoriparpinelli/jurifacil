<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Painel — Jurifácil</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #090b10;
    --surface: rgba(255, 255, 255, 0.045);
    --border: rgba(255, 255, 255, 0.09);
    --ink: #f3f5f9;
    --ink-soft: #aab1c2;
    --accent: #7c6bff;
    --accent-2: #22d3ee;
    --accent-ink: #0a0c12;
    --gradient: linear-gradient(135deg, var(--accent) 0%, var(--accent-2) 100%);
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
  header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 28px;
    border-bottom: 1px solid var(--border);
  }
  .brand {
    font-family: "Fraunces", Georgia, serif;
    font-weight: 700;
    font-size: 1.2rem;
    background: var(--gradient);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
  }
  form button {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--ink-soft);
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-family: inherit;
    font-size: 0.85rem;
  }
  form button:hover { border-color: var(--accent); color: var(--ink); }
  main {
    max-width: 720px;
    margin: 0 auto;
    padding: 60px 24px;
    text-align: center;
  }
  h1 { font-family: "Fraunces", Georgia, serif; font-size: 1.8rem; margin-bottom: 8px; }
  p { color: var(--ink-soft); }
</style>
</head>
<body>
  <header>
    <span class="brand">Jurifácil</span>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit">Sair</button>
    </form>
  </header>
  <main>
    <h1>Bem-vindo(a), {{ Auth::user()->name }}</h1>
    <p>O painel de tarefas, documentos e minutas será construído aqui nas próximas etapas (Fase 1 do roadmap).</p>
  </main>
</body>
</html>
