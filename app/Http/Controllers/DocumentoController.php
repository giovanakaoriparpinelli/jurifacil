<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use App\Models\Documento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentoController extends Controller
{
    /** Extensões aceitas (validadas pelo conteúdo real do arquivo, não só pelo nome). */
    private const EXTENSOES = 'pdf,doc,docx,xls,xlsx,ppt,pptx,odt,ods,txt,rtf,csv,jpg,jpeg,png,zip';

    public function index(Request $request): View
    {
        $filtros = [
            'caso' => $request->get('caso', ''),
            'categoria' => $request->get('categoria', ''),
            'q' => trim((string) $request->get('q', '')),
        ];

        $query = Documento::with(['caso', 'autor']);

        if ($filtros['caso'] === 'sem') {
            $query->whereNull('caso_id');
        } elseif (ctype_digit($filtros['caso'])) {
            $query->where('caso_id', (int) $filtros['caso']);
        }
        if (array_key_exists($filtros['categoria'], Documento::CATEGORIAS)) {
            $query->where('categoria', $filtros['categoria']);
        }
        if ($filtros['q'] !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\%', '\_'], $filtros['q']).'%';
            $query->where(fn ($q) => $q->where('nome', 'like', $termo)->orWhere('arquivo_original', 'like', $termo));
        }

        return view('documentos.index', [
            'documentos' => $query->orderByDesc('created_at')->orderByDesc('id')->get(),
            'filtros' => $filtros,
            'casos' => Caso::orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'arquivos' => ['required', 'array', 'max:20'],
            'arquivos.*' => ['file', 'max:20480', 'extensions:'.self::EXTENSOES, 'mimes:'.self::EXTENSOES],
            'nome' => ['nullable', 'string', 'max:255'],
            'categoria' => ['required', Rule::in(array_keys(Documento::CATEGORIAS))],
            'caso_id' => ['nullable', Rule::exists('casos', 'id')->where('tenant_id', $request->user()->tenant_id)],
        ]);

        $arquivos = $request->file('arquivos');
        $pasta = 'documentos/'.$request->user()->tenant_id;

        foreach ($arquivos as $arquivo) {
            $original = $arquivo->getClientOriginalName();
            $nome = count($arquivos) === 1 && ! empty($data['nome'])
                ? $data['nome']
                : pathinfo($original, PATHINFO_FILENAME);

            Documento::create([
                'nome' => $nome,
                'categoria' => $data['categoria'],
                'caso_id' => $data['caso_id'] ?? null,
                'enviado_por' => $request->user()->id,
                'arquivo_path' => $arquivo->store($pasta, Documento::DISCO),
                'arquivo_original' => $original,
                'mime' => $arquivo->getMimeType(),
                'tamanho' => $arquivo->getSize(),
            ]);
        }

        return back()->with('status', count($arquivos) === 1 ? 'Documento enviado.' : count($arquivos).' documentos enviados.');
    }

    public function update(Request $request, Documento $documento): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'categoria' => ['required', Rule::in(array_keys(Documento::CATEGORIAS))],
            'caso_id' => ['nullable', Rule::exists('casos', 'id')->where('tenant_id', $request->user()->tenant_id)],
        ]);

        $documento->update($data);

        return back()->with('status', 'Documento atualizado.');
    }

    public function download(Documento $documento): StreamedResponse
    {
        abort_unless(Storage::disk(Documento::DISCO)->exists($documento->arquivo_path), 404);

        return Storage::disk(Documento::DISCO)->download($documento->arquivo_path, $documento->arquivo_original);
    }

    public function destroy(Documento $documento): RedirectResponse
    {
        $documento->delete();

        return back()->with('status', 'Documento removido.');
    }
}
