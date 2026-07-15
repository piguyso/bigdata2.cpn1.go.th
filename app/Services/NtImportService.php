<?php

namespace App\Services;

use App\Support\TabularFileReader;
use Illuminate\Support\Facades\DB;

class NtImportService
{
    public function preview(string $path): array
    {
        $rows = $this->readCsvRows($path);
        $schools = $this->schoolMap();
        $validRows = [];
        $invalidRows = [];
        $warnings = [];

        foreach ($rows as $index => $row) {
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
            $validRows[] = $parsed;
        }

        $unmatchedRows = array_values(array_filter($validRows, fn ($row) => ! $row['matched_school']));
        if (count($unmatchedRows) > 0) {
            $warnings[] = 'พบโรงเรียนที่ยังจับคู่กับ system_school ไม่ได้จำนวน '.count($unmatchedRows).' รายการ';
        }

        return [
            'total_rows' => count($rows),
            'valid_rows' => count($validRows),
            'invalid_rows' => count($invalidRows),
            'unmatched_rows' => count($unmatchedRows),
            'schema_versions' => ['nt13'],
            'warnings' => $warnings,
            'invalid_samples' => array_slice($invalidRows, 0, 10),
            'sample_rows' => array_map(fn ($row) => [
                'nt_school_code' => $row['nt_school_code'],
                'school_name' => $row['school_name'],
                'district' => $row['district'],
                'school_size' => $row['school_size'],
                'math_percent' => $row['math_percent'],
                'thai_percent' => $row['thai_percent'],
                'total_percent' => $row['total_percent'],
                'total_quality' => $row['total_quality'],
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

    private function readCsvRows(string $path): array
    {
        return TabularFileReader::rows($path);
    }

    private function looksLikeDataRow(array $row): bool
    {
        return count($row) >= 13
            && preg_match('/^\d{10}$/', $this->normalizeDigits((string) ($row[0] ?? ''))) === 1
            && trim((string) ($row[1] ?? '')) !== '';
    }

    private function parseDataRow(array $row): array
    {
        if (count($row) < 13) {
            return [
                'valid' => false,
                'reason' => 'จำนวนคอลัมน์ไม่ครบ 13 คอลัมน์ตาม schema NT',
            ];
        }

        return [
            'valid' => true,
            'schema_version' => 'nt13',
            'nt_school_code' => $this->normalizeDigits((string) $row[0]),
            'school_name' => trim((string) $row[1]),
            'district' => trim((string) $row[2]),
            'school_size' => trim((string) $row[3]),
            'math_score' => $this->toDecimal($row[4] ?? null),
            'math_percent' => $this->toDecimal($row[5] ?? null),
            'thai_score' => $this->toDecimal($row[6] ?? null),
            'thai_percent' => $this->toDecimal($row[7] ?? null),
            'total_score' => $this->toDecimal($row[8] ?? null),
            'total_percent' => $this->toDecimal($row[9] ?? null),
            'math_quality' => trim((string) ($row[10] ?? '')),
            'thai_quality' => trim((string) ($row[11] ?? '')),
            'total_quality' => trim((string) ($row[12] ?? '')),
            'payload' => [
                'raw' => array_values($row),
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
