<?php

namespace App\Support;

class SchoolLogo
{
    public static function url(mixed $path): ?string
    {
        $path = trim((string) $path);

        return $path === '' ? null : '/storage/'.ltrim($path, '/');
    }
}
