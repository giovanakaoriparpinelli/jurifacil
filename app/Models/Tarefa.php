<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['titulo', 'descricao', 'prazo', 'prioridade', 'status', 'concluida_em', 'caso_id', 'criado_por', 'responsavel_id', 'tenant_id'])]
class Tarefa extends Model
{
    use BelongsToTenant;

    public const PRIORIDADES = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta'];

    public const STATUS = ['a_fazer' => 'A fazer', 'em_andamento' => 'Em andamento', 'concluida' => 'Concluída'];

    protected function casts(): array
    {
        return [
            'prazo' => 'date',
            'concluida_em' => 'datetime',
        ];
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class);
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public function isConcluida(): bool
    {
        return $this->status === 'concluida';
    }

    public function isAtrasada(): bool
    {
        return ! $this->isConcluida() && $this->prazo !== null && $this->prazo->isBefore(today());
    }

    /** Muda o status mantendo `concluida_em` coerente. */
    public function definirStatus(string $status): void
    {
        $this->status = $status;
        $this->concluida_em = $status === 'concluida' ? ($this->concluida_em ?? now()) : null;
        $this->save();
    }
}
