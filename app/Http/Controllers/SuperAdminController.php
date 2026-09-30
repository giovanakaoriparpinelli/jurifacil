<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Prazo;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
    /**
     * Redefine a senha do admin de um escritório (ex.: ficou sem acesso e não
     * há outro admin no escritório para fazer isso pela tela de Equipe).
     */
    public function resetarSenhaAdmin(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($request->user()->is_super_admin, 403);

        $admin = $tenant->usuarios()->where('role', 'admin')->orderBy('id')->first();

        if (! $admin) {
            return back()->withErrors(['escritorio' => "O escritório \"{$tenant->nome}\" não tem um admin."]);
        }

        if ($admin->id === $request->user()->id) {
            return back()->withErrors(['escritorio' => 'Para trocar a sua própria senha, use Perfil.']);
        }

        $senhaTemporaria = Str::password(10, symbols: false);

        $admin->forceFill([
            'password' => Hash::make($senhaTemporaria),
            'remember_token' => null,
        ])->save();

        return back()->with('status', "Senha de {$admin->email} ({$tenant->nome}) redefinida. Senha temporária: {$senhaTemporaria} — repasse e peça para trocar em Perfil no primeiro login.");
    }

    /**
     * Exclui o escritório e TUDO que pertence a ele (usuários e prazos). Não
     * há desfazer. Nunca permite excluir o escritório do próprio super admin.
     */
    public function excluir(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($request->user()->is_super_admin, 403);

        if ($tenant->id === $request->user()->tenant_id || $tenant->usuarios()->where('is_super_admin', true)->exists()) {
            return back()->withErrors(['escritorio' => 'Este escritório contém a conta de super admin e não pode ser excluído.']);
        }

        $nome = $tenant->nome;

        DB::transaction(function () use ($tenant) {
            // withoutGlobalScopes: o escopo de tenant filtraria pelo escritório de quem está logado.
            Prazo::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
            User::where('tenant_id', $tenant->id)->delete();
            $tenant->delete();
        });

        // As linhas de documentos somem por cascata no banco (sem eventos de model), então os arquivos são apagados aqui.
        Storage::disk(Documento::DISCO)->deleteDirectory('documentos/'.$tenant->id);

        return back()->with('status', "Escritório \"{$nome}\" excluído.");
    }
}
