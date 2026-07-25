<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$email = 'elvis@luzicity.com.br';
$name = 'Elvis';
$password = 'Start@Game357';
$roleName = 'Master';
$guardName = config('auth.defaults.guard', 'web');

echo PHP_EOL;
echo "==============================================" . PHP_EOL;
echo " LUZICITY - RECUPERACAO DO USUARIO MASTER" . PHP_EOL;
echo "==============================================" . PHP_EOL;
echo "Banco: " . (string) config('database.connections.' . config('database.default') . '.database') . PHP_EOL;
echo "Usuario: {$email}" . PHP_EOL;
echo PHP_EOL;

try {
    DB::beginTransaction();

    if (!Schema::hasTable('users')) {
        throw new RuntimeException('A tabela users nao existe.');
    }

    if (!Schema::hasTable('roles') || !Schema::hasTable('model_has_roles')) {
        throw new RuntimeException(
            'As tabelas do Spatie Permission nao foram encontradas. Execute as migrations antes.'
        );
    }

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    /*
     * Cria ou atualiza o papel Master.
     */
    $masterRole = Role::query()->firstOrCreate([
        'name' => $roleName,
        'guard_name' => $guardName,
    ]);

    /*
     * O papel Master recebe todas as permissoes atualmente existentes.
     * Se ainda nao houver permissoes cadastradas, o papel sera criado mesmo assim.
     */
    if (Schema::hasTable('permissions')) {
        $permissions = Permission::query()
            ->where('guard_name', $guardName)
            ->get();

        if ($permissions->isNotEmpty()) {
            $masterRole->syncPermissions($permissions);
        }
    }

    /*
     * Remove o usuario anterior e seus vinculos do Spatie.
     * O delete pelo Eloquent permite que eventos e casts do modelo sejam respeitados.
     */
    $existingUser = User::query()->where('email', $email)->first();

    if ($existingUser) {
        if (method_exists($existingUser, 'syncRoles')) {
            $existingUser->syncRoles([]);
        }

        $existingUser->delete();
        echo "[OK] Usuario anterior removido." . PHP_EOL;
    } else {
        echo "[INFO] Nenhum usuario anterior encontrado." . PHP_EOL;
    }

    /*
     * Recria o usuario.
     */
    $attributes = [
        'name' => $name,
        'email' => $email,
        'password' => Hash::make($password),
    ];

    if (Schema::hasColumn('users', 'is_active')) {
        $attributes['is_active'] = true;
    }

    if (Schema::hasColumn('users', 'email_verified_at')) {
        $attributes['email_verified_at'] = now();
    }

    if (Schema::hasColumn('users', 'last_login_at')) {
        $attributes['last_login_at'] = null;
    }

    $user = new User();
    $user->forceFill($attributes);
    $user->save();

    $user->assignRole($masterRole);

    DB::commit();

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    echo "[OK] Papel Master criado ou atualizado." . PHP_EOL;
    echo "[OK] Todas as permissoes existentes foram vinculadas ao Master." . PHP_EOL;
    echo "[OK] Usuario Elvis recriado e ativado." . PHP_EOL;
    echo "[OK] Papel Master atribuido ao usuario." . PHP_EOL;
    echo PHP_EOL;
    echo "CREDENCIAIS:" . PHP_EOL;
    echo "E-mail: {$email}" . PHP_EOL;
    echo "Senha : {$password}" . PHP_EOL;
    echo PHP_EOL;
    echo "RECUPERACAO CONCLUIDA COM SUCESSO." . PHP_EOL;
    exit(0);
} catch (Throwable $e) {
    if (DB::transactionLevel() > 0) {
        DB::rollBack();
    }

    fwrite(STDERR, PHP_EOL . "[ERRO] " . $e->getMessage() . PHP_EOL);
    fwrite(STDERR, "Arquivo: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL);
    exit(1);
}
