<?php

namespace App\Support;

use RuntimeException;

class TabularFileReader
{
    public static function rows(string $path, ?string $filename = null): array
    {
        $extension = strtolower(pathinfo($filename ?: $path, PATHINFO_EXTENSION));

        return match ($extension) {
            'csv', 'txt' => self::csvRows($path),
            'xlsx' => SimpleXlsxReader::rows($path),
            default => throw new RuntimeException('รองรับเฉพาะไฟล์ .csv, .txt หรือ .xlsx'),
        };
    }

    private static function csvRows(string $path): array
    {
        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException('ไม่สามารถเปิดไฟล์ CSV ได้');
        }

        if (! mb_check_encoding($content, 'UTF-8')) {
            $converted = @iconv('CP874', 'UTF-8//IGNORE', $content);
            if ($converted !== false) {
                $content = $converted;
            }
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? '';
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = array_filter(explode("\n", $content), fn ($line) => trim($line) !== '');

        return array_map(static fn ($line) => array_map(
            static fn ($cell) => self::cleanCell((string) $cell),
            str_getcsv($line)
        ), $lines);
    }

    private static function cleanCell(string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/u', '', $value) ?? $value;
        $value = preg_replace('/^๏ปฟ/u', '', $value) ?? $value;

        return trim($value);
    }
}
