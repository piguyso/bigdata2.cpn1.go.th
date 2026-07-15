<?php

namespace App\Services;

use App\Support\StudentDataTypes;
use App\Support\TabularFileReader;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StudentDataImportService
{
    public function preview(string $path, string $dataType, ?string $filename = null, ?string $expectedAcademicYear = null, ?string $expectedTerm = null): array
    {
        $definition = StudentDataTypes::get($dataType);
        if (! $definition) {
            throw new RuntimeException('ไม่พบชนิดข้อมูลที่เลือก');
        }

        $rows = TabularFileReader::rows($path, $filename);
        $schools = DB::table('system_school')
            ->pluck('schoolname', 'smis')
            ->mapWithKeys(fn ($name, $smis) => [$this->digits((string) $smis) => $name])
            ->all();

        $validRows = [];
        $invalidRows = [];
        $warnings = [];

        foreach ($rows as $index => $row) {
            if (! $this->isDataRow($row)) {
                continue;
            }

            $parsed = $this->parseRow($row, $definition['schema'], $dataType, $index + 1);

            if (! $parsed['valid']) {
                $invalidRows[] = [
                    'row_number' => $index + 1,
                    'reason' => $parsed['reason'],
                    'columns' => count($row),
                ];
                continue;
            }

            if ($expectedAcademicYear !== null && $expectedTerm !== null && $parsed['raw_year_term'] !== "{$expectedAcademicYear}-{$expectedTerm}") {
                $invalidRows[] = [
                    'row_number' => $index + 1,
                    'reason' => 'ปี-รอบในไฟล์ไม่ตรงกับค่าที่เลือก',
                    'columns' => count($row),
                ];
                continue;
            }

            $parsed['school_name'] = $schools[$parsed['school_smis']] ?? null;
            $parsed['matched_school'] = isset($schools[$parsed['school_smis']]);
            $validRows[] = $parsed;
        }

        $unmatchedRows = array_values(array_filter($validRows, fn ($row) => ! $row['matched_school']));

        if (count($invalidRows) > 0) {
            $warnings[] = 'พบแถวที่โครงสร้างไม่ตรงกับชนิดข้อมูลที่เลือก จำนวน ' . count($invalidRows) . ' แถว';
        }

        if (count($unmatchedRows) > 0) {
            $warnings[] = 'พบรหัสโรงเรียนที่ยังไม่ตรงกับ system_school จำนวน ' . count($unmatchedRows) . ' แถว';
        }

        return [
            'total_rows' => count($rows),
            'valid_rows' => count($validRows),
            'invalid_rows' => count($invalidRows),
            'unmatched_rows' => count($unmatchedRows),
            'warnings' => $warnings,
            'invalid_samples' => array_slice($invalidRows, 0, 10),
            'sample_rows' => array_map(fn ($row) => [
                'raw_year_term' => $row['raw_year_term'],
                'school_smis' => $row['school_smis'],
                'school_name' => $row['school_name'],
                'category' => $row['category'],
                'total' => $row['total'],
                'matched_school' => $row['matched_school'],
            ], array_slice($validRows, 0, 10)),
            'rows' => $validRows,
        ];
    }

    public function import(string $path, string $filename, array $options): array
    {
        $dataType = $options['data_type'];
        $definition = StudentDataTypes::get($dataType);
        $parsed = $this->preview($path, $dataType, $filename, $options['academic_year'], (string) $options['term']);

        if ($parsed['valid_rows'] === 0) {
            throw new RuntimeException('โครงสร้างไฟล์ไม่ถูกต้อง หรือไม่พบข้อมูลที่นำเข้าได้');
        }

        $schoolMap = DB::table('system_school')
            ->select('id', 'smis')
            ->get()
            ->mapWithKeys(fn ($school) => [$this->digits((string) $school->smis) => $school->id])
            ->all();

        $matchedRows = array_values(array_filter($parsed['rows'], fn ($row) => isset($schoolMap[$row['school_smis']])));

        if (count($matchedRows) === 0) {
            throw new RuntimeException('ไม่พบข้อมูลโรงเรียนที่จับคู่กับ system_school ได้');
        }

        $importId = DB::transaction(function () use ($options, $definition, $parsed, $matchedRows, $schoolMap, $filename) {
            DB::table('student_data_records')
                ->where('academic_year', $options['academic_year'])
                ->where('term', $options['term'])
                ->where('data_type', $options['data_type'])
                ->delete();

            DB::table('student_data_imports')
                ->where('academic_year', $options['academic_year'])
                ->where('term', $options['term'])
                ->where('data_type', $options['data_type'])
                ->delete();

            $importId = DB::table('student_data_imports')->insertGetId([
                'academic_year' => $options['academic_year'],
                'term' => (string) $options['term'],
                'data_type' => $options['data_type'],
                'data_label' => $definition['label'],
                'source_filename' => $options['source_filename'],
                'stored_filename' => basename($filename),
                'schema_version' => $definition['schema'],
                'total_rows' => $parsed['total_rows'],
                'valid_rows' => $parsed['valid_rows'],
                'imported_rows' => count($matchedRows),
                'unmatched_rows' => $parsed['unmatched_rows'],
                'invalid_rows' => $parsed['invalid_rows'],
                'mode' => 'replace',
                'warnings' => json_encode($parsed['warnings'], JSON_UNESCAPED_UNICODE),
                'created_by' => $options['created_by'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $payload = [];
            foreach ($matchedRows as $row) {
                $payload[] = [
                    'import_id' => $importId,
                    'academic_year' => $options['academic_year'],
                    'term' => (string) $options['term'],
                    'data_type' => $options['data_type'],
                    'school_id' => $schoolMap[$row['school_smis']],
                    'school_smis' => $row['school_smis'],
                    'category' => $row['category'],
                    'row_order' => $row['row_order'],
                    'metrics' => json_encode($row['metrics'], JSON_UNESCAPED_UNICODE),
                    'total_male' => $row['total_male'],
                    'total_female' => $row['total_female'],
                    'total' => $row['total'],
                    'rooms_total' => $row['rooms_total'],
                    'payload' => json_encode($row['payload'], JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach (array_chunk($payload, 500) as $chunk) {
                DB::table('student_data_records')->upsert(
                    $chunk,
                    ['academic_year', 'term', 'data_type', 'school_smis', 'category'],
                    ['import_id', 'school_id', 'row_order', 'metrics', 'total_male', 'total_female', 'total', 'rooms_total', 'payload', 'updated_at']
                );
            }

            return $importId;
        });

        return [
            'import_id' => $importId,
            'imported_rows' => count($matchedRows),
            'preview' => $parsed,
        ];
    }

    private function parseRow(array $row, string $schema, string $dataType, int $rowNumber): array
    {
        $expected = StudentDataTypes::expectedColumns($schema);
        if (count($row) !== $expected) {
            return ['valid' => false, 'reason' => 'จำนวนคอลัมน์ไม่ตรงกับชนิดข้อมูลที่เลือก'];
        }

        $yearTerm = trim((string) $row[0]);
        $schoolSmis = $this->digits((string) $row[1]);
        $category = null;
        $metrics = [];
        $offset = 2;

        if (! preg_match('/^\d{4}-\d+$/', $yearTerm) || $schoolSmis === '') {
            return ['valid' => false, 'reason' => 'ปี-รอบ หรือ SMIS ไม่ถูกต้อง'];
        }

        if (in_array($schema, ['category_grade', 'category_total', 'category_group6', 'category_group7'], true)) {
            $category = trim((string) $row[2]);
            $offset = 3;
        }

        if ($schema === 'grade_rooms') {
            foreach (array_keys(StudentDataTypes::LEVELS_23) as $index => $key) {
                $base = $offset + ($index * 4);
                $metrics[$key] = [
                    'label' => StudentDataTypes::LEVELS_23[$key],
                    'male' => $this->toInt($row[$base] ?? 0),
                    'female' => $this->toInt($row[$base + 1] ?? 0),
                    'total' => $this->toInt($row[$base + 2] ?? 0),
                    'rooms' => $this->toInt($row[$base + 3] ?? 0),
                ];
            }
        } elseif (in_array($schema, ['category_grade', 'grade_only'], true)) {
            foreach (array_keys(StudentDataTypes::LEVELS_23) as $index => $key) {
                $base = $offset + ($index * 3);
                $metrics[$key] = [
                    'label' => StudentDataTypes::LEVELS_23[$key],
                    'male' => $this->toInt($row[$base] ?? 0),
                    'female' => $this->toInt($row[$base + 1] ?? 0),
                    'total' => $this->toInt($row[$base + 2] ?? 0),
                ];
            }
        } elseif ($schema === 'category_total') {
            $metrics['total'] = [
                'label' => 'รวม',
                'male' => $this->toInt($row[$offset] ?? 0),
                'female' => $this->toInt($row[$offset + 1] ?? 0),
                'total' => $this->toInt($row[$offset + 2] ?? 0),
            ];
        } elseif (in_array($schema, ['category_group6', 'category_group7'], true)) {
            $groups = $schema === 'category_group6' ? 6 : 7;
            for ($i = 0; $i < $groups; $i++) {
                $base = $offset + ($i * 3);
                $key = 'group_' . ($i + 1);
                $metrics[$key] = [
                    'label' => 'กลุ่ม ' . ($i + 1),
                    'male' => $this->toInt($row[$base] ?? 0),
                    'female' => $this->toInt($row[$base + 1] ?? 0),
                    'total' => $this->toInt($row[$base + 2] ?? 0),
                ];
            }
        } elseif (str_starts_with($schema, 'facility')) {
            $category = 'ข้อมูลโรงเรียน';
            for ($i = $offset; $i < count($row); $i++) {
                $key = 'value_' . ($i - $offset + 1);
                $metrics[$key] = [
                    'label' => 'ค่า ' . ($i - $offset + 1),
                    'value' => $this->toInt($row[$i] ?? 0),
                    'raw' => trim((string) ($row[$i] ?? '')),
                ];
            }
        }

        if ($category === null || $category === '') {
            $category = StudentDataTypes::get($dataType)['label'];
        }

        $totalKey = array_key_exists('all_total', $metrics) ? 'all_total' : array_key_last($metrics);
        $totals = $metrics[$totalKey] ?? ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0, 'value' => 0];

        return [
            'valid' => true,
            'row_order' => $rowNumber,
            'raw_year_term' => $yearTerm,
            'school_smis' => $schoolSmis,
            'category' => $category,
            'metrics' => $metrics,
            'total_male' => (int) ($totals['male'] ?? 0),
            'total_female' => (int) ($totals['female'] ?? 0),
            'total' => (int) ($totals['total'] ?? $totals['value'] ?? 0),
            'rooms_total' => (int) ($totals['rooms'] ?? 0),
            'payload' => [
                'raw_year_term' => $yearTerm,
                'schema' => $schema,
                'columns' => count($row),
            ],
        ];
    }

    private function isDataRow(array $row): bool
    {
        $token = trim((string) ($row[0] ?? ''));

        return preg_match('/^\d{4}-\d+$/', $token) === 1;
    }

    private function digits(string $value): string
    {
        return preg_replace('/\D+/', '', trim($value)) ?: '';
    }

    private function toInt(mixed $value): int
    {
        $normalized = preg_replace('/[^0-9\-]/', '', trim((string) $value));

        return $normalized === '' || $normalized === '-' ? 0 : (int) $normalized;
    }
}
