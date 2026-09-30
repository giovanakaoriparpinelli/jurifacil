<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('caso_id')->nullable()->constrained('casos')->nullOnDelete();
            $table->foreignId('criado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->date('prazo')->nullable();
            $table->string('prioridade', 10)->default('media');
            $table->string('status', 15)->default('a_fazer');
            $table->timestamp('concluida_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarefas');
    }
};
