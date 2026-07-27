<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ValidateUploadedFiles
{
    private const DENIED_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
        'cgi', 'pl', 'py', 'sh', 'bat', 'cmd', 'com', 'exe', 'dll',
        'html', 'htm', 'js', 'svg',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        foreach ($request->allFiles() as $key => $files) {
            $this->inspect($files, (string) $key);
        }

        return $next($request);
    }

    private function inspect(UploadedFile|array $files, string $key): void
    {
        foreach (is_array($files) ? $files : [$files] as $index => $file) {
            if (is_array($file)) {
                $this->inspect($file, "{$key}.{$index}");
                continue;
            }
            if (! $file->isValid()) {
                $this->reject($key, 'O upload não foi concluído corretamente.');
            }
            if (in_array(strtolower($file->getClientOriginalExtension()), self::DENIED_EXTENSIONS, true)) {
                $this->reject($key, 'Este tipo de arquivo não é permitido.');
            }
            $mime = strtolower((string) $file->getMimeType());
            if (str_starts_with($mime, 'image/')) {
                $dimensions = @getimagesize($file->getRealPath());
                if (! $dimensions || ($dimensions[0] * $dimensions[1]) > 40_000_000) {
                    $this->reject($key, 'A imagem é inválida ou excede o limite de 40 megapixels.');
                }
            }
        }
    }

    private function reject(string $key, string $message): never
    {
        throw ValidationException::withMessages([$key => $message]);
    }
}
