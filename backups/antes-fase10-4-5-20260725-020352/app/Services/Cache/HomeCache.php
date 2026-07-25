<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Throwable;

class HomeCache
{
    private const VERSION_KEY = 'luzicity:home-cache-version';

    public static function key(string $name): string
    {
        return "luzicity:home:v".self::version().":".$name;
    }

    public static function flush(): int
    {
        $version = self::version() + 1;

        try {
            Cache::forever(self::VERSION_KEY, $version);
        } catch (Throwable) {
            // Falhas de cache nao devem impedir publicacoes ou edicoes.
        }

        return $version;
    }

    public static function version(): int
    {
        try {
            $version = Cache::get(self::VERSION_KEY);

            if (is_numeric($version) && (int) $version > 0) {
                return (int) $version;
            }

            Cache::forever(self::VERSION_KEY, 1);
        } catch (Throwable) {
            // Mantem a aplicacao funcional quando o cache estiver indisponivel.
        }

        return 1;
    }
}
