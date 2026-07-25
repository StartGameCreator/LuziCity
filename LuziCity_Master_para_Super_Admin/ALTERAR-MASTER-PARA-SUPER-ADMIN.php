<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$email = 'elvis@luzicity.com.br';
$oldRoleName = 'Master';
$newRoleName = 'Super Admin';
$guardName = config('auth.defaults.guard', 'web');

echo PHP_EOL;
echo "==================================================" . PHP_EOL;
echo " LUZICITY - MASTER PARA SUPER ADMIN" . PHP_EOL;
echo "==================================================" . PHP_EOL;
echo "Usuario: {$email}" . PHP_EOL;
echo PHP_EOL;

try {
    if (!Schema::hasTable('roles') || !Schema::hasTable('model_has_roles')) {
        throw new RuntimeException(
            'As tabelas do Spatie Permission nao foram encontradas.'
        );
    }

    DB::beginTransaction();

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $oldRole = Role::query()
        ->where('name', $oldRoleName)
        ->where('guard_name', $guardName)
        ->first();

    $newRole = Role::query()
        ->where('name', $newRoleName)
        ->where('guard_name', $guardName)
        ->first();

    /*
     * Caso ainda nao exista Super Admin:
     * - renomeia Master para preservar o mesmo ID e todos os vinculos;
     * - ou cria Super Admin se Master nao existir.
     */
    if (!$newRole && $oldRole) {
        $oldRole->forceFill(['name' => $newRoleName])->save();
        $newRole = $oldRole->fresh();

        echo "[OK] Papel Master renomeado para Super Admin." . PHP_EOL;
    } elseif (!$newRole) {
        $newRole = Role::query()->create([
            'name' => $newRoleName,
            'guard_name' => $guardName,
        ]);

        echo "[OK] Papel Super Admin criado." . PHP_EOL;
    } else {
        echo "[INFO] O papel Super Admin ja existia." . PHP_EOL;
    }

    /*
     * Se Master e Super Admin existirem simultaneamente,
     * une permissoes e transfere usuarios antes de remover Master.
     */
    $oldRole = Role::query()
        ->where('name', $oldRoleName)
        ->where('guard_name', $guardName)
        ->first();

    if ($oldRole && $oldRole->id !== $newRole->id) {
        $mergedPermissions = $newRole->permissions
            ->merge($oldRole->permissions)
            ->unique('id');

        if ($mergedPermissions->isNotEmpty()) {
            $newRole->syncPermissions($mergedPermissions);
        }

        $usersWithOldRole = User::role($oldRoleName, $guardName)->get();

        foreach ($usersWithOldRole as $roleUser) {
            $roleUser->removeRole($oldRole);
            $roleUser->assignRole($newRole);
        }

        $oldRole->delete();

        echo "[OK] Usuarios e permissoes de Master migrados para Super Admin." . PHP_EOL;
        echo "[OK] Papel Master removido." . PHP_EOL;
    }

    /*
     * Garante que Elvis fique ativo e como Super Admin.
     */
    $user = User::query()->where('email', $email)->first();

    if (!$user) {
        throw new RuntimeException(
            "O usuario {$email} nao foi encontrado. Execute primeiro o recuperador de usuario."
        );
    }

    if (Schema::hasColumn('users', 'is_active')) {
        $user->forceFill(['is_active' => true])->save();
    }

    $user->syncRoles([$newRole]);

    DB::commit();

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    echo "[OK] Elvis recebeu exclusivamente o papel Super Admin." . PHP_EOL;
    echo PHP_EOL;
    echo "RESULTADO:" . PHP_EOL;
    echo "E-mail: {$email}" . PHP_EOL;
    echo "Papel : {$newRoleName}" . PHP_EOL;
    echo PHP_EOL;
    echo "PROCESSO CONCLUIDO COM SUCESSO." . PHP_EOL;

    exit(0);
} catch (Throwable $e) {
    if (DB::transactionLevel() > 0) {
        DB::rollBack();
    }

    fwrite(STDERR, PHP_EOL . "[ERRO] " . $e->getMessage() . PHP_EOL);
    fwrite(STDERR, "Arquivo: " . $e->getFile() . ':' . $e->getLine() . PHP_EOL);

    exit(1);
}
