<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['titulo', 'descricao', 'vencimento', 'concluido'])]
class Prazo extends Model
{
    protected function casts(): array
    {
        return [
            'vencimento' => 'date',
            'concluido' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
