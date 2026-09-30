<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Caso/cliente/pasta: agrupa tarefas e documentos do escritório. */
#[Fillable(['nome', 'cliente', 'numero_processo', 'descricao', 'tenant_id'])]
class Caso extends Model
{
    use BelongsToTenant;

    public function tarefas(): HasMany
    {
        return $this->hasMany(Tarefa::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }
}
