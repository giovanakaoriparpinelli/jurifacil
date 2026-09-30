<?php

namespace Tests\Feature;

use App\Models\Caso;
use App\Models\Documento;
use App\Models\Tarefa;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TarefasCasosDocumentosTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $nome, bool $super = false): User
    {
        $tenant = Tenant::create(['nome' => $nome, 'status' => 'aprovado']);

        return User::create([
            'tenant_id' => $tenant->id, 'name' => $nome, 'email' => strtolower($nome).'@t.test',
            'password' => 'x', 'role' => 'admin', 'is_super_admin' => $super,
        ]);
    }

    public function test_cria_edita_conclui_e_remove_tarefa(): void
    {
        $u = $this->usuario('Ana');
        $this->actingAs($u)->post(route('casos.store'), ['nome' => 'Caso 1']);
        $caso = Caso::first();

        $this->actingAs($u)->post(route('tarefas.store'), [
            'titulo' => 'Protocolar', 'prazo' => '2026-10-10', 'prioridade' => 'alta', 'status' => 'a_fazer',
            'caso_id' => $caso->id, 'responsavel_id' => $u->id,
        ])->assertSessionHasNoErrors();

        $t = Tarefa::first();
        $this->assertSame($u->id, $t->criado_por);
        $this->assertNull($t->concluida_em);

        $this->actingAs($u)->patch(route('tarefas.concluir', $t))->assertRedirect();
        $this->assertSame('concluida', $t->fresh()->status);
        $this->assertNotNull($t->fresh()->concluida_em);

        $this->actingAs($u)->put(route('tarefas.update', $t), [
            'titulo' => 'Protocolar recurso', 'prioridade' => 'baixa', 'status' => 'em_andamento',
        ])->assertSessionHasNoErrors();
        $t->refresh();
        $this->assertSame('Protocolar recurso', $t->titulo);
        $this->assertSame('em_andamento', $t->status);
        $this->assertNull($t->concluida_em);

        $this->actingAs($u)->get(route('tarefas.index'))->assertOk()->assertSee('Protocolar recurso');
        $this->actingAs($u)->get(route('casos.index'))->assertOk()->assertSee('Caso 1');
        $this->actingAs($u)->get(route('dashboard'))->assertOk()->assertSee('Minhas tarefas em aberto');
        $this->actingAs($u)->delete(route('tarefas.destroy', $t))->assertRedirect();
        $this->assertSame(0, Tarefa::count());
    }

    public function test_filtros_de_tarefa_e_status_invalido(): void
    {
        $u = $this->usuario('Ana');
        $this->actingAs($u);
        foreach ([['A', 'a_fazer', 'alta'], ['B', 'concluida', 'baixa']] as [$titulo, $status, $prio]) {
            Tarefa::create(['titulo' => $titulo, 'status' => $status, 'prioridade' => $prio]);
        }

        $abertas = $this->actingAs($u)->get(route('tarefas.index'))->getContent();
        $this->assertStringContainsString('Tarefas (1)', $abertas);
        $this->assertStringContainsString('Tarefas (2)', $this->actingAs($u)->get(route('tarefas.index', ['status' => 'todas']))->getContent());
        $this->assertStringContainsString('Tarefas (1)', $this->actingAs($u)->get(route('tarefas.index', ['status' => 'todas', 'prioridade' => 'baixa']))->getContent());

        $this->actingAs($u)->post(route('tarefas.store'), ['titulo' => 'x', 'prioridade' => 'urgente', 'status' => 'a_fazer'])
            ->assertSessionHasErrors('prioridade');
    }

    public function test_isolamento_entre_escritorios_em_tarefas_casos_e_documentos(): void
    {
        Storage::fake(Documento::DISCO);
        $a = $this->usuario('Ana');
        $b = $this->usuario('Beto');

        $casoB = Caso::withoutGlobalScopes()->create(['nome' => 'Caso B', 'tenant_id' => $b->tenant_id]);
        $tarefaB = Tarefa::withoutGlobalScopes()->create(['titulo' => 'B', 'tenant_id' => $b->tenant_id]);
        $this->actingAs($b)->post(route('documentos.store'), ['arquivos' => [UploadedFile::fake()->create('b.pdf', 10, 'application/pdf')], 'categoria' => 'outros']);
        $docB = Documento::withoutGlobalScopes()->first();

        // A não pode usar caso de B, nem tocar em tarefa/caso/documento de B.
        $this->actingAs($a)->post(route('tarefas.store'), ['titulo' => 'x', 'prioridade' => 'media', 'status' => 'a_fazer', 'caso_id' => $casoB->id])
            ->assertSessionHasErrors('caso_id');
        $this->actingAs($a)->post(route('tarefas.store'), ['titulo' => 'x', 'prioridade' => 'media', 'status' => 'a_fazer', 'responsavel_id' => $b->id])
            ->assertSessionHasErrors('responsavel_id');
        $this->actingAs($a)->put(route('tarefas.update', $tarefaB), ['titulo' => 'hack', 'prioridade' => 'media', 'status' => 'a_fazer'])->assertNotFound();
        $this->actingAs($a)->delete(route('tarefas.destroy', $tarefaB))->assertNotFound();
        $this->actingAs($a)->put(route('casos.update', $casoB), ['nome' => 'hack'])->assertNotFound();
        $this->actingAs($a)->delete(route('casos.destroy', $casoB))->assertNotFound();
        $this->actingAs($a)->get(route('documentos.download', $docB))->assertNotFound();
        $this->actingAs($a)->delete(route('documentos.destroy', $docB))->assertNotFound();
        $this->actingAs($a)->post(route('documentos.store'), ['arquivos' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')], 'categoria' => 'outros', 'caso_id' => $casoB->id])
            ->assertSessionHasErrors('caso_id');

        $this->assertSame('B', $tarefaB->fresh()->titulo);
        $this->assertSame(1, Documento::withoutGlobalScopes()->count());
        Storage::disk(Documento::DISCO)->assertExists($docB->arquivo_path);
    }

    public function test_upload_download_edicao_e_exclusao_de_documento(): void
    {
        Storage::fake(Documento::DISCO);
        $u = $this->usuario('Ana');
        $this->actingAs($u);
        $caso = Caso::create(['nome' => 'Caso 1']);

        $this->actingAs($u)->post(route('documentos.store'), [
            'arquivos' => [UploadedFile::fake()->create('contrato assinado.pdf', 100, 'application/pdf')],
            'nome' => 'Contrato Silva', 'categoria' => 'contrato', 'caso_id' => $caso->id,
        ])->assertSessionHasNoErrors();

        $doc = Documento::first();
        $this->assertSame('Contrato Silva', $doc->nome);
        $this->assertStringStartsWith('documentos/'.$u->tenant_id.'/', $doc->arquivo_path);
        Storage::disk(Documento::DISCO)->assertExists($doc->arquivo_path);

        $this->actingAs($u)->get(route('documentos.download', $doc))->assertOk()->assertDownload('contrato assinado.pdf');
        $this->actingAs($u)->get(route('documentos.index', ['q' => 'Silva']))->assertSee('Contrato Silva');
        $this->actingAs($u)->get(route('documentos.index', ['q' => 'inexistente']))->assertDontSee('Contrato Silva');

        $this->actingAs($u)->put(route('documentos.update', $doc), ['nome' => 'Contrato final', 'categoria' => 'peticao', 'caso_id' => ''])->assertSessionHasNoErrors();
        $this->assertSame('Contrato final', $doc->fresh()->nome);
        $this->assertNull($doc->fresh()->caso_id);

        $this->actingAs($u)->delete(route('documentos.destroy', $doc))->assertRedirect();
        Storage::disk(Documento::DISCO)->assertMissing($doc->arquivo_path);
        $this->assertSame(0, Documento::count());
    }

    public function test_rejeita_extensoes_perigosas(): void
    {
        Storage::fake(Documento::DISCO);
        $u = $this->usuario('Ana');

        foreach (['shell.php', 'run.exe', 'x.html', 'evil.phtml'] as $nome) {
            $this->actingAs($u)->post(route('documentos.store'), [
                'arquivos' => [UploadedFile::fake()->create($nome, 5)], 'categoria' => 'outros',
            ])->assertSessionHasErrors();
        }

        $this->assertSame(0, Documento::count());
        $this->assertSame([], Storage::disk(Documento::DISCO)->allFiles());
    }

    public function test_excluir_escritorio_apaga_arquivos_e_dados_novos(): void
    {
        Storage::fake(Documento::DISCO);
        $super = $this->usuario('Dono', true);
        $alvo = $this->usuario('Alvo');

        $this->actingAs($alvo)->post(route('casos.store'), ['nome' => 'C']);
        $this->actingAs($alvo)->post(route('tarefas.store'), ['titulo' => 'T', 'prioridade' => 'media', 'status' => 'a_fazer']);
        $this->actingAs($alvo)->post(route('documentos.store'), ['arquivos' => [UploadedFile::fake()->create('d.pdf', 10, 'application/pdf')], 'categoria' => 'outros']);
        $path = Documento::withoutGlobalScopes()->first()->arquivo_path;

        $this->actingAs($super)->delete(route('superadmin.escritorios.excluir', $alvo->tenant))->assertSessionHas('status');

        $this->assertSame(0, Caso::withoutGlobalScopes()->count());
        $this->assertSame(0, Tarefa::withoutGlobalScopes()->count());
        $this->assertSame(0, Documento::withoutGlobalScopes()->count());
        Storage::disk(Documento::DISCO)->assertMissing($path);
    }
}
