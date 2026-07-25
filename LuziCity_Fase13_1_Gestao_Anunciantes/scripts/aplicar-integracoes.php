<?php
$root = getcwd();
if (! is_file($root.'/artisan')) { fwrite(STDERR, "Raiz Laravel inválida: {$root}\n"); exit(1); }
function patch(string $path, callable $fn): void { $old=file_get_contents($path); $new=$fn($old); if($new!==$old) file_put_contents($path,$new); }
patch($root.'/routes/web.php', function($s){$line="require __DIR__.'/advertisers.php';"; return str_contains($s,$line)?$s:rtrim($s)."\n\n{$line}\n";});
patch($root.'/app/Http/Controllers/AdminDashboardController.php', function($s){$needle="['label' => 'Central TV Web'";$entry="['label' => 'Gestão de Anunciantes', 'description' => 'CRM comercial, contatos, contratos, documentos e histórico.', 'route' => 'admin.advertisers.index', 'icon' => 'user'],\n                ";return str_contains($s,"'admin.advertisers.index'")?$s:str_replace($needle,$entry.$needle,$s);});
patch($root.'/resources/views/layouts/app.blade.php', function($s){$needle='<a href="{{ route(\'admin.system-health.index\') }}"';$entry="<a href=\"{{ route('admin.advertisers.index') }}\"><x-app-icon name=\"user\" /> Anunciantes</a>\n                    ";return str_contains($s,"route('admin.advertisers.index')")?$s:str_replace($needle,$entry.$needle,$s);});
echo "Integrações aplicadas.\n";
