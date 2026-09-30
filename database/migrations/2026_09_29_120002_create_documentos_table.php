<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('caso_id')->nullable()->constrained('casos')->nullOnDelete();
            $table->foreignId('enviado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nome');
            $table->string('categoria', 20)->default('outros');
            $table->string('arquivo_path');
            $table->string('arquivo_original');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('tamanho')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
