<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class FerramentaController extends Controller
{
    public function movimentacoes(): View
    {
        return view('ferramentas.movimentacoes');
    }

    public function intimacoes(): View
    {
        return view('ferramentas.intimacoes');
    }

    public function areaCliente(): View
    {
        return view('ferramentas.cliente');
    }

    public function movimentacoesConsulta(Request $request): JsonResponse
    {
        $numero = preg_replace('/\D+/', '', (string) $request->query('processo', ''));

        if (strlen($numero) !== 20) {
            return response()->json(['erro' => 'Informe um número de processo CNJ com 20 dígitos.'], 422);
        }

        if (! $this->validaCnj($numero)) {
            return response()->json(['erro' => 'O número informado não passou na validação do padrão CNJ.'], 422);
        }

        $alias = $this->tribunalAlias($numero);

        if ($alias === null) {
            return response()->json(['erro' => 'Este segmento de Justiça ainda não possui uma rota pública compatível no DataJud.'], 422);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'APIKey '.config('services.datajud.key'),
            ])
                ->connectTimeout(8)
                ->timeout(30)
                ->post("https://api-publica.datajud.cnj.jus.br/api_publica_{$alias}/_search", [
                    'size' => 20,
                    'query' => ['match' => ['numeroProcesso' => $numero]],
                    '_source' => [
                        'numeroProcesso', 'tribunal', 'grau', 'classe', 'orgaoJulgador',
                        'dataAjuizamento', 'dataHoraUltimaAtualizacao', 'movimentos',
                    ],
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            report($e);

            return response()->json(['erro' => 'O DataJud está indisponível no momento. Tente novamente em alguns minutos.'], 502);
        }

        if ($response->failed()) {
            return response()->json(['erro' => 'O DataJud está indisponível no momento. Tente novamente em alguns minutos.'], 502);
        }

        $decoded = $response->json();

        if (! is_array($decoded) || ! isset($decoded['hits']['hits']) || ! is_array($decoded['hits']['hits'])) {
            return response()->json(['erro' => 'O DataJud retornou uma resposta inesperada.'], 502);
        }

        $processos = array_values(array_filter(array_map(
            static fn (array $hit): ?array => isset($hit['_source']) && is_array($hit['_source']) ? $hit['_source'] : null,
            $decoded['hits']['hits']
        )));

        return response()->json([
            'processo' => $numero,
            'tribunalConsultado' => strtoupper($alias),
            'total' => count($processos),
            'resultados' => $processos,
        ]);
    }

    private function validaCnj(string $numero): bool
    {
        $value = substr($numero, 0, 7).substr($numero, 9).substr($numero, 7, 2);
        $remainder = 0;

        foreach (str_split($value) as $digit) {
            $remainder = ($remainder * 10 + (int) $digit) % 97;
        }

        return $remainder === 1;
    }

    private function tribunalAlias(string $numero): ?string
    {
        $segmento = (int) $numero[13];
        $tribunal = (int) substr($numero, 14, 2);
        $ufs = [
            1 => 'ac', 2 => 'al', 3 => 'ap', 4 => 'am', 5 => 'ba', 6 => 'ce',
            7 => 'dft', 8 => 'es', 9 => 'go', 10 => 'ma', 11 => 'mt', 12 => 'ms',
            13 => 'mg', 14 => 'pa', 15 => 'pb', 16 => 'pr', 17 => 'pe', 18 => 'pi',
            19 => 'rj', 20 => 'rn', 21 => 'rs', 22 => 'ro', 23 => 'rr', 24 => 'sc',
            25 => 'se', 26 => 'sp', 27 => 'to',
        ];

        return match ($segmento) {
            3 => 'stj',
            4 => $tribunal >= 1 && $tribunal <= 6 ? 'trf'.$tribunal : null,
            5 => $tribunal === 0 ? 'tst' : ($tribunal <= 24 ? 'trt'.$tribunal : null),
            6 => $tribunal === 0 ? 'tse' : (isset($ufs[$tribunal]) ? 'tre-'.$ufs[$tribunal] : null),
            7 => 'stm',
            8 => isset($ufs[$tribunal]) ? 'tj'.$ufs[$tribunal] : null,
            9 => match ($tribunal) { 13 => 'tjmmg', 21 => 'tjmrs', 26 => 'tjmsp', default => null },
            default => null,
        };
    }
}
