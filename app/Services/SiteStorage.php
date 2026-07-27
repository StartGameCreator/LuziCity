<?php

namespace App\Services;

use App\Models\Site;

class SiteStorage
{
    public static function directory(string $directory): string
    {
        $site = Site::current();

        return 'sites/'.($site?->slug ?: 'default').'/'.trim($directory, '/');
    }
}
