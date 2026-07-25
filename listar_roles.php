<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;

echo "ROLES ENCONTRADAS:\n";
echo "------------------\n";

foreach (Role::all() as $role) {
    echo $role->id . " - " . $role->name . PHP_EOL;
}