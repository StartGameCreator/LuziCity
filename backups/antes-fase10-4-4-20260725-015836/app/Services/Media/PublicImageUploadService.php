<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class PublicImageUploadService
{
    /**
     * @param array<int, UploadedFile> $files
     * @return array<int, string>
     */
    public function storeMany(array $files, string $relativeDirectory): array
    {
        if ($files === []) {
            return [];
        }

        $relativeDirectory = trim(str_replace('\\', '/', $relativeDirectory), '/');

        if ($relativeDirectory === '' || str_contains($relativeDirectory, '..')) {
            throw new RuntimeException('Diretório de upload inválido.');
        }

        $absoluteDirectory = public_path($relativeDirectory);
        File::ensureDirectoryExists($absoluteDirectory);

        $stored = [];

        try {
            foreach ($files as $file) {
                if (! $file instanceof UploadedFile || ! $file->isValid()) {
                    throw new RuntimeException('Arquivo de imagem inválido.');
                }

                $extension = strtolower($file->extension() ?: $file->guessExtension() ?: 'bin');
                $filename = Str::uuid().'.'.$extension;
                $file->move($absoluteDirectory, $filename);
                $stored[] = $relativeDirectory.'/'.$filename;
            }
        } catch (\Throwable $exception) {
            foreach ($stored as $relativePath) {
                File::delete(public_path($relativePath));
            }

            throw $exception;
        }

        return $stored;
    }
}
