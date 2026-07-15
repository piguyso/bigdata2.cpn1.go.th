<?php

namespace App\Support;

class StudentDataTypes
{
    public const LEVELS_23 = [
        'k1' => 'อนุบาล 1',
        'k2' => 'อนุบาล 2',
        'k3' => 'อนุบาล 3',
        'pre_primary_total' => 'รวมก่อนประถม',
        'p1' => 'ป.1',
        'p2' => 'ป.2',
        'p3' => 'ป.3',
        'p4' => 'ป.4',
        'p5' => 'ป.5',
        'p6' => 'ป.6',
        'primary_total' => 'รวมประถม',
        'm1' => 'ม.1',
        'm2' => 'ม.2',
        'm3' => 'ม.3',
        'lower_secondary_total' => 'รวมมัธยมต้น',
        'm4' => 'ม.4',
        'm5' => 'ม.5',
        'm6' => 'ม.6',
        'voc1' => 'ปวช.1',
        'voc2' => 'ปวช.2',
        'voc3' => 'ปวช.3',
        'upper_secondary_total' => 'รวมมัธยมปลาย/ปวช.',
        'all_total' => 'รวมทั้งหมด',
    ];

    public const TYPES = [
        'shortage' => ['label' => 'จำนวนนักเรียนขาดแคลน', 'schema' => 'category_grade'],
        'age' => ['label' => 'จำนวนนักเรียนจำแนกตามอายุ', 'schema' => 'category_grade'],
        'disadvantaged' => ['label' => 'จำนวนนักเรียนด้อยโอกาส', 'schema' => 'category_grade'],
        'disabled' => ['label' => 'จำนวนนักเรียนพิการ', 'schema' => 'category_grade'],
        'disabled_disadvantaged' => ['label' => 'จำนวนนักเรียนพิการและด้อยโอกาส', 'schema' => 'category_grade'],
        'distance_transport' => ['label' => 'จำนวนนักเรียนที่อยู่ห่างเกิน 3 กม.จำแนกตามการเดินทาง', 'schema' => 'category_grade'],
        'boarding' => ['label' => 'จำนวนนักเรียนพักนอน', 'schema' => 'category_grade'],
        'disposal' => ['label' => 'จำนวนนักเรียนจำหน่าย', 'schema' => 'category_grade'],
        'dropout' => ['label' => 'จำนวนนักเรียนออกกลางคัน', 'schema' => 'category_grade'],
        'religion' => ['label' => 'จำนวนนักเรียนแยกตามศาสนา', 'schema' => 'category_grade'],
        'nationality' => ['label' => 'จำนวนนักเรียนแยกตามสัญชาติ', 'schema' => 'category_grade'],
        'failed_assessment' => ['label' => 'จำนวนนักเรียนไม่ผ่านการตัดสินการประเมินปลายปีจำแนกตามเกณฑ์รายชั้น', 'schema' => 'category_grade'],
        'inspection' => ['label' => 'ตารางตรวจพินิจข้อมูลนักเรียน', 'schema' => 'category_grade'],
        'nutrition_weight_height' => ['label' => 'ภาวะโภชนาการ น้ำหนักตามเกณฑ์ส่วนสูง', 'schema' => 'category_grade'],
        'nutrition_height_age' => ['label' => 'ภาวะโภชนาการ ส่วนสูงตามเกณฑ์อายุ', 'schema' => 'category_grade'],
        'g_code' => ['label' => 'จำนวนนักเรียนรหัส G', 'schema' => 'grade_only'],
        'graduate_p6_continue' => ['label' => 'จำนวนนักเรียนจบ ป.6 ศึกษาต่อจำแนกตามเพศ สังกัด', 'schema' => 'category_group7'],
        'graduate_m3_continue' => ['label' => 'จำนวนนักเรียนจบการศึกษา ม.3 ศึกษาต่อ.ไม่ศึกษาต่อ', 'schema' => 'category_total'],
        'graduate_m6_continue' => ['label' => 'จำนวนนักเรียนจบการศึกษา ม.6 ศึกษาต่อ.ไม่ศึกษาต่อ', 'schema' => 'category_total'],
        'graduate_duration' => ['label' => 'จำนวนนักเรียนจบชั้นอนุบาล, ป.3, ป.6, ม.3, ม.6 และปวช. จำแนกตามเวลาที่ใช้เรียน', 'schema' => 'category_group6'],
        'electricity' => ['label' => 'จำนวนโรงเรียนที่มีไฟฟ้า-ไม่มีไฟฟ้า', 'schema' => 'facility4'],
        'water' => ['label' => 'จำนวนโรงเรียนที่ใช้น้ำประปา', 'schema' => 'facility18'],
        'internet' => ['label' => 'จำนวนโรงเรียนที่ใช้อินเทอร์เน็ต', 'schema' => 'facility11'],
    ];

    public static function all(): array
    {
        return self::TYPES;
    }

    public static function keys(): array
    {
        return array_keys(self::TYPES);
    }

    public static function defaultKey(): string
    {
        return 'age';
    }

    public static function get(string $type): ?array
    {
        return self::TYPES[$type] ?? null;
    }

    public static function expectedColumns(string $schema): int
    {
        return match ($schema) {
            'grade_rooms' => 94,
            'category_grade' => 72,
            'grade_only' => 71,
            'category_total' => 6,
            'category_group6' => 21,
            'category_group7' => 24,
            'facility4' => 4,
            'facility11' => 11,
            'facility18' => 18,
            default => 0,
        };
    }

    public static function headers(string $type): array
    {
        $definition = self::get($type);
        if (! $definition) {
            return [];
        }

        $schema = $definition['schema'];
        $headers = ['ปี-รอบ', 'SMIS'];

        if (in_array($schema, ['category_grade', 'category_total', 'category_group6', 'category_group7'], true)) {
            $headers[] = 'หมวด';
        }

        if (in_array($schema, ['category_grade', 'grade_only'], true)) {
            foreach (self::LEVELS_23 as $label) {
                array_push($headers, "{$label} ชาย", "{$label} หญิง", "{$label} รวม");
            }
        } elseif ($schema === 'category_total') {
            array_push($headers, 'ชาย', 'หญิง', 'รวม');
        } elseif ($schema === 'category_group6') {
            foreach (['กลุ่ม 1', 'กลุ่ม 2', 'กลุ่ม 3', 'กลุ่ม 4', 'กลุ่ม 5', 'กลุ่ม 6'] as $label) {
                array_push($headers, "{$label} ชาย", "{$label} หญิง", "{$label} รวม");
            }
        } elseif ($schema === 'category_group7') {
            foreach (['กลุ่ม 1', 'กลุ่ม 2', 'กลุ่ม 3', 'กลุ่ม 4', 'กลุ่ม 5', 'กลุ่ม 6', 'กลุ่ม 7'] as $label) {
                array_push($headers, "{$label} ชาย", "{$label} หญิง", "{$label} รวม");
            }
        } elseif (str_starts_with($schema, 'facility')) {
            $count = self::expectedColumns($schema) - 2;
            for ($i = 1; $i <= $count; $i++) {
                $headers[] = "ค่า {$i}";
            }
        }

        return $headers;
    }

    public static function templateRow(string $type, string $yearTerm = '2569-1'): array
    {
        $headers = self::headers($type);
        $row = array_fill(0, count($headers), '0');
        $row[0] = $yearTerm;
        $row[1] = '86010001';

        if (in_array(self::get($type)['schema'] ?? '', ['category_grade', 'category_total', 'category_group6', 'category_group7'], true)) {
            $row[2] = 'ตัวอย่างหมวด';
        }

        return $row;
    }
}
