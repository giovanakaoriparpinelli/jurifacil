<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'tenant_id', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Propositalmente SEM o trait BelongsToTenant aqui: aplicar o escopo global
     * (que depende de auth()->user()) neste model causaria recursão infinita —
     * resolver o usuário logado já é, em si, uma consulta a User. O isolamento
     * por tenant para listagens de usuários é feito manualmente (ver
     * TeamController), só o tenant_id em si fica protegido pela FK.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasMany<Prazo>
     */
    public function prazos(): HasMany
    {
        return $this->hasMany(Prazo::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
