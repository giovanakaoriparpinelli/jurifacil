<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aplica isolamento entre escritórios (tenants) de forma automática:
 * toda consulta a um model com esta trait já vem filtrada pelo tenant do
 * usuário logado, e todo registro criado já nasce com o tenant_id certo —
 * sem depender de lembrar de escopar manualmente em cada controller
 * (ver PLANO_DE_NEGOCIO.md, seção 7: isolamento entre tenants é o risco
 * mais grave do sistema).
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', auth()->user()->tenant_id);
            }
        });

        static::creating(function ($model) {
            if (empty($model->tenant_id) && auth()->check()) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
