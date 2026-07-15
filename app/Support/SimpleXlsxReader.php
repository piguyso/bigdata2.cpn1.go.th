<?php

namespace App\Support;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class SimpleXlsxReader
{
    public static function rows(string $path): array
    {
        if (! class_exists('ZipArchive')) {
            throw new RuntimeException('เซิร์ฟเวอร์ของคุณไม่รองรับการเปิดไฟล์ .xlsx (กรุณาเปิดใช้งาน PHP Extension: zip ในไฟล์ php.ini หรือแผงควบคุมโฮสติ้ง)');
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('ไม่สามารถเปิดไฟล์ XLSX ได้');
        }

        $sharedStrings = self::sharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (! is_string($sheetXml) || trim($sheetXml) === '') {
            throw new RuntimeException('ไม่พบ worksheet แรกในไฟล์ XLSX');
        }

        $sheet = self::xml($sheetXml);
        $rows = [];

        foreach ($sheet->sheetData->row ?? [] as $row) {
            $rowValues = [];

            foreach ($row->c ?? [] as $cell) {
                $cellRef = (string) ($cell['r'] ?? '');
                $columnIndex = self::columnIndex($cellRef);

                if ($columnIndex < 1) {
                    continue;
                }

                $rowValues[$columnIndex - 1] = self::cellValue($cell, $sharedStrings);
            }

            if ($rowValues !== []) {
                ksort($rowValues);
                $maxIndex = max(array_keys($rowValues));
                $rows[] = array_map(
                    fn ($index) => trim((string) ($rowValues[$index] ?? '')),
                    range(0, $maxIndex)
                );
            }
        }

        return $rows;
    }

    private static function sharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if (! is_string($xml) || trim($xml) === '') {
            return [];
        }

        $shared = self::xml($xml);
        $strings = [];

        foreach ($shared->si ?? [] as $si) {
            if (isset($si->t)) {
                $strings[] = (string) $si->t;
                continue;
            }

            $text = '';
            foreach ($si->r ?? [] as $run) {
                $text .= (string) ($run->t ?? '');
            }
            $strings[] = $text;
        }

        return $strings;
    }

    private static function cellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) ($cell['t'] ?? '');

        if ($type === 'inlineStr') {
            return (string) ($cell->is->t ?? '');
        }

        $value = (string) ($cell->v ?? '');

        if ($type === 's') {
            return (string) ($sharedStrings[(int) $value] ?? '');
        }

        return $value;
    }

    private static function columnIndex(string $cellRef): int
    {
        if (! preg_match('/^([A-Z]+)/i', $cellRef, $matches)) {
            return 0;
        }

        $index = 0;
        foreach (str_split(strtoupper($matches[1])) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index;
    }

    private static function xml(string $xml): SimpleXMLElement
    {
        $parsed = simplexml_load_string($xml);

        if (! $parsed instanceof SimpleXMLElement) {
            throw new RuntimeException('ไฟล์ XLSX มีรูปแบบ XML ไม่ถูกต้อง');
        }

        return $parsed;
    }
}
