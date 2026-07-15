<?php

namespace App\Services;

use App\Support\TabularFileReader;
use Illuminate\Support\Facades\DB;

class SchoolmisImportService
{
    private const SCHEMA_82 = [
        'k1',
        'k2',
        'k3',
        'pre_primary_total',
        'p1',
        'p2',
        'p3',
        'p4',
        'p5',
        'p6',
        'primary_total',
        'm1',
        'm2',
        'm3',
        'lower_secondary_total',
        'm4',
        'm5',
        'm6',
        'upper_secondary_total',
        'all_total',
    ];

    private const SCHEMA_94 = [
        'k1',
        'k2',
        'k3',
        'pre_primary_total',
        'p1',
        'p2',
        'p3',
        'p4',
        'p5',
        'p6',
        'primary_total',
        'm1',
        'm2',
        'm3',
        'lower_secondary_total',
        'm4',
        'm5',
        'm6',
        'voc1',
        'voc2',
        'voc3',
        'upper_secondary_total',
        'all_total',
    ];

    public function preview(string $path): array
    {
        $rows = $this->readCsvRows($path);
        $warnings = [];
        $validRows = [];
        $invalidRows = [];
        $detectedTokens = [];
        $schemas = [];
        $schools = DB::table('system_school')
            ->pluck('schoolname', 'smis')
            ->mapWithKeys(fn ($name, $smis) => [$this->normalizeDigits($smis) => $name])
            ->all();

        foreach ($rows as $index => $row) {
            if (! $this->isDataRow($row)) {
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

            $schoolSmis = $parsed['school_smis'];
            $parsed['school_name'] = $schools[$schoolSmis] ?? null;
            $parsed['matched_school'] = isset($schools[$schoolSmis]);
            $validRows[] = $parsed;
            $detectedTokens[$parsed['raw_year_term']] = true;
            $schemas[$parsed['schema_version']] = true;
        }

        if (count($schemas) > 1) {
            $warnings[] = 'ไฟล์นี้มี schema หลายแบบปะปนกัน ระบบจะนำเข้าตามจำนวนคอลัมน์ของแต่ละแถว';
        }

        if (count($detectedTokens) > 1) {
            $warnings[] = 'ไฟล์นี้มีค่าปี/รอบในข้อมูลมากกว่าหนึ่งแบบ ควรตรวจสอบก่อนนำเข้า';
        }

        $unmatchedRows = array_values(array_filter($validRows, fn ($row) => ! $row['matched_school']));
        if (count($unmatchedRows) > 0) {
            $warnings[] = 'พบรหัสโรงเรียนที่ยังไม่ตรงกับ system_school จำนวน ' . count($unmatchedRows) . ' รายการ';
        }

        return [
            'total_rows' => count($rows),
            'valid_rows' => count($validRows),
            'invalid_rows' => count($invalidRows),
            'unmatched_rows' => count($unmatchedRows),
            'schema_versions' => array_values(array_keys($schemas)),
            'detected_year_terms' => array_values(array_keys($detectedTokens)),
            'warnings' => $warnings,
            'invalid_samples' => array_slice($invalidRows, 0, 10),
            'sample_rows' => array_map(function ($row) {
                return [
                    'school_smis' => $row['school_smis'],
                    'school_name' => $row['school_name'],
                    'raw_year_term' => $row['raw_year_term'],
                    'schema_version' => $row['schema_version'],
                    'student_total' => $row['student_total'],
                    'room_total' => $row['room_total'],
                    'matched_school' => $row['matched_school'],
                ];
            }, array_slice($validRows, 0, 10)),
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

    private function isDataRow(array $row): bool
    {
        if (count($row) < 6) {
            return false;
        }

        $token = trim((string) ($row[0] ?? ''));
        $school = $this->normalizeDigits((string) ($row[1] ?? ''));

        return preg_match('/^\d{4}-\d$/', $token) === 1 && $school !== '';
    }

    private function parseDataRow(array $row): array
    {
        $rawYearTerm = trim((string) $row[0]);
        $schoolSmis = $this->normalizeDigits((string) $row[1]);
        $measureValues = array_values(array_slice($row, 2));
        $groupCount = intdiv(count($measureValues), 4);
        $labels = $this->labelsForGroupCount($groupCount);

        if ($labels === null || count($measureValues) !== count($labels) * 4) {
            return [
                'valid' => false,
                'reason' => 'จำนวนคอลัมน์ไม่ตรงกับ schema ของ schoolmis',
            ];
        }

        [$rowYear, $rowTerm] = explode('-', $rawYearTerm);
        $metrics = [];

        foreach ($labels as $index => $label) {
            $offset = $index * 4;
            $metrics[$label] = [
                'male' => $this->toInt($measureValues[$offset] ?? 0),
                'female' => $this->toInt($measureValues[$offset + 1] ?? 0),
                'total' => $this->toInt($measureValues[$offset + 2] ?? 0),
                'rooms' => $this->toInt($measureValues[$offset + 3] ?? 0),
            ];
        }

        $totalKey = array_key_exists('all_total', $metrics) ? 'all_total' : array_key_last($metrics);
        $totals = $metrics[$totalKey];

        return [
            'valid' => true,
            'raw_year_term' => $rawYearTerm,
            'detected_year' => $rowYear,
            'detected_term' => (int) $rowTerm,
            'school_smis' => $schoolSmis,
            'schema_version' => (string) (count($labels) * 4 + 2),
            'metrics' => $metrics,
            'male_total' => $totals['male'],
            'female_total' => $totals['female'],
            'student_total' => $totals['total'],
            'room_total' => $totals['rooms'],
        ];
    }

    private function labelsForGroupCount(int $groupCount): ?array
    {
        return match ($groupCount) {
            20 => self::SCHEMA_82,
            23 => self::SCHEMA_94,
            default => null,
        };
    }

    private function normalizeDigits(string $value): string
    {
        return preg_replace('/\D+/', '', trim($value)) ?: '';
    }

    private function toInt(mixed $value): int
    {
        $normalized = preg_replace('/[^0-9\-]/', '', trim((string) $value));

        if ($normalized === '' || $normalized === '-') {
            return 0;
        }

        return (int) $normalized;
    }
}
