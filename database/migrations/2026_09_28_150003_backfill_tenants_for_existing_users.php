<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Antes do multi-tenant, cada usuário era, na prática, o próprio "escritório".
 * Esta migration preserva esse comportamento: cria um Tenant para cada usuário
 * que ainda não tem um (tenant_id nulo), promove esse usuário a admin do seu
 * próprio tenant, e migra os prazos dele junto — sem apagar nenhum dado.
 * Segura para rodar tanto local (poucos registros de teste) quanto em produção
 * (conta real do Mauro + seus prazos).
 */
return new class extends Migration
{
    public function up(): void
    {
        $agora = now();

        DB::table('users')->whereNull('tenant_id')->orderBy('id')->get()->each(function ($usuario) use ($agora) {
            $nomeTenant = trim($usuario->name) !== '' ? $usuario->name : 'Escritório';

            $tenantId = DB::table('tenants')->insertGetId([
                'nome' => $nomeTenant,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);

            DB::table('users')->where('id', $usuario->id)->update([
                'tenant_id' => $tenantId,
                'role' => 'admin',
            ]);

            DB::table('prazos')->where('user_id', $usuario->id)->update([
                'tenant_id' => $tenantId,
            ]);
        });
    }

    public function down(): void
    {
        // Backfill de dados não é revertido — reverter descartaria a associação
        // real entre usuários/prazos existentes e seus tenants.
    }
};
