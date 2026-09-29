<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'E-mail ou senha incorretos.'])
                ->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->tenant->isAprovado()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $mensagem = $user->tenant->status === 'rejeitado'
                ? 'O cadastro do seu escritório não foi aprovado. Entre em contato com o suporte.'
                : 'Seu escritório ainda está aguardando aprovação. Você poderá entrar assim que liberarmos o acesso.';

            return back()->withErrors(['email' => $mensagem])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
