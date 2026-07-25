<?php
$root = $argv[1] ?? getcwd();
$required = [
    'artisan',
    'config/database.php',
    'database/database.sqlite',
];
foreach ($required as $path) {
    if (! file_exists($root.DIRECTORY_SEPARATOR.$path)) {
        fwrite(STDERR, "Ausente: {$path}\n");
        exit(1);
    }
}
echo "Estrutura base validada.\n";
