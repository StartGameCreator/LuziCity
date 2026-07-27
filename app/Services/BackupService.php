<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupService
{
    public function create(): array
    {
        $temporaryDirectory = storage_path('app/backup-tmp-'.bin2hex(random_bytes(8)));
        File::ensureDirectoryExists($temporaryDirectory);

        try {
            $databaseFile = $this->dumpDatabase($temporaryDirectory);
            $archiveName = 'luzicity-'.now()->format('Y-m-d_H-i-s').'-'.bin2hex(random_bytes(3)).'.zip';
            $archivePath = $temporaryDirectory.DIRECTORY_SEPARATOR.$archiveName;
            $manifest = [
                'created_at' => now()->toIso8601String(),
                'app' => config('app.name'),
                'database_driver' => DB::getDriverName(),
                'database_file' => basename($databaseFile),
                'files' => [],
            ];

            $zip = new ZipArchive;
            throw_unless($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, RuntimeException::class, 'Nao foi possivel criar o arquivo ZIP.');

            $this->addFile($zip, $databaseFile, 'database/'.basename($databaseFile), $manifest);

            if (config('backup.include_storage')) {
                $storageRoot = storage_path('app');
                foreach (File::allFiles($storageRoot) as $file) {
                    $path = $file->getRealPath();
                    if (! $path || str_starts_with($path, $temporaryDirectory) || str_contains(str_replace('\\', '/', $path), '/app/backups/')) {
                        continue;
                    }

                    $relative = str_replace('\\', '/', $file->getRelativePathname());
                    $this->addFile($zip, $path, 'storage/'.$relative, $manifest);
                }
            }

            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            $zip->close();

            $destination = config('backup.path').'/'.$archiveName;
            $stream = fopen($archivePath, 'rb');
            throw_unless(is_resource($stream) && Storage::disk(config('backup.disk'))->put($destination, $stream), RuntimeException::class, 'Nao foi possivel salvar o backup no disco configurado.');
            if (is_resource($stream)) {
                fclose($stream);
            }

            return [
                'path' => $destination,
                'disk' => config('backup.disk'),
                'bytes' => File::size($archivePath),
                'files' => count($manifest['files']),
            ];
        } finally {
            File::deleteDirectory($temporaryDirectory);
        }
    }

    public function verify(string $path): array
    {
        $disk = Storage::disk(config('backup.disk'));
        throw_unless($disk->exists($path), RuntimeException::class, "Backup nao encontrado: {$path}");

        $temporaryFile = tempnam(sys_get_temp_dir(), 'luzicity-verify-');
        throw_unless($temporaryFile !== false, RuntimeException::class, 'Nao foi possivel criar arquivo temporario.');

        try {
            file_put_contents($temporaryFile, $disk->get($path));
            $zip = new ZipArchive;
            throw_unless($zip->open($temporaryFile, ZipArchive::CHECKCONS) === true, RuntimeException::class, 'Backup ZIP invalido ou corrompido.');

            $manifestRaw = $zip->getFromName('manifest.json');
            throw_unless(is_string($manifestRaw), RuntimeException::class, 'Manifesto ausente no backup.');
            $manifest = json_decode($manifestRaw, true, flags: JSON_THROW_ON_ERROR);

            foreach ($manifest['files'] ?? [] as $entry => $expectedHash) {
                $contents = $zip->getFromName($entry);
                throw_unless(is_string($contents) && hash('sha256', $contents) === $expectedHash, RuntimeException::class, "Arquivo corrompido: {$entry}");
            }

            $databaseEntry = 'database/'.($manifest['database_file'] ?? '');
            $databaseContents = $zip->getFromName($databaseEntry);
            throw_unless(is_string($databaseContents) && $databaseContents !== '', RuntimeException::class, 'Dump do banco ausente ou vazio.');

            if (($manifest['database_driver'] ?? null) === 'sqlite') {
                $restoreFile = $temporaryFile.'.sqlite';
                file_put_contents($restoreFile, $databaseContents);
                try {
                    $pdo = new \PDO('sqlite:'.$restoreFile);
                    throw_unless($pdo->query('PRAGMA integrity_check')->fetchColumn() === 'ok', RuntimeException::class, 'A restauracao SQLite falhou na verificacao de integridade.');
                } finally {
                    File::delete($restoreFile);
                }
            }

            $zip->close();

            return ['valid' => true, 'files' => count($manifest['files'] ?? []), 'created_at' => $manifest['created_at'] ?? null];
        } finally {
            File::delete($temporaryFile);
        }
    }

    public function prune(?int $days = null): int
    {
        $days ??= (int) config('backup.retention_days');
        $cutoff = now()->subDays(max(1, $days))->timestamp;
        $disk = Storage::disk(config('backup.disk'));
        $removed = 0;

        foreach ($disk->files(config('backup.path')) as $file) {
            if (str_ends_with($file, '.zip') && $disk->lastModified($file) < $cutoff) {
                $removed += $disk->delete($file) ? 1 : 0;
            }
        }

        return $removed;
    }

    private function dumpDatabase(string $directory): string
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $config = $connection->getConfig();

        if ($driver === 'sqlite') {
            $source = $config['database'];
            $target = $directory.DIRECTORY_SEPARATOR.'database.sqlite';

            if ($source === ':memory:') {
                $quotedTarget = str_replace("'", "''", $target);
                DB::statement("VACUUM INTO '{$quotedTarget}'");
            } else {
                throw_unless(is_string($source) && File::exists($source), RuntimeException::class, 'Arquivo do banco SQLite nao encontrado.');
                DB::statement('PRAGMA wal_checkpoint(FULL)');
                throw_unless(File::copy($source, $target), RuntimeException::class, 'Falha ao copiar o banco SQLite.');
            }

            return $target;
        }

        $target = $directory.DIRECTORY_SEPARATOR.'database.sql';
        $command = $driver === 'pgsql'
            ? ['pg_dump', '--no-owner', '--no-privileges', '--host='.$config['host'], '--port='.(string) $config['port'], '--username='.$config['username'], '--file='.$target, $config['database']]
            : ['mysqldump', '--single-transaction', '--quick', '--host='.$config['host'], '--port='.(string) $config['port'], '--user='.$config['username'], '--result-file='.$target, $config['database']];
        $environment = $driver === 'pgsql'
            ? ['PGPASSWORD' => (string) $config['password']]
            : ['MYSQL_PWD' => (string) $config['password']];
        $process = new Process($command, null, $environment, null, 600);
        $process->mustRun();
        throw_unless(File::exists($target) && File::size($target) > 0, RuntimeException::class, 'O dump do banco ficou vazio.');

        return $target;
    }

    private function addFile(ZipArchive $zip, string $source, string $entry, array &$manifest): void
    {
        throw_unless($zip->addFile($source, $entry), RuntimeException::class, "Falha ao incluir {$entry}.");
        $manifest['files'][$entry] = hash_file('sha256', $source);
    }
}
