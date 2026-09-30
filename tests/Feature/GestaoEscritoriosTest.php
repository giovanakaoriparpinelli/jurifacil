<?php

namespace Tests\Feature;

use App\Models\Prazo;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GestaoEscritoriosTest extends TestCase
{
    use RefreshDatabase;

    private function escritorio(string $nome, string $role = 'admin', bool $super = false): User
    {
        $tenant = Tenant::create(['nome' => $nome, 'status' => 'aprovado']);

        return User::create([
            'tenant_id' => $tenant->id, 'name' => $nome, 'email' => strtolower(str_replace(' ', '', $nome)).'@t.test',
            'password' => 'senha-antiga', 'role' => $role, 'is_super_admin' => $super,
        ]);
    }

    public function test_super_admin_exclui_escritorio_com_usuarios_e_prazos(): void
    {
        $super = $this->escritorio('Dono', 'admin', true);
        $alvo = $this->escritorio('Alvo');
        $outro = $this->escritorio('Outro');
        foreach ([$alvo, $outro] as $u) {
            Prazo::withoutGlobalScopes()->create(['user_id' => $u->id, 'tenant_id' => $u->tenant_id, 'titulo' => 'x', 'vencimento' => now()]);
        }

        $this->actingAs($super)->delete(route('superadmin.escritorios.excluir', $alvo->tenant))->assertSessionHas('status');

        $this->assertDatabaseMissing('tenants', ['id' => $alvo->tenant_id]);
        $this->assertDatabaseMissing('users', ['id' => $alvo->id]);
        $this->assertSame(0, Prazo::withoutGlobalScopes()->where('tenant_id', $alvo->tenant_id)->count());
        $this->assertDatabaseHas('tenants', ['id' => $outro->tenant_id]);
        $this->assertSame(1, Prazo::withoutGlobalScopes()->where('tenant_id', $outro->tenant_id)->count());
    }

    public function test_nao_exclui_escritorio_do_super_admin_nem_sem_permissao(): void
    {
        $super = $this->escritorio('Dono', 'admin', true);
        $comum = $this->escritorio('Comum');

        $this->actingAs($super)->delete(route('superadmin.escritorios.excluir', $super->tenant))->assertSessionHasErrors('escritorio');
        $this->assertDatabaseHas('tenants', ['id' => $super->tenant_id]);

        $this->actingAs($comum)->delete(route('superadmin.escritorios.excluir', $super->tenant))->assertForbidden();
        $this->assertDatabaseHas('tenants', ['id' => $super->tenant_id]);
    }

    public function test_admin_redefine_senha_de_colega_mas_nao_de_outro_escritorio(): void
    {
        $admin = $this->escritorio('Banca A');
        $colega = User::create(['tenant_id' => $admin->tenant_id, 'name' => 'Colega', 'email' => 'c@t.test', 'password' => 'senha-antiga', 'role' => 'usuario']);
        $estranho = $this->escritorio('Banca B');

        $this->actingAs($admin)->post(route('equipe.resetar-senha', $colega))->assertSessionHas('status');
        $this->assertFalse(Hash::check('senha-antiga', $colega->fresh()->password));

        $this->actingAs($admin)->post(route('equipe.resetar-senha', $estranho))->assertNotFound();
        $this->assertTrue(Hash::check('senha-antiga', $estranho->fresh()->password));

        $this->actingAs($admin)->post(route('equipe.resetar-senha', $admin))->assertSessionHasErrors('usuario');
        $this->actingAs($colega)->post(route('equipe.resetar-senha', $admin))->assertForbidden();
    }

    public function test_super_admin_redefine_senha_do_admin_de_um_escritorio(): void
    {
        $super = $this->escritorio('Dono', 'admin', true);
        $admin = $this->escritorio('Banca A');

        $this->actingAs($super)->post(route('superadmin.escritorios.resetar-senha', $admin->tenant))->assertSessionHas('status');
        $this->assertFalse(Hash::check('senha-antiga', $admin->fresh()->password));

        $this->actingAs($admin)->post(route('superadmin.escritorios.resetar-senha', $super->tenant))->assertForbidden();
    }

    public function test_admin_edita_nome_do_proprio_escritorio_e_usuario_comum_nao(): void
    {
        $admin = $this->escritorio('Nome Velho');
        $comum = User::create(['tenant_id' => $admin->tenant_id, 'name' => 'C', 'email' => 'c@t.test', 'password' => 'x', 'role' => 'usuario']);

        $this->actingAs($comum)->put(route('escritorio.update'), ['nome' => 'Hack'])->assertForbidden();
        $this->actingAs($admin)->put(route('escritorio.update'), ['nome' => 'Nome Novo'])->assertSessionHas('status');

        $this->assertSame('Nome Novo', $admin->tenant->fresh()->nome);
        $this->actingAs($admin)->get(route('equipe.index'))->assertOk()->assertSee('Nome Novo');
    }
}
