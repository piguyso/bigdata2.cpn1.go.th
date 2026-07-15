<?php

namespace App\Services;

use App\Support\TabularFileReader;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RtImportService
{
    public function preview(string $path): array
    {
        $workbook = $this->readWorkbookDataRows($path);
        $schools = $this->schoolMap();
        $validRowsBySchoolCode = [];
        $invalidRows = [];
        $warnings = [];
        $duplicateRows = 0;

        foreach ($workbook['rows'] as $index => $row) {
            if (! $this->looksLikeDataRow($row)) {
                continue;
            }

            $parsed = $this->parseDataRow($row);
            if (! $parsed['valid']) {
                $invalidRows[] = [
                    'row_number' => $index + 1,
                    'reason' => $parsed['reason'],
                ];
                continue;
            }

            $school = $schools[$this->normalizeName($parsed['school_name'])]
                ?? $schools[$this->normalizeBaseName($parsed['school_name'])]
                ?? null;
            $parsed['school_id'] = $school['id'] ?? null;
            $parsed['system_smis'] = $school['smis'] ?? null;
            $parsed['matched_school'] = $school !== null;

            $schoolCode = $parsed['rt_school_code'];
            if (isset($validRowsBySchoolCode[$schoolCode])) {
                $duplicateRows++;
                if ($this->scoreCompleteness($parsed) <= $this->scoreCompleteness($validRowsBySchoolCode[$schoolCode])) {
                    continue;
                }
            }

            $validRowsBySchoolCode[$schoolCode] = $parsed;
        }

        $validRows = array_values($validRowsBySchoolCode);
        $unmatchedRows = array_values(array_filter($validRows, fn ($row) => ! $row['matched_school']));
        if (count($unmatchedRows) > 0) {
            $warnings[] = 'พบโรงเรียนที่ยังจับคู่กับ system_school ไม่ได้จำนวน '.count($unmatchedRows).' รายการ';
        }
        if ($duplicateRows > 0) {
            $warnings[] = 'พบแถวรหัสโรงเรียนซ้ำจากหลาย sheet จำนวน '.$duplicateRows.' แถว ระบบเลือกแถวที่มีค่าสถิติครบที่สุดให้อัตโนมัติ';
        }

        return [
            'total_rows' => count($workbook['rows']),
            'valid_rows' => count($validRows),
            'invalid_rows' => count($invalidRows),
            'unmatched_rows' => count($unmatchedRows),
            'sheet_name' => $workbook['sheet_name'],
            'sheet_names' => $workbook['sheet_names'],
            'schema_versions' => ['rt_auto_school_rows'],
            'warnings' => $warnings,
            'invalid_samples' => array_slice($invalidRows, 0, 10),
            'sample_rows' => array_map(fn ($row) => [
                'rt_school_code' => $row['rt_school_code'],
                'school_name' => $row['school_name'],
                'district' => $row['district'],
                'school_size' => $row['school_size'],
                'students_count' => $row['students_count'],
                'reading_aloud_percent' => $row['reading_aloud_percent'],
                'reading_comprehension_percent' => $row['reading_comprehension_percent'],
                'total_percent' => $row['total_percent'],
                'source_sheet' => $row['source_sheet'],
                'matched_school' => $row['matched_school'],
                'system_smis' => $row['system_smis'],
            ], array_slice($validRows, 0, 10)),
            'rows' => $validRows,
        ];
    }

    public function parseForImport(string $path): array
    {
        return $this->preview($path);
    }

    private function readWorkbookDataRows(string $path): array
    {
        if (in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['csv', 'txt'], true)) {
            return [
                'sheet_name' => 'csv',
                'sheet_names' => ['csv'],
                'rows' => array_map(function (array $row) {
                    $mapped = ['_sheet_name' => 'csv'];

                    foreach (array_values($row) as $index => $value) {
                        $mapped[$this->columnName($index + 1)] = trim((string) $value);
                    }

                    return $mapped;
                }, TabularFileReader::rows($path)),
            ];
        }

        $zipData = file_get_contents($path);
        if ($zipData === false) {
            throw new RuntimeException('ไม่สามารถเปิดไฟล์ Excel ได้');
        }

        $sharedStrings = $this->readSharedStrings($zipData);
        $sheetMap = $this->readSheetMap($zipData);
        if (count($sheetMap) === 0) {
            throw new RuntimeException('ไม่พบ sheet ในไฟล์ Excel');
        }

        $rows = [];
        foreach ($sheetMap as $sheetName => $sheetPath) {
            $sheetXml = $this->zipEntry($zipData, $sheetPath);
            if ($sheetXml === null) {
                continue;
            }

            foreach ($this->parseRows($sheetXml, $sharedStrings) as $row) {
                $row['_sheet_name'] = $sheetName;
                $rows[] = $row;
            }
        }

        return [
            'sheet_name' => 'auto',
            'sheet_names' => array_keys($sheetMap),
            'rows' => $rows,
        ];
    }

    private function readSharedStrings(string $zipData): array
    {
        $xml = $this->zipEntry($zipData, 'xl/sharedStrings.xml');
        if ($xml === null) {
            return [];
        }

        preg_match_all('/<si.*?<\/si>/s', $xml, $items);

        return array_map(function ($item) {
            preg_match_all('/<t[^>]*>(.*?)<\/t>/s', $item, $matches);

            return html_entity_decode(implode('', $matches[1]), ENT_QUOTES | ENT_XML1, 'UTF-8');
        }, $items[0]);
    }

    private function readSheetMap(string $zipData): array
    {
        $workbookXml = $this->zipEntry($zipData, 'xl/workbook.xml');
        $relsXml = $this->zipEntry($zipData, 'xl/_rels/workbook.xml.rels');
        if ($workbookXml === null || $relsXml === null) {
            return [];
        }

        preg_match_all('/<Relationship[^>]*Id="([^"]+)"[^>]*Target="([^"]+)"/', $relsXml, $relMatches, PREG_SET_ORDER);
        $rels = [];
        foreach ($relMatches as $match) {
            $rels[$match[1]] = 'xl/'.ltrim($match[2], '/');
        }

        preg_match_all('/<sheet[^>]*name="([^"]+)"[^>]*r:id="([^"]+)"/', $workbookXml, $sheetMatches, PREG_SET_ORDER);
        $sheets = [];
        foreach ($sheetMatches as $match) {
            if (isset($rels[$match[2]])) {
                $sheets[html_entity_decode($match[1], ENT_QUOTES | ENT_XML1, 'UTF-8')] = $rels[$match[2]];
            }
        }

        return $sheets;
    }

    private function zipEntry(string $zipData, string $entryName): ?string
    {
        $entries = $this->zipDirectory($zipData);
        if (! isset($entries[$entryName])) {
            return null;
        }

        $entry = $entries[$entryName];
        $localOffset = $entry['local_offset'];
        if (substr($zipData, $localOffset, 4) !== "PK\x03\x04") {
            return null;
        }

        $nameLength = $this->u16($zipData, $localOffset + 26);
        $extraLength = $this->u16($zipData, $localOffset + 28);
        $dataOffset = $localOffset + 30 + $nameLength + $extraLength;
        $compressed = substr($zipData, $dataOffset, $entry['compressed_size']);

        if ($entry['method'] === 0) {
            return $compressed;
        }

        if ($entry['method'] === 8) {
            $content = gzinflate($compressed);

            return $content === false ? null : $content;
        }

        throw new RuntimeException('ไม่รองรับ compression method '.$entry['method'].' ในไฟล์ Excel');
    }

    private function zipDirectory(string $zipData): array
    {
        $eocdOffset = strrpos($zipData, "PK\x05\x06");
        if ($eocdOffset === false) {
            throw new RuntimeException('ไฟล์ xlsx ไม่สมบูรณ์หรือไม่ใช่ไฟล์ Excel');
        }

        $centralOffset = $this->u32($zipData, $eocdOffset + 16);
        $entries = [];
        $offset = $centralOffset;

        while (substr($zipData, $offset, 4) === "PK\x01\x02") {
            $method = $this->u16($zipData, $offset + 10);
            $compressedSize = $this->u32($zipData, $offset + 20);
            $nameLength = $this->u16($zipData, $offset + 28);
            $extraLength = $this->u16($zipData, $offset + 30);
            $commentLength = $this->u16($zipData, $offset + 32);
            $localOffset = $this->u32($zipData, $offset + 42);
            $name = substr($zipData, $offset + 46, $nameLength);

            $entries[$name] = [
                'method' => $method,
                'compressed_size' => $compressedSize,
                'local_offset' => $localOffset,
            ];

            $offset += 46 + $nameLength + $extraLength + $commentLength;
        }

        return $entries;
    }

    private function u16(string $data, int $offset): int
    {
        return unpack('v', substr($data, $offset, 2))[1];
    }

    private function u32(string $data, int $offset): int
    {
        return unpack('V', substr($data, $offset, 4))[1];
    }

    private function columnName(int $index): string
    {
        $name = '';

        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)).$name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private function parseRows(string $sheetXml, array $sharedStrings): array
    {
        preg_match_all('/<row[^>]*r="(\d+)"[^>]*>(.*?)<\/row>/s', $sheetXml, $rowMatches, PREG_SET_ORDER);

        return array_map(function ($rowMatch) use ($sharedStrings) {
            preg_match_all('/<c[^>]*r="([A-Z]+)\d+"([^>]*)>(.*?)<\/c>/s', $rowMatch[2], $cellMatches, PREG_SET_ORDER);
            $row = ['_row_number' => (int) $rowMatch[1]];

            foreach ($cellMatches as $cellMatch) {
                $row[$cellMatch[1]] = $this->cellValue($cellMatch[2], $cellMatch[3], $sharedStrings);
            }

            return $row;
        }, $rowMatches);
    }

    private function cellValue(string $attributes, string $body, array $sharedStrings): string
    {
        if (preg_match('/<v>(.*?)<\/v>/s', $body, $valueMatch) !== 1) {
            return '';
        }

        $value = $valueMatch[1];
        if (str_contains($attributes, 't="s"')) {
            $value = $sharedStrings[(int) $value] ?? $value;
        }

        return trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8'));
    }

    private function looksLikeDataRow(array $row): bool
    {
        return preg_match('/^\d{10}$/', $this->normalizeDigits((string) ($row['C'] ?? ''))) === 1
            && trim((string) ($row['D'] ?? '')) !== '';
    }

    private function parseDataRow(array $row): array
    {
        return [
            'valid' => true,
            'schema_version' => 'rt_auto_school_rows',
            'source_sheet' => trim((string) ($row['_sheet_name'] ?? '')),
            'rt_school_code' => $this->normalizeDigits((string) ($row['C'] ?? '')),
            'school_name' => trim((string) ($row['D'] ?? '')),
            'district' => trim((string) ($row['E'] ?? '')),
            'school_size' => trim((string) ($row['F'] ?? '')),
            'students_count' => (int) $this->toDecimal($row['G'] ?? 0),
            'reading_aloud_percent' => $this->toDecimal($row['H'] ?? null),
            'reading_aloud_sd' => $this->toDecimal($row['I'] ?? null),
            'reading_aloud_max' => $this->toDecimal($row['J'] ?? null),
            'reading_aloud_min' => $this->toDecimal($row['K'] ?? null),
            'reading_aloud_mode' => $this->toDecimal($row['L'] ?? null),
            'reading_aloud_median' => $this->toDecimal($row['M'] ?? null),
            'reading_comprehension_percent' => $this->toDecimal($row['N'] ?? null),
            'reading_comprehension_sd' => $this->toDecimal($row['O'] ?? null),
            'reading_comprehension_max' => $this->toDecimal($row['P'] ?? null),
            'reading_comprehension_min' => $this->toDecimal($row['Q'] ?? null),
            'reading_comprehension_mode' => $this->toDecimal($row['R'] ?? null),
            'reading_comprehension_median' => $this->toDecimal($row['S'] ?? null),
            'total_percent' => $this->toDecimal($row['T'] ?? null),
            'total_sd' => $this->toDecimal($row['U'] ?? null),
            'total_max' => $this->toDecimal($row['V'] ?? null),
            'total_min' => $this->toDecimal($row['W'] ?? null),
            'total_mode' => $this->toDecimal($row['X'] ?? null),
            'total_median' => $this->toDecimal($row['Y'] ?? null),
            'payload' => [
                'raw' => $row,
            ],
        ];
    }

    private function schoolMap(): array
    {
        $map = [];

        DB::table('system_school')
            ->select('id', 'smis', 'schoolname')
            ->get()
            ->each(function ($school) use (&$map) {
                $schoolData = [
                    'id' => (int) $school->id,
                    'smis' => (string) $school->smis,
                ];

                foreach ([$this->normalizeName((string) $school->schoolname), $this->normalizeBaseName((string) $school->schoolname)] as $key) {
                    if ($key !== '' && ! isset($map[$key])) {
                        $map[$key] = $schoolData;
                    }
                }
            });

        return $map;
    }

    private function scoreCompleteness(array $row): int
    {
        $scoreFields = [
            'reading_aloud_percent',
            'reading_aloud_sd',
            'reading_aloud_max',
            'reading_aloud_min',
            'reading_aloud_mode',
            'reading_aloud_median',
            'reading_comprehension_percent',
            'reading_comprehension_sd',
            'reading_comprehension_max',
            'reading_comprehension_min',
            'reading_comprehension_mode',
            'reading_comprehension_median',
            'total_percent',
            'total_sd',
            'total_max',
            'total_min',
            'total_mode',
            'total_median',
        ];

        return count(array_filter($scoreFields, fn ($field) => $row[$field] !== null));
    }

    private function normalizeName(string $value): string
    {
        $value = preg_replace('/\s+/u', '', trim($value)) ?: '';
        $value = str_replace(['(', ')', '（', '）', '.', 'ฯ'], '', $value);

        return $value;
    }

    private function normalizeBaseName(string $value): string
    {
        $value = preg_replace('/[\(（].*?[\)）]/u', '', trim($value)) ?: '';

        return $this->normalizeName($value);
    }

    private function normalizeDigits(string $value): string
    {
        return preg_replace('/\D+/', '', trim($value)) ?: '';
    }

    private function toDecimal(mixed $value): ?float
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        $normalized = str_replace(',', '', $normalized);

        return is_numeric($normalized) ? (float) $normalized : null;
    }
}
