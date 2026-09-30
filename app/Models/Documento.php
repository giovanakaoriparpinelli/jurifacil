<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['nome', 'categoria', 'arquivo_path', 'arquivo_original', 'mime', 'tamanho', 'caso_id', 'enviado_por', 'tenant_id'])]
class Documento extends Model
{
    use BelongsToTenant;

    public const CATEGORIAS = [
        'peticao' => 'Petição',
        'contrato' => 'Contrato',
        'procuracao' => 'Procuração',
        'decisao' => 'Decisão / Sentença',
        'comprovante' => 'Comprovante',
        'outros' => 'Outros',
    ];

    /** Disco privado (fora de public/): só é entregue por rota autenticada e escopada ao escritório. */
    public const DISCO = 'local';

    protected static function booted(): void
    {
        static::deleted(function (Documento $documento) {
            Storage::disk(self::DISCO)->delete($documento->arquivo_path);
        });
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enviado_por');
    }

    public function tamanhoLegivel(): string
    {
        $bytes = $this->tamanho;

        return match (true) {
            $bytes >= 1048576 => number_format($bytes / 1048576, 1, ',', '.').' MB',
            $bytes >= 1024 => number_format($bytes / 1024, 0, ',', '.').' KB',
            default => $bytes.' B',
        };
    }
}
