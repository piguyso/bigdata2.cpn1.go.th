<?php

namespace App\Services;

class SurveyAlignmentService
{
    /**
     * Normalize text for string matching.
     */
    public function normalizeForMatch(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim((string)$text);
    }

    /**
     * Extract subject categories from text.
     */
    public function extractTeachingCategories(string $text): array
    {
        $normalized = $this->normalizeForMatch($text);
        if ($normalized === '') {
            return [];
        }

        $categoryKeywords = [
            'thai' => ['ภาษาไทย', 'thai'],
            'math' => ['คณิต', 'mathematics', 'math'],
            'science' => ['วิทยาศาสตร์', 'วิทย์', 'ชีว', 'เคมี', 'ฟิสิก', 'science', 'biology', 'chemistry', 'physics'],
            'english' => ['อังกฤษ', 'english'],
            'social' => ['สังคม', 'ประวัติศาสตร์', 'ภูมิศาสตร์', 'หน้าที่พลเมือง', 'social'],
            'health_pe' => ['พลศึกษา', 'สุขศึกษา', 'พละ', 'physical education', 'health'],
            'art' => ['ศิลปะ', 'ดนตรี', 'นาฏศิลป์', 'art', 'music'],
            'career_tech' => ['การงานอาชีพ', 'งานอาชีพ', 'คอมพิวเตอร์', 'เทคโนโลยี', 'ict', 'coding', 'computer', 'technology'],
            'early_childhood' => ['ปฐมวัย', 'อนุบาล', 'early childhood'],
            'special_edu' => ['การศึกษาพิเศษ', 'special education'],
            'foreign_lang' => ['ภาษาจีน', 'ภาษาญี่ปุ่น', 'ภาษาเกาหลี', 'ภาษาฝรั่งเศส', 'ภาษาเยอรมัน', 'chinese', 'japanese', 'korean', 'french', 'german'],
        ];

        $matched = [];
        foreach ($categoryKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_strpos($normalized, $this->normalizeForMatch($keyword), 0, 'UTF-8') !== false) {
                    $matched[$category] = true;
                    break;
                }
            }
        }

        return array_keys($matched);
    }

    /**
     * Get human-readable label for category key.
     */
    public function getCategoryLabel(string $category): string
    {
        $labels = [
            'thai' => 'ภาษาไทย',
            'math' => 'คณิตศาสตร์',
            'science' => 'วิทยาศาสตร์',
            'english' => 'ภาษาอังกฤษ',
            'social' => 'สังคมศึกษา',
            'health_pe' => 'สุขศึกษา/พลศึกษา',
            'art' => 'ศิลปะ/ดนตรี/นาฏศิลป์',
            'career_tech' => 'การงานอาชีพ/คอมพิวเตอร์',
            'early_childhood' => 'ปฐมวัย',
            'special_edu' => 'การศึกษาพิเศษ',
            'foreign_lang' => 'ภาษาต่างประเทศอื่น',
        ];

        return $labels[$category] ?? $category;
    }

    /**
     * Evaluate major-subject alignment.
     * Accepts models/arrays for educations and subjects.
     */
    public function evaluateAlignment($educations, $subjects): array
    {
        $majorTexts = [];
        foreach ($educations as $edu) {
            $field = is_array($edu) ? ($edu['field_of_study'] ?? '') : ($edu->field_of_study ?? '');
            $major = is_array($edu) ? ($edu['major'] ?? '') : ($edu->major ?? '');
            $combined = trim($field . ' ' . $major);
            if ($combined !== '') {
                $majorTexts[] = $combined;
            }
        }

        $subjectTexts = [];
        foreach ($subjects as $subject) {
            $name = is_array($subject) ? ($subject['subject_name'] ?? '') : ($subject->subject_name ?? '');
            if ($name !== '') {
                $subjectTexts[] = $name;
            }
        }

        if (empty($majorTexts) || empty($subjectTexts)) {
            return [
                'status' => 'insufficient',
                'label' => 'ข้อมูลไม่เพียงพอ',
                'description' => 'ต้องมีทั้งข้อมูลสาขา/วิชาเอก และรายวิชาที่สอน จึงจะประเมินได้',
                'score' => 0,
                'matchedLabels' => [],
                'unmatchedMajorLabels' => [],
            ];
        }

        $majorCategories = [];
        foreach ($majorTexts as $text) {
            foreach ($this->extractTeachingCategories($text) as $category) {
                $majorCategories[$category] = true;
            }
        }

        $subjectCategories = [];
        foreach ($subjectTexts as $text) {
            foreach ($this->extractTeachingCategories($text) as $category) {
                $subjectCategories[$category] = true;
            }
        }

        if (empty($majorCategories) || empty($subjectCategories)) {
            return [
                'status' => 'insufficient',
                'label' => 'ข้อมูลไม่เพียงพอ',
                'description' => 'ระบบยังไม่พบหมวดวิชาที่ชัดเจนจากข้อมูลที่กรอก',
                'score' => 0,
                'matchedLabels' => [],
                'unmatchedMajorLabels' => [],
            ];
        }

        $majorKeys = array_keys($majorCategories);
        $subjectKeys = array_keys($subjectCategories);
        $matched = array_values(array_intersect($majorKeys, $subjectKeys));
        $unmatchedMajor = array_values(array_diff($majorKeys, $subjectKeys));

        $score = (int)round((count($matched) / max(1, count($majorKeys))) * 100);

        if ($score >= 70) {
            $status = 'good';
            $label = 'สอดคล้องสูง';
            $description = 'วิชาที่สอนมีความสอดคล้องกับสาขา/วิชาเอกค่อนข้างสูง';
        } elseif ($score > 0) {
            $status = 'partial';
            $label = 'สอดคล้องบางส่วน';
            $description = 'พบความสอดคล้องบางรายวิชา แต่อาจมีวิชาที่สอนนอกสาขา';
        } else {
            $status = 'low';
            $label = 'ยังไม่สอดคล้อง';
            $description = 'ยังไม่พบความเชื่อมโยงชัดเจนระหว่างวิชาเอกกับวิชาที่สอน';
        }

        return [
            'status' => $status,
            'label' => $label,
            'description' => $description,
            'score' => $score,
            'matchedLabels' => array_map([$this, 'getCategoryLabel'], $matched),
            'unmatchedMajorLabels' => array_map([$this, 'getCategoryLabel'], $unmatchedMajor),
        ];
    }
}
