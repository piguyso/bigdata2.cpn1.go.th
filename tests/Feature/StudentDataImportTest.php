<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentDataImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_preview_and_import_student_extra_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createAcademicYear('2569');
        $this->createSchool('86010001', 'โรงเรียนทดสอบ');

        $file = UploadedFile::fake()->createWithContent(
            'student-age.csv',
            $this->makeCategoryGradeCsv('2569-1', '86010001', 'อายุ 7 ปี', 5, 7, 12)
        );

        $preview = $this->actingAs($admin)->post('/admin/student-data-imports/preview', [
            'academic_year' => '2569',
            'term' => 1,
            'data_type' => 'age',
            'file' => $file,
        ]);

        $preview->assertOk()
            ->assertJson([
                'status' => 'success',
                'preview' => [
                    'valid_rows' => 1,
                    'invalid_rows' => 0,
                    'unmatched_rows' => 0,
                ],
            ]);

        $this->actingAs($admin)->postJson('/admin/student-data-imports/import', [
            'academic_year' => '2569',
            'term' => 1,
            'data_type' => 'age',
            'upload_token' => $preview->json('upload_token'),
            'source_filename' => 'student-age.csv',
        ])->assertOk()
            ->assertJson([
                'status' => 'success',
                'imported_rows' => 1,
            ]);

        $this->assertDatabaseHas('student_data_records', [
            'academic_year' => '2569',
            'term' => '1',
            'data_type' => 'age',
            'school_smis' => '86010001',
            'category' => 'อายุ 7 ปี',
            'total_male' => 5,
            'total_female' => 7,
            'total' => 12,
        ]);
    }

    public function test_preview_blocks_year_term_mismatch_before_import(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createAcademicYear('2569');
        $this->createSchool('86010001', 'โรงเรียนทดสอบ');

        $file = UploadedFile::fake()->createWithContent(
            'wrong-term.csv',
            $this->makeCategoryGradeCsv('2569-2', '86010001', 'อายุ 7 ปี', 5, 7, 12)
        );

        $this->actingAs($admin)->post('/admin/student-data-imports/preview', [
            'academic_year' => '2569',
            'term' => 1,
            'data_type' => 'age',
            'file' => $file,
        ])->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'preview' => [
                    'valid_rows' => 0,
                    'invalid_rows' => 1,
                ],
            ]);

        $this->assertDatabaseCount('student_data_records', 0);
    }

    public function test_student_data_dashboard_api_paginates_and_searches_on_server(): void
    {
        $this->createAcademicYear('2569');
        $importId = $this->createStudentDataImport();

        for ($i = 1; $i <= 15; $i++) {
            $smis = '8601' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $this->createSchool($smis, 'โรงเรียนทดสอบ ' . $i);
            $this->createStudentDataRecord($importId, $smis, 'อายุ ' . $i . ' ปี', $i);
        }
        $this->createSchool('86010000', 'โรงเรียนไม่มีข้อมูล');
        $this->createZeroStudentDataRecord($importId, '86010000', 'ไม่มีข้อมูล');

        DB::table('system_school')
            ->where('smis', '86010007')
            ->update(['logo_path' => 'school-logos/asset/86010007.png']);

        $this->getJson('/api/student-data/dashboard?academic_year=2569&term=1&data_type=age&page=2&per_page=6')
            ->assertOk()
            ->assertJsonPath('pagination.current_page', 2)
            ->assertJsonPath('pagination.per_page', 6)
            ->assertJsonPath('pagination.total', 15)
            ->assertJsonPath('schools.0.school_smis', '86010007')
            ->assertJsonPath('schools.0.logo_url', '/storage/school-logos/asset/86010007.png')
            ->assertJsonCount(6, 'schools');

        $this->getJson('/api/student-data/dashboard?academic_year=2569&term=1&data_type=age&search=ทดสอบ%2015&per_page=12')
            ->assertOk()
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('schools.0.school_smis', '86010015');

        $this->getJson('/api/student-data/dashboard?academic_year=2569&term=1&data_type=age&category=อายุ%2015%20ปี&per_page=12')
            ->assertOk()
            ->assertJsonPath('category', 'อายุ 15 ปี')
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('schools.0.category', 'อายุ 15 ปี')
            ->assertJsonPath('summary.records', 1);

        $export = $this->get('/students/export/xlsx?academic_year=2569&term=1&data_type=age&category=อายุ%2015%20ปี');
        $export->assertOk();
        $sheetXml = $this->worksheetXml($export->baseResponse->getFile()->getPathname());
        $this->assertStringContainsString('ปีการศึกษา', $sheetXml);
        $this->assertStringContainsString('SMIS', $sheetXml);
        $this->assertStringContainsString('ชื่อโรงเรียน', $sheetXml);
        $this->assertStringContainsString('86010015', $sheetXml);
        $this->assertStringNotContainsString('86010014', $sheetXml);

        $this->getJson('/api/student-data/dashboard?academic_year=2569&term=1&data_type=age&search=ไม่มีข้อมูล&per_page=12')
            ->assertOk()
            ->assertJsonPath('pagination.total', 0)
            ->assertJsonPath('summary.records', 0)
            ->assertJsonCount(0, 'schools');

        $this->getJson('/api/student-data/dashboard?academic_year=2569&term=1&data_type=age&per_page=200')
            ->assertStatus(422);
    }

    public function test_student_dashboard_uses_academic_year_config_for_selector(): void
    {
        $this->createAcademicYear('2568', false);
        $this->createAcademicYear('2569', true);

        $this->get('/students')
            ->assertOk()
            ->assertSee('"year":"2569"', false)
            ->assertSee('"year":"2568"', false)
            ->assertSee('academic_year: "2569"', false);

        $this->getJson('/api/student-data/dashboard?data_type=age')
            ->assertOk()
            ->assertJsonPath('academic_year', '2569')
            ->assertJsonPath('academic_years.0.year', '2569');
    }

    private function createAcademicYear(string $year, bool $active = true): void
    {
        if ($active) {
            DB::table('academic_years')->update(['is_active' => false]);
        }

        DB::table('academic_years')->updateOrInsert(
            ['year' => $year],
            [
                'name' => 'ปีการศึกษา ' . $year,
                'is_active' => $active,
                'sort_order' => (int) $year,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function createSchool(string $smis, string $name): void
    {
        DB::table('system_group')->insertOrIgnore([
            'code' => '01',
            'name' => 'เมืองชุมพร 1',
        ]);

        DB::table('system_school')->insert([
            'smis' => $smis,
            'percode' => '',
            'ministry' => '',
            'schoolname' => $name,
            'schoolname_eng' => '',
            'schoolgroup' => '01',
            'muti' => '',
            'road' => '',
            'muban' => '',
            'tambon' => '',
            'amper' => '',
            'province' => 'ชุมพร',
            'postcode' => '',
            'lat' => '',
            'lng' => '',
            'length_km' => '',
            'maplink' => '',
            'tel' => '',
            'email' => '',
            'website' => '',
            'logo_path' => null,
            'statusID' => '1',
            'statusDetail' => 'เปิด',
        ]);
    }

    private function createStudentDataImport(): int
    {
        return DB::table('student_data_imports')->insertGetId([
            'academic_year' => '2569',
            'term' => '1',
            'data_type' => 'age',
            'data_label' => 'จำนวนนักเรียนจำแนกตามอายุ',
            'source_filename' => 'test.csv',
            'stored_filename' => null,
            'schema_version' => 'category_grade',
            'total_rows' => 15,
            'valid_rows' => 15,
            'imported_rows' => 15,
            'unmatched_rows' => 0,
            'invalid_rows' => 0,
            'mode' => 'replace',
            'created_by' => null,
            'warnings' => json_encode([], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createStudentDataRecord(int $importId, string $smis, string $category, int $total): void
    {
        DB::table('student_data_records')->insert([
            'import_id' => $importId,
            'academic_year' => '2569',
            'term' => '1',
            'data_type' => 'age',
            'school_id' => DB::table('system_school')->where('smis', $smis)->value('id'),
            'school_smis' => $smis,
            'category' => $category,
            'row_order' => $total,
            'metrics' => json_encode([
                'all_total' => [
                    'label' => 'รวมทั้งหมด',
                    'male' => $total,
                    'female' => $total + 1,
                    'total' => ($total * 2) + 1,
                ],
            ], JSON_UNESCAPED_UNICODE),
            'total_male' => $total,
            'total_female' => $total + 1,
            'total' => ($total * 2) + 1,
            'rooms_total' => 0,
            'payload' => json_encode([], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createZeroStudentDataRecord(int $importId, string $smis, string $category): void
    {
        DB::table('student_data_records')->insert([
            'import_id' => $importId,
            'academic_year' => '2569',
            'term' => '1',
            'data_type' => 'age',
            'school_id' => DB::table('system_school')->where('smis', $smis)->value('id'),
            'school_smis' => $smis,
            'category' => $category,
            'row_order' => 0,
            'metrics' => json_encode([
                'all_total' => [
                    'label' => 'รวมทั้งหมด',
                    'male' => 0,
                    'female' => 0,
                    'total' => 0,
                ],
            ], JSON_UNESCAPED_UNICODE),
            'total_male' => 0,
            'total_female' => 0,
            'total' => 0,
            'rooms_total' => 0,
            'payload' => json_encode([], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeCategoryGradeCsv(string $yearTerm, string $smis, string $category, int $male, int $female, int $total): string
    {
        $groups = array_fill(0, 22, [0, 0, 0]);
        $groups[] = [$male, $female, $total];

        return implode(',', array_merge([$yearTerm, $smis, $category], array_merge(...$groups)));
    }

    private function worksheetXml(string $xlsxPath): string
    {
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($xlsxPath));
        $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        $this->assertIsString($xml);

        return html_entity_decode($xml, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
