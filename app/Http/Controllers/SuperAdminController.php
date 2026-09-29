<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Painel restrito ao super admin (dono da plataforma, hoje só a conta do
 * Mauro) para aprovar ou rejeitar novos escritórios cadastrados via /registro
 * antes que consigam logar no sistema.
 */
class SuperAdminController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->is_super_admin, 403);

        return view('superadmin.escritorios', [
            'tenants' => Tenant::with('usuarios')->orderByRaw("status = 'pendente' desc")->orderByDesc('created_at')->get(),
        ]);
    }

    public function aprovar(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($request->user()->is_super_admin, 403);

        $tenant->update(['status' => 'aprovado']);

        return back()->with('status', "Escritório \"{$tenant->nome}\" aprovado.");
    }

    public function rejeitar(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($request->user()->is_super_admin, 403);

        $tenant->update(['status' => 'rejeitado']);

        return back()->with('status', "Escritório \"{$tenant->nome}\" rejeitado.");
    }
}
