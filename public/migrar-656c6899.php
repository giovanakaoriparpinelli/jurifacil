<?php
/**
 * SCRIPT TEMPORARIO DE USO UNICO: roda as migrations pendentes em producao
 * (Laravel Toolkit indisponivel) e se autoexclui ao terminar com sucesso.
 * Exige token (so o hash esta aqui). Remover do repositorio depois de usar.
 */
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

$hash = '0ad0c3832415d1e0f034dfcc8a4e1adb01d7446964faf7db92ca80eabdb54393';
if (! hash_equals($hash, hash('sha256', (string) ($_GET['t'] ?? '')))) {
    http_response_code(404);
    exit('Not found');
}

$raiz = dirname(__DIR__);

// Trava contra a ordem errada de deploy (codigo primeiro, banco depois).
foreach (['database/migrations/2026_09_29_120002_create_documentos_table.php', 'app/Models/Documento.php', 'app/Http/Controllers/DocumentoController.php'] as $arquivo) {
    if (! file_exists($raiz.'/'.$arquivo)) {
        http_response_code(409);
        exit("ABORTADO: codigo ainda nao implantado (falta {$arquivo}). Faca Puxar + Implantar no Plesk e tente de novo. Nada foi alterado.");
    }
}

require $raiz.'/vendor/autoload.php';
$app = require $raiz.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "== migrate:status (antes) ==\n";
$kernel->call('migrate:status');
echo $kernel->output(), "\n";

if (isset($_GET['status'])) {
    exit("(so consulta; nada alterado)\n");
}

echo "== migrate --force ==\n";
try {
    $codigo = $kernel->call('migrate', ['--force' => true]);
    echo $kernel->output(), "\n";
} catch (Throwable $e) {
    http_response_code(500);
    exit('ERRO: '.get_class($e).': '.$e->getMessage()."\n(script mantido no servidor para nova tentativa)");
}

echo "== migrate:status (depois) ==\n";
$kernel->call('migrate:status');
echo $kernel->output(), "\n";

echo "== armazenamento de documentos ==\n";
$dir = $raiz.'/storage/app/private/documentos';
if (! is_dir($dir)) {
    @mkdir($dir, 0775, true);
}
$teste = $dir.'/.teste-escrita';
echo is_dir($dir) && @file_put_contents($teste, 'ok') !== false ? "storage/app/private/documentos: ESCRITA OK\n" : "storage/app/private/documentos: SEM PERMISSAO DE ESCRITA\n";
@unlink($teste);
echo 'upload_max_filesize=', ini_get('upload_max_filesize'), ' post_max_size=', ini_get('post_max_size'), ' max_file_uploads=', ini_get('max_file_uploads'), "\n";

if ($codigo === 0) {
    echo @unlink(__FILE__) ? "\nOK: migrations aplicadas e script autoexcluido.\n" : "\nOK: migrations aplicadas, mas NAO consegui apagar o script - apague public/".basename(__FILE__)." manualmente.\n";
}
