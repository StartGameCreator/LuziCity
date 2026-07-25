<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email','elvis@luzicity.com.br')->first();

if (!$user) {
    echo "Usuário não encontrado\n";
    exit;
}

echo "Email: {$user->email}\n";
echo "Hash: {$user->password}\n";
echo "Ativo: ".($user->is_active ?? 'campo inexistente')."\n";
echo "Email verificado: ".($user->email_verified_at ?? 'não')."\n";