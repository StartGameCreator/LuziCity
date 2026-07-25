<?php
$root = getcwd();
if (! is_file($root.'/artisan')) { fwrite(STDERR, "Raiz Laravel invalida: {$root}\n"); exit(1); }
function updateFile(string $path, callable $callback): void {
    if (! is_file($path)) { throw new RuntimeException("Arquivo obrigatorio ausente: {$path}"); }
    $old = file_get_contents($path);
    $new = $callback($old);
    if (! is_string($new) || $new === '') { throw new RuntimeException("Integracao produziu conteudo invalido: {$path}"); }
    if ($new !== $old) { file_put_contents($path, $new); }
}
try {
    updateFile($root.'/routes/web.php', function (string $s): string {
        $line = "require __DIR__.'/advertisers.php';";
        return str_contains($s, $line) ? $s : rtrim($s)."\n\n{$line}\n";
    });

    updateFile($root.'/app/Http/Controllers/AdminDashboardController.php', function (string $s): string {
        if (str_contains($s, "'admin.advertisers.index'")) return $s;
        $needle = "['label' => 'Central TV Web'";
        if (! str_contains($s, $needle)) throw new RuntimeException('Ponto de integracao do dashboard nao encontrado.');
        $entry = "['label' => 'Gestao de Anunciantes', 'description' => 'CRM comercial, contatos, contratos, documentos e historico.', 'route' => 'admin.advertisers.index', 'icon' => 'user'],\n                ";
        return str_replace($needle, $entry.$needle, $s);
    });

    updateFile($root.'/resources/views/layouts/app.blade.php', function (string $s): string {
        if (str_contains($s, "route('admin.advertisers.index')")) return $s;
        $needle = '<a href="{{ route(\'admin.system-health.index\') }}"';
        if (! str_contains($s, $needle)) throw new RuntimeException('Ponto de integracao do menu nao encontrado.');
        $entry = "<a href=\"{{ route('admin.advertisers.index') }}\"><x-app-icon name=\"user\" /> Anunciantes</a>\n                    ";
        return str_replace($needle, $entry.$needle, $s);
    });

    echo "Integracoes aplicadas com sucesso.\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage()."\n");
    exit(1);
}
