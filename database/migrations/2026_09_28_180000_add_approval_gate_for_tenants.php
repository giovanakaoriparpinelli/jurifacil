<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona um portão de aprovação para novos escritórios: quem se cadastra
 * pelo /registro nasce com status "pendente" e não consegue logar até um
 * super admin (o dono da plataforma, não um admin de escritório comum)
 * aprovar. Escritórios que já existiam antes desta trava (inclusive os
 * criados durante o teste de hoje) são aprovados automaticamente, para
 * ninguém perder acesso que já tinha.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('status')->default('pendente')->after('nome');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super_admin')->default(false)->after('role');
        });

        DB::table('tenants')->update(['status' => 'aprovado']);

        DB::table('users')
            ->where('tenant_id', 1)
            ->where('role', 'admin')
            ->update(['is_super_admin' => true]);
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_super_admin');
        });
    }
};
