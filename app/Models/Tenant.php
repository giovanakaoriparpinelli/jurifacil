<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nome', 'status'])]
class Tenant extends Model
{
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function prazos(): HasMany
    {
        return $this->hasMany(Prazo::class);
    }

    public function isAprovado(): bool
    {
        return $this->status === 'aprovado';
    }
}
