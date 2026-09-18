<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Entrar — Jurifácil</title>
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
    --ink-faint: #6c7383;
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
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg);
    color: var(--ink);
    font-family: "Manrope", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    padding: 24px;
  }
  .card {
    width: 100%;
    max-width: 380px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 36px 32px;
    backdrop-filter: blur(12px);
  }
  .brand {
    font-family: "Fraunces", Georgia, serif;
    font-weight: 700;
    font-size: 1.5rem;
    margin: 0 0 4px;
    background: var(--gradient);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
  }
  .subtitle {
    color: var(--ink-soft);
    font-size: 0.9rem;
    margin: 0 0 28px;
  }
  label {
    display: block;
    font-size: 0.85rem;
    color: var(--ink-soft);
    margin-bottom: 6px;
  }
  input[type="email"], input[type="password"] {
    width: 100%;
    padding: 11px 14px;
    margin-bottom: 18px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--ink);
    font-size: 0.95rem;
    font-family: inherit;
  }
  input:focus {
    outline: none;
    border-color: var(--accent);
  }
  .remember {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--ink-soft);
    font-size: 0.85rem;
    margin-bottom: 22px;
  }
  .remember input { width: auto; margin: 0; }
  button {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 10px;
    background: var(--gradient);
    color: var(--accent-ink);
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
  }
  button:hover { opacity: 0.92; }
  .errors {
    background: rgba(239, 68, 68, 0.12);
    border: 1px solid rgba(239, 68, 68, 0.35);
    color: #fca5a5;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.85rem;
    margin-bottom: 18px;
  }
  .foot {
    margin-top: 24px;
    text-align: center;
    color: var(--ink-faint);
    font-size: 0.8rem;
  }
</style>
</head>
<body>
  <div class="card">
    <p class="brand">Jurifácil</p>
    <p class="subtitle">Acesse sua conta para continuar.</p>

    @if ($errors->any())
      <div class="errors">
        @foreach ($errors->all() as $error)
          {{ $error }}
        @endforeach
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf
      <label for="email">E-mail</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>

      <label for="password">Senha</label>
      <input type="password" id="password" name="password" required>

      <label class="remember">
        <input type="checkbox" name="remember">
        Lembrar de mim
      </label>

      <button type="submit">Entrar</button>
    </form>

    <p class="foot">Sistema em construção — acesso restrito à equipe.</p>
  </div>
</body>
</html>
