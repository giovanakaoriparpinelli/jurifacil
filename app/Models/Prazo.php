<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['titulo', 'descricao', 'vencimento', 'concluido', 'user_id', 'tenant_id'])]
class Prazo extends Model
{
    use BelongsToTenant;

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
