<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CONFIGURAÇÃO ===\n";
echo "Driver: ".config('database.default')."\n";
echo "Banco: ".config('database.connections.sqlite.database')."\n\n";

try {

    $usuarios = DB::table('users')
        ->select('id','name','email')
        ->get();

    echo "Usuários encontrados: ".$usuarios->count()."\n\n";

    foreach($usuarios as $u){
        echo "{$u->id} | {$u->name} | {$u->email}\n";
    }

}catch(Throwable $e){

    echo "\nERRO:\n";
    echo $e->getMessage()."\n";

}