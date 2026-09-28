<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('equipe.index', [
            'usuarios' => User::where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:admin,usuario'],
        ]);

        $senhaTemporaria = Str::password(10, symbols: false);

        User::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($senhaTemporaria),
        ]);

        return back()->with('status', "Usuário cadastrado. Senha temporária: {$senhaTemporaria} — repasse e peça para trocar em Perfil no primeiro login.");
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($usuario->tenant_id === $request->user()->tenant_id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($usuario->id)],
            'role' => ['required', 'in:admin,usuario'],
        ]);

        $usuario->update($data);

        return back()->with('status', 'Usuário atualizado.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($usuario->tenant_id === $request->user()->tenant_id, 404);

        if ($usuario->id === $request->user()->id) {
            return back()->withErrors(['usuario' => 'Você não pode remover sua própria conta.']);
        }

        $usuario->delete();

        return back()->with('status', 'Usuário removido.');
    }
}
