<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SchoolmisImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_schoolmis_management(): void
    {
        $this->get('/admin/schoolmis')->assertRedirect('/login');
        $this->get('/admin/schoolmis/data')->assertRedirect('/login');
        $this->post('/admin/schoolmis/preview', [])->assertRedirect('/login');
        $this->post('/admin/schoolmis/import', [])->assertRedirect('/login');
        $this->delete('/admin/schoolmis/data-set')->assertRedirect('/login');
    }

    public function test_admin_can_preview_and_import_schoolmis_file(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createAcademicYear('2569');
        $this->createSchool('86010001', 'โรงเรียนทดสอบ');

        $file = UploadedFile::fake()->createWithContent(
            'schoolmis.csv',
            $this->makeSchoolmisCsv([
                ['2569-1', '86010001', $this->measureSet(10, 12, 22, 2)],
            ])
        );

        $preview = $this->actingAs($admin)->post('/admin/schoolmis/preview', [
            'academic_year' => '2569',
            'term' => 1,
            'csv' => $file,
        ]);

        $preview->assertOk()
            ->assertJson([
                'status' => 'success',
                'preview' => [
                    'valid_rows' => 1,
                    'unmatched_rows' => 0,
                ],
            ]);

        $token = $preview->json('upload_token');

        $this->actingAs($admin)->postJson('/admin/schoolmis/import', [
            'academic_year' => '2569',
            'term' => 1,
            'mode' => 'replace',
            'upload_token' => $token,
            'source_filename' => 'schoolmis.csv',
        ])->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'นำเข้าข้อมูล SchoolMIS เรียบร้อยแล้ว (1 โรงเรียน)',
                'imported_rows' => 1,
            ]);

        $this->assertDatabaseHas('schoolmis_imports', [
            'academic_year' => '2569',
            'term' => 1,
            'imported_rows' => 1,
        ]);

        $this->assertDatabaseHas('schoolmis_records', [
            'academic_year' => '2569',
            'term' => 1,
            'school_smis' => '86010001',
            'student_total' => 22,
            'room_total' => 2,
        ]);
    }

    public function test_unmatched_schoolmis_rows_are_not_imported(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createAcademicYear('2572');
        $this->createSchool('86010005', 'โรงเรียนห้า');

        $file = UploadedFile::fake()->createWithContent(
            'mixed-schoolmis.csv',
            $this->makeSchoolmisCsv([
                ['2572-1', '86010005', $this->measureSet(6, 7, 13, 1)],
                ['2572-1', '99999999', $this->measureSet(3, 4, 7, 1)],
            ])
        );

        $preview = $this->actingAs($admin)->post('/admin/schoolmis/preview', [
            'academic_year' => '2572',
            'term' => 1,
            'csv' => $file,
        ]);

        $preview->assertOk()
            ->assertJson([
                'status' => 'success',
                'preview' => [
                    'valid_rows' => 2,
                    'unmatched_rows' => 1,
                ],
            ]);

        $this->actingAs($admin)->postJson('/admin/schoolmis/import', [
            'academic_year' => '2572',
            'term' => 1,
            'mode' => 'replace',
            'upload_token' => $preview->json('upload_token'),
            'source_filename' => 'mixed-schoolmis.csv',
        ])->assertOk()
            ->assertJson([
                'status' => 'success',
                'imported_rows' => 1,
            ]);

        $this->assertDatabaseHas('schoolmis_records', [
            'academic_year' => '2572',
            'term' => 1,
            'school_smis' => '86010005',
            'student_total' => 13,
        ]);

        $this->assertDatabaseMissing('schoolmis_records', [
            'academic_year' => '2572',
            'term' => 1,
            'school_smis' => '99999999',
        ]);

        $this->assertDatabaseHas('schoolmis_imports', [
            'academic_year' => '2572',
            'term' => 1,
            'valid_rows' => 2,
            'imported_rows' => 1,
            'unmatched_rows' => 1,
        ]);
    }

    public function test_replace_mode_replaces_existing_schoolmis_records_for_same_year_and_term(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createAcademicYear('2570');
        $this->createSchool('86010001', 'โรงเรียนหนึ่ง');
        $this->createSchool('86010002', 'โรงเรียนสอง');

        $firstFile = UploadedFile::fake()->createWithContent(
            'first.csv',
            $this->makeSchoolmisCsv([
                ['2570-2', '86010001', $this->measureSet(5, 7, 12, 1)],
            ])
        );

        $firstPreview = $this->actingAs($admin)->post('/admin/schoolmis/preview', [
            'academic_year' => '2570',
            'term' => 2,
            'csv' => $firstFile,
        ]);

        $this->actingAs($admin)->postJson('/admin/schoolmis/import', [
            'academic_year' => '2570',
            'term' => 2,
            'mode' => 'replace',
            'upload_token' => $firstPreview->json('upload_token'),
            'source_filename' => 'first.csv',
        ])->assertOk();

        $secondFile = UploadedFile::fake()->createWithContent(
            'second.csv',
            $this->makeSchoolmisCsv([
                ['2570-2', '86010002', $this->measureSet(8, 9, 17, 1)],
            ])
        );

        $secondPreview = $this->actingAs($admin)->post('/admin/schoolmis/preview', [
            'academic_year' => '2570',
            'term' => 2,
            'csv' => $secondFile,
        ]);

        $this->actingAs($admin)->postJson('/admin/schoolmis/import', [
            'academic_year' => '2570',
            'term' => 2,
            'mode' => 'replace',
            'upload_token' => $secondPreview->json('upload_token'),
            'source_filename' => 'second.csv',
        ])->assertOk();

        $this->assertDatabaseMissing('schoolmis_records', [
            'academic_year' => '2570',
            'term' => 2,
            'school_smis' => '86010001',
        ]);

        $this->assertDatabaseHas('schoolmis_records', [
            'academic_year' => '2570',
            'term' => 2,
            'school_smis' => '86010002',
            'student_total' => 17,
        ]);
    }

    public function test_admin_can_delete_schoolmis_data_set_by_year_and_term(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createAcademicYear('2571');
        $this->createSchool('86010003', 'โรงเรียนสาม');

        DB::table('schoolmis_imports')->insert([
            'academic_year_id' => (int) DB::table('academic_years')->where('year', '2571')->value('id'),
            'academic_year' => '2571',
            'term' => 3,
            'source_filename' => 'delete-me.csv',
            'stored_filename' => 'delete-me.csv',
            'schema_version' => '82',
            'total_rows' => 1,
            'valid_rows' => 1,
            'imported_rows' => 1,
            'unmatched_rows' => 0,
            'invalid_rows' => 0,
            'mode' => 'replace',
            'created_by' => $admin->id,
            'warnings' => json_encode([], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('schoolmis_records')->insert([
            'import_id' => 1,
            'academic_year_id' => (int) DB::table('academic_years')->where('year', '2571')->value('id'),
            'academic_year' => '2571',
            'term' => 3,
            'school_id' => (int) DB::table('system_school')->where('smis', '86010003')->value('id'),
            'school_smis' => '86010003',
            'schema_version' => '82',
            'raw_year_term' => '2571-3',
            'male_total' => 4,
            'female_total' => 5,
            'student_total' => 9,
            'room_total' => 1,
            'metrics' => json_encode(['all_total' => ['male' => 4, 'female' => 5, 'total' => 9, 'rooms' => 1]], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->deleteJson('/admin/schoolmis/data-set', [
            'academic_year' => '2571',
            'term' => 3,
        ])->assertOk()
            ->assertJson([
                'status' => 'success',
                'deleted_records' => 1,
                'deleted_imports' => 1,
            ]);

        $this->assertDatabaseMissing('schoolmis_records', [
            'academic_year' => '2571',
            'term' => 3,
            'school_smis' => '86010003',
        ]);

        $this->assertDatabaseMissing('schoolmis_imports', [
            'academic_year' => '2571',
            'term' => 3,
        ]);
    }

    private function createAcademicYear(string $year): void
    {
        DB::table('academic_years')->updateOrInsert(
            ['year' => $year],
            [
                'name' => 'ปีการศึกษา ' . $year,
                'is_active' => true,
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
            'statusID' => '1',
            'statusDetail' => 'เปิด',
        ]);
    }

    private function makeSchoolmisCsv(array $rows): string
    {
        return implode("\n", array_map(function ($row) {
            return implode(',', array_merge([$row[0], $row[1]], $row[2]));
        }, $rows));
    }

    private function measureSet(int $maleTotal, int $femaleTotal, int $studentTotal, int $roomTotal): array
    {
        $groups = array_fill(0, 19, [0, 0, 0, 0]);
        $groups[] = [$maleTotal, $femaleTotal, $studentTotal, $roomTotal];

        return array_merge(...$groups);
    }
}
