<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_dashboard_is_publicly_accessible(): void
    {
        $this->get('/')->assertOk();
        $this->get('/dashboard')->assertRedirect('/');
    }

    public function test_dashboard_stats_show_schoolmis_information(): void
    {
        $academicYearId = $this->seedAcademicYear();
        $this->seedSchool('86010001', 'โรงเรียน A', '01', 'เมืองชุมพร 1', 'เมืองชุมพร');
        $this->seedSchool('86010002', 'โรงเรียน B', '02', 'ท่าแซะ 1', 'ท่าแซะ');
        $this->seedSchool('86010003', 'โรงเรียน C', '03', 'ปะทิว 1', 'ปะทิว');
        $user = User::factory()->create(['role' => 'admin']);

        DB::table('schoolmis_imports')->insert([
            'academic_year_id' => $academicYearId,
            'academic_year' => '2569',
            'term' => 1,
            'source_filename' => 'test.csv',
            'stored_filename' => 'test.csv',
            'schema_version' => '82',
            'total_rows' => 3,
            'valid_rows' => 3,
            'imported_rows' => 3,
            'unmatched_rows' => 0,
            'invalid_rows' => 0,
            'mode' => 'replace',
            'created_by' => $user->id,
            'warnings' => json_encode([], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('schoolmis_records')->insert([
            [
                'import_id' => 1,
                'academic_year_id' => $academicYearId,
                'academic_year' => '2569',
                'term' => 1,
                'school_id' => 1,
                'school_smis' => '86010001',
                'schema_version' => '82',
                'raw_year_term' => '2569-1',
                'male_total' => 100,
                'female_total' => 120,
                'student_total' => 220,
                'room_total' => 10,
                'metrics' => json_encode([
                    'pre_primary_total' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'primary_total' => ['male' => 80, 'female' => 90, 'total' => 170, 'rooms' => 7],
                    'lower_secondary_total' => ['male' => 20, 'female' => 25, 'total' => 45, 'rooms' => 2],
                    'upper_secondary_total' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'm3' => ['male' => 12, 'female' => 13, 'total' => 25, 'rooms' => 1],
                    'm4' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'm5' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'm6' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'all_total' => ['male' => 100, 'female' => 120, 'total' => 220, 'rooms' => 10],
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'import_id' => 1,
                'academic_year_id' => $academicYearId,
                'academic_year' => '2569',
                'term' => 1,
                'school_id' => 2,
                'school_smis' => '86010002',
                'schema_version' => '82',
                'raw_year_term' => '2569-1',
                'male_total' => 80,
                'female_total' => 90,
                'student_total' => 170,
                'room_total' => 8,
                'metrics' => json_encode([
                    'pre_primary_total' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'primary_total' => ['male' => 60, 'female' => 65, 'total' => 125, 'rooms' => 5],
                    'lower_secondary_total' => ['male' => 20, 'female' => 25, 'total' => 45, 'rooms' => 2],
                    'upper_secondary_total' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'm3' => ['male' => 10, 'female' => 12, 'total' => 22, 'rooms' => 1],
                    'm4' => ['male' => 8, 'female' => 9, 'total' => 17, 'rooms' => 1],
                    'm5' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'm6' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'all_total' => ['male' => 80, 'female' => 90, 'total' => 170, 'rooms' => 8],
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'import_id' => 1,
                'academic_year_id' => $academicYearId,
                'academic_year' => '2569',
                'term' => 1,
                'school_id' => 3,
                'school_smis' => '86010003',
                'schema_version' => '82',
                'raw_year_term' => '2569-1',
                'male_total' => 40,
                'female_total' => 50,
                'student_total' => 90,
                'room_total' => 4,
                'metrics' => json_encode([
                    'pre_primary_total' => ['male' => 10, 'female' => 12, 'total' => 22, 'rooms' => 1],
                    'primary_total' => ['male' => 30, 'female' => 38, 'total' => 68, 'rooms' => 3],
                    'lower_secondary_total' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'upper_secondary_total' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'm3' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'm4' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'm5' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'm6' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'all_total' => ['male' => 40, 'female' => 50, 'total' => 90, 'rooms' => 4],
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'import_id' => 1,
                'academic_year_id' => $academicYearId,
                'academic_year' => '2569',
                'term' => 2,
                'school_id' => 1,
                'school_smis' => '86010001',
                'schema_version' => '82',
                'raw_year_term' => '2569-2',
                'male_total' => 90,
                'female_total' => 95,
                'student_total' => 185,
                'room_total' => 9,
                'metrics' => json_encode([
                    'pre_primary_total' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'primary_total' => ['male' => 70, 'female' => 75, 'total' => 145, 'rooms' => 6],
                    'lower_secondary_total' => ['male' => 20, 'female' => 20, 'total' => 40, 'rooms' => 3],
                    'upper_secondary_total' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'all_total' => ['male' => 90, 'female' => 95, 'total' => 185, 'rooms' => 9],
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->getJson('/api/dashboard/stats?academic_year=2569&term=1')
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'selectedYear' => '2569',
                    'selectedTerm' => 1,
                    'overview' => [
                        'schoolsCount' => 3,
                        'opportunitySchoolsCount' => 2,
                        'studentTotal' => 480,
                        'roomTotal' => 22,
                        'maleTotal' => 220,
                        'femaleTotal' => 260,
                        'schoolSizeSummary' => [
                            'small' => 1,
                            'medium' => 2,
                            'large' => 0,
                            'special' => 0,
                        ],
                        'opportunitySchoolSizeSummary' => [
                            'small' => 0,
                            'medium' => 2,
                            'large' => 0,
                            'special' => 0,
                        ],
                    ],
                ],
            ])
            ->assertJsonPath('data.networkSummary.0.label', 'เมืองชุมพร 1')
            ->assertJsonPath('data.networkSummary.1.label', 'ท่าแซะ 1')
            ->assertJsonPath('data.networkSummary.2.label', 'ปะทิว 1')
            ->assertJsonPath('data.networkSummary.0.sizeSummary.medium', 1)
            ->assertJsonPath('data.networkSummary.1.sizeSummary.medium', 1)
            ->assertJsonPath('data.networkSummary.2.sizeSummary.small', 1)
            ->assertJsonPath('data.districtSummary.0.label', 'เมืองชุมพร')
            ->assertJsonPath('data.districtSummary.0.sizeSummary.medium', 1)
            ->assertJsonPath('data.districtSummary.0.rooms', 10)
            ->assertJsonPath('data.genderTrend.0.label', '2569 / 1')
            ->assertJsonPath('data.genderTrend.0.male_total', 220)
            ->assertJsonPath('data.genderTrend.0.female_total', 260)
            ->assertJsonPath('data.genderTrend.1.label', '2569 / 2')
            ->assertJsonPath('data.genderTrend.1.student_total', 185)
            ->assertJsonPath('data.levelSummary.0.key', 'pre_primary_total')
            ->assertJsonPath('data.levelSummary.1.key', 'primary_total')
            ->assertJsonPath('data.levelSummary.4.key', 'all_total');
    }

    public function test_school_size_page_is_publicly_accessible(): void
    {
        $this->get('/schools?academic_year=2569&term=1')
            ->assertOk()
            ->assertSee('โรงเรียนทั้งหมด');

        $this->get('/schools/size/medium?academic_year=2569&term=1')
            ->assertOk()
            ->assertSee('โรงเรียนขนาดกลาง');

        $this->get('/schools/opportunity?academic_year=2569&term=1')
            ->assertOk()
            ->assertSee('โรงเรียนขยายโอกาส');

        $this->get('/schools/opportunity/size/medium?academic_year=2569&term=1')
            ->assertOk()
            ->assertSee('โรงเรียนขยายโอกาสขนาดกลาง');

        $this->get('/schools/network/'.urlencode('เมืองชุมพร 1').'?academic_year=2569&term=1')
            ->assertOk()
            ->assertSee('โรงเรียนในเครือข่ายเมืองชุมพร 1');

        $this->get('/schools/network/'.urlencode('เมืองชุมพร 1').'/size/medium?academic_year=2569&term=1')
            ->assertOk()
            ->assertSee('โรงเรียนเครือข่ายเมืองชุมพร 1 ขนาดกลาง');

        $this->get('/schools/district/'.urlencode('เมืองชุมพร').'?academic_year=2569&term=1')
            ->assertOk()
            ->assertSee('โรงเรียนในอำเภอเมืองชุมพร');

        $this->get('/schools/district/'.urlencode('เมืองชุมพร').'/size/medium?academic_year=2569&term=1')
            ->assertOk()
            ->assertSee('โรงเรียนอำเภอเมืองชุมพร ขนาดกลาง');
    }

    public function test_dashboard_drilldown_can_filter_by_school_size(): void
    {
        $academicYearId = $this->seedAcademicYear();
        $this->seedSchool('86010001', 'โรงเรียน A', '01', 'เมืองชุมพร 1', 'เมืองชุมพร');
        $this->seedSchool('86010002', 'โรงเรียน B', '02', 'ท่าแซะ 1', 'ท่าแซะ');
        $user = User::factory()->create(['role' => 'admin']);

        DB::table('schoolmis_imports')->insert([
            'academic_year_id' => $academicYearId,
            'academic_year' => '2569',
            'term' => 1,
            'source_filename' => 'test.csv',
            'stored_filename' => 'test.csv',
            'schema_version' => '82',
            'total_rows' => 2,
            'valid_rows' => 2,
            'imported_rows' => 2,
            'unmatched_rows' => 0,
            'invalid_rows' => 0,
            'mode' => 'replace',
            'created_by' => $user->id,
            'warnings' => json_encode([], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('schoolmis_records')->insert([
            [
                'import_id' => 1,
                'academic_year_id' => $academicYearId,
                'academic_year' => '2569',
                'term' => 1,
                'school_id' => 1,
                'school_smis' => '86010001',
                'schema_version' => '82',
                'raw_year_term' => '2569-1',
                'male_total' => 100,
                'female_total' => 120,
                'student_total' => 220,
                'room_total' => 10,
                'metrics' => json_encode([
                    'pre_primary_total' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'primary_total' => ['male' => 80, 'female' => 90, 'total' => 170, 'rooms' => 7],
                    'lower_secondary_total' => ['male' => 20, 'female' => 25, 'total' => 45, 'rooms' => 2],
                    'upper_secondary_total' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'm3' => ['male' => 12, 'female' => 13, 'total' => 25, 'rooms' => 1],
                    'm4' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'm5' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'm6' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
                    'all_total' => ['male' => 100, 'female' => 120, 'total' => 220, 'rooms' => 10],
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'import_id' => 1,
                'academic_year_id' => $academicYearId,
                'academic_year' => '2569',
                'term' => 1,
                'school_id' => 2,
                'school_smis' => '86010002',
                'schema_version' => '82',
                'raw_year_term' => '2569-1',
                'male_total' => 15,
                'female_total' => 20,
                'student_total' => 35,
                'room_total' => 2,
                'metrics' => $this->metricsJson(15, 20, 35, 2, 15, 20, 35, 2, 0, 0, 0, 0, 0, 0, 0, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->getJson('/api/dashboard/drilldown?academic_year=2569&term=1&type=school_size&value=medium')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'school_smis' => '86010001',
                'student_total' => 220,
            ]);

        $this->getJson('/api/dashboard/drilldown?academic_year=2569&term=1&type=all_schools&value=all')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'school_smis' => '86010002',
                'student_total' => 35,
            ]);

        $this->getJson('/api/dashboard/drilldown?academic_year=2569&term=1&type=opportunity_school_size&value=medium')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'school_smis' => '86010001',
                'student_total' => 220,
            ]);

        $this->getJson('/api/dashboard/drilldown?academic_year=2569&term=1&type=network_school_size&value='.urlencode('เมืองชุมพร 1||medium'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'school_smis' => '86010001',
                'student_total' => 220,
            ]);
    }

    public function test_school_trend_uses_latest_term_for_each_year(): void
    {
        $year2568Id = $this->seedAcademicYear('2568', false);
        $year2569Id = $this->seedAcademicYear('2569', true);
        $this->seedSchool('86010001', 'โรงเรียน A', '01', 'เมืองชุมพร 1', 'เมืองชุมพร');

        DB::table('schoolmis_records')->insert([
            [
                'import_id' => null,
                'academic_year_id' => $year2568Id,
                'academic_year' => '2568',
                'term' => 1,
                'school_id' => 1,
                'school_smis' => '86010001',
                'schema_version' => '82',
                'raw_year_term' => '2568-1',
                'male_total' => 40,
                'female_total' => 50,
                'student_total' => 90,
                'room_total' => 4,
                'metrics' => $this->metricsJson(40, 50, 90, 4, 40, 50, 90, 4, 0, 0, 0, 0, 0, 0, 0, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'import_id' => null,
                'academic_year_id' => $year2568Id,
                'academic_year' => '2568',
                'term' => 2,
                'school_id' => 1,
                'school_smis' => '86010001',
                'schema_version' => '82',
                'raw_year_term' => '2568-2',
                'male_total' => 50,
                'female_total' => 55,
                'student_total' => 105,
                'room_total' => 5,
                'metrics' => $this->metricsJson(50, 55, 105, 5, 50, 55, 105, 5, 0, 0, 0, 0, 0, 0, 0, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'import_id' => null,
                'academic_year_id' => $year2569Id,
                'academic_year' => '2569',
                'term' => 1,
                'school_id' => 1,
                'school_smis' => '86010001',
                'schema_version' => '82',
                'raw_year_term' => '2569-1',
                'male_total' => 60,
                'female_total' => 70,
                'student_total' => 130,
                'room_total' => 6,
                'metrics' => $this->metricsJson(60, 70, 130, 6, 60, 70, 130, 6, 0, 0, 0, 0, 0, 0, 0, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->getJson('/api/dashboard/school-trend?school_smis=86010001')
            ->assertOk()
            ->assertJsonCount(2, 'data.points')
            ->assertJsonPath('data.points.0.academic_year', '2568')
            ->assertJsonPath('data.points.0.term', 2)
            ->assertJsonPath('data.points.0.student_total', 105)
            ->assertJsonPath('data.summary.change', 25)
            ->assertJsonPath('data.summary.changePercent', 23.8);
    }

    public function test_student_trend_returns_each_year_and_term_summary(): void
    {
        $year2568Id = $this->seedAcademicYear('2568', false);
        $year2569Id = $this->seedAcademicYear('2569', true);
        $this->seedSchool('86010001', 'โรงเรียน A', '01', 'เมืองชุมพร 1', 'เมืองชุมพร');
        $this->seedSchool('86010002', 'โรงเรียน B', '02', 'ท่าแซะ 1', 'ท่าแซะ');

        DB::table('schoolmis_records')->insert([
            [
                'import_id' => null,
                'academic_year_id' => $year2568Id,
                'academic_year' => '2568',
                'term' => 1,
                'school_id' => 1,
                'school_smis' => '86010001',
                'schema_version' => '82',
                'raw_year_term' => '2568-1',
                'male_total' => 40,
                'female_total' => 45,
                'student_total' => 85,
                'room_total' => 4,
                'metrics' => $this->metricsJson(40, 45, 85, 4, 40, 45, 85, 4, 0, 0, 0, 0, 0, 0, 0, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'import_id' => null,
                'academic_year_id' => $year2568Id,
                'academic_year' => '2568',
                'term' => 2,
                'school_id' => 2,
                'school_smis' => '86010002',
                'schema_version' => '82',
                'raw_year_term' => '2568-2',
                'male_total' => 50,
                'female_total' => 55,
                'student_total' => 105,
                'room_total' => 5,
                'metrics' => $this->metricsJson(50, 55, 105, 5, 50, 55, 105, 5, 0, 0, 0, 0, 0, 0, 0, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'import_id' => null,
                'academic_year_id' => $year2569Id,
                'academic_year' => '2569',
                'term' => 1,
                'school_id' => 1,
                'school_smis' => '86010001',
                'schema_version' => '82',
                'raw_year_term' => '2569-1',
                'male_total' => 60,
                'female_total' => 70,
                'student_total' => 130,
                'room_total' => 6,
                'metrics' => $this->metricsJson(60, 70, 130, 6, 60, 70, 130, 6, 0, 0, 0, 0, 0, 0, 0, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'import_id' => null,
                'academic_year_id' => $year2569Id,
                'academic_year' => '2569',
                'term' => 1,
                'school_id' => 2,
                'school_smis' => '86010002',
                'schema_version' => '82',
                'raw_year_term' => '2569-1',
                'male_total' => 55,
                'female_total' => 60,
                'student_total' => 115,
                'room_total' => 5,
                'metrics' => $this->metricsJson(55, 60, 115, 5, 55, 60, 115, 5, 0, 0, 0, 0, 0, 0, 0, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->getJson('/api/dashboard/student-trend')
            ->assertOk()
            ->assertJsonCount(3, 'data.points')
            ->assertJsonPath('data.points.0.academic_year', '2568')
            ->assertJsonPath('data.points.0.term', 1)
            ->assertJsonPath('data.points.1.term', 2)
            ->assertJsonPath('data.points.2.student_total', 245)
            ->assertJsonPath('data.points.2.schools_count', 2)
            ->assertJsonPath('data.summary.first.label', '2568 / 1')
            ->assertJsonPath('data.summary.latest.label', '2569 / 1')
            ->assertJsonPath('data.summary.change', 160)
            ->assertJsonPath('data.summary.changePercent', 188.2);
    }

    public function test_school_student_detail_returns_grade_rows_and_summary(): void
    {
        $academicYearId = $this->seedAcademicYear();
        $this->seedSchool('86010001', 'โรงเรียน A', '01', 'เมืองชุมพร 1', 'เมืองชุมพร');

        DB::table('schoolmis_records')->insert([
            'import_id' => null,
            'academic_year_id' => $academicYearId,
            'academic_year' => '2569',
            'term' => 1,
            'school_id' => 1,
            'school_smis' => '86010001',
            'schema_version' => '82',
            'raw_year_term' => '2569-1',
            'male_total' => 12,
            'female_total' => 15,
            'student_total' => 27,
            'room_total' => 2,
            'metrics' => json_encode([
                'k1' => ['male' => 2, 'female' => 3, 'total' => 5, 'rooms' => 1],
                'p1' => ['male' => 10, 'female' => 12, 'total' => 22, 'rooms' => 1],
                'pre_primary_total' => ['male' => 2, 'female' => 3, 'total' => 5, 'rooms' => 1],
                'primary_total' => ['male' => 10, 'female' => 12, 'total' => 22, 'rooms' => 1],
                'all_total' => ['male' => 12, 'female' => 15, 'total' => 27, 'rooms' => 2],
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/dashboard/school-student-detail?school_smis=86010001&academic_year=2569&term=1')
            ->assertOk()
            ->assertJsonPath('data.school.schoolname', 'โรงเรียน A')
            ->assertJsonPath('data.gradeRows.0.label', 'อนุบาล 1')
            ->assertJsonPath('data.gradeRows.0.male', 2)
            ->assertJsonPath('data.gradeRows.1.label', 'ประถมศึกษาปีที่ 1')
            ->assertJsonPath('data.gradeRows.1.total', 22)
            ->assertJsonPath('data.summary.rooms', 2)
            ->assertJsonPath('data.summary.male', 12)
            ->assertJsonPath('data.summary.female', 15)
            ->assertJsonPath('data.summary.total', 27);
    }

    public function test_level_trend_returns_each_year_and_term_summary(): void
    {
        $year2568Id = $this->seedAcademicYear('2568');
        $year2569Id = $this->seedAcademicYear('2569');
        $this->seedSchool('86010001', 'โรงเรียน A', '01', 'เมืองชุมพร 1', 'เมืองชุมพร');
        $this->seedSchool('86010002', 'โรงเรียน B', '02', 'ท่าแซะ 1', 'ท่าแซะ');

        DB::table('schoolmis_records')->insert([
            [
                'import_id' => null,
                'academic_year_id' => $year2568Id,
                'academic_year' => '2568',
                'term' => 1,
                'school_id' => 1,
                'school_smis' => '86010001',
                'schema_version' => '82',
                'raw_year_term' => '2568-1',
                'male_total' => 40,
                'female_total' => 50,
                'student_total' => 90,
                'room_total' => 4,
                'metrics' => $this->metricsJson(40, 50, 90, 4, 30, 40, 70, 3, 10, 10, 20, 1, 0, 0, 0, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'import_id' => null,
                'academic_year_id' => $year2568Id,
                'academic_year' => '2568',
                'term' => 2,
                'school_id' => 1,
                'school_smis' => '86010001',
                'schema_version' => '82',
                'raw_year_term' => '2568-2',
                'male_total' => 44,
                'female_total' => 56,
                'student_total' => 100,
                'room_total' => 5,
                'metrics' => $this->metricsJson(44, 56, 100, 5, 32, 44, 76, 3, 12, 12, 24, 2, 0, 0, 0, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'import_id' => null,
                'academic_year_id' => $year2569Id,
                'academic_year' => '2569',
                'term' => 1,
                'school_id' => 1,
                'school_smis' => '86010001',
                'schema_version' => '82',
                'raw_year_term' => '2569-1',
                'male_total' => 50,
                'female_total' => 60,
                'student_total' => 110,
                'room_total' => 5,
                'metrics' => $this->metricsJson(50, 60, 110, 5, 35, 45, 80, 3, 15, 15, 30, 2, 0, 0, 0, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'import_id' => null,
                'academic_year_id' => $year2569Id,
                'academic_year' => '2569',
                'term' => 1,
                'school_id' => 2,
                'school_smis' => '86010002',
                'schema_version' => '82',
                'raw_year_term' => '2569-1',
                'male_total' => 45,
                'female_total' => 55,
                'student_total' => 100,
                'room_total' => 4,
                'metrics' => $this->metricsJson(45, 55, 100, 4, 30, 40, 70, 3, 15, 15, 30, 1, 0, 0, 0, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->getJson('/api/dashboard/level-trend?level=primary_total')
            ->assertOk()
            ->assertJsonPath('data.level', 'primary_total')
            ->assertJsonPath('data.levelLabel', 'ประถมศึกษา')
            ->assertJsonCount(3, 'data.points')
            ->assertJsonPath('data.points.0.academic_year', '2568')
            ->assertJsonPath('data.points.0.term', 1)
            ->assertJsonPath('data.points.0.student_total', 70)
            ->assertJsonPath('data.points.1.term', 2)
            ->assertJsonPath('data.points.1.student_total', 76)
            ->assertJsonPath('data.points.2.student_total', 150)
            ->assertJsonPath('data.points.2.schools_count', 2)
            ->assertJsonPath('data.summary.first.label', '2568 / 1')
            ->assertJsonPath('data.summary.latest.label', '2569 / 1')
            ->assertJsonPath('data.summary.change', 80)
            ->assertJsonPath('data.summary.changePercent', 114.3);
    }

    public function test_school_info_returns_profile_and_map_link(): void
    {
        Http::fake([
            'https://router.project-osrm.org/*' => Http::response([
                'code' => 'Ok',
                'routes' => [
                    ['distance' => 12345.0],
                ],
            ], 200),
        ]);

        $this->seedSchool('86010001', 'โรงเรียน A', '01', 'เมืองชุมพร 1', 'เมืองชุมพร', [
            'percode' => '110001',
            'schoolname_eng' => 'School A',
            'muban' => '5',
            'tambon' => 'ท่าตะเภา',
            'postcode' => '86000',
            'lat' => '10.50',
            'lng' => '99.10',
            'tel' => '077000000',
            'email' => 'school-a@example.test',
            'website' => 'school-a.example.test',
        ]);

        $this->getJson('/api/dashboard/school-info?school_smis=86010001')
            ->assertOk()
            ->assertJsonPath('data.schoolname', 'โรงเรียน A')
            ->assertJsonPath('data.schoolname_eng', 'School A')
            ->assertJsonPath('data.schoolgroup_name', 'เมืองชุมพร 1')
            ->assertJsonPath('data.lat', '10.50')
            ->assertJsonPath('data.lng', '99.10')
            ->assertJsonPath('data.length_km', '12.3')
            ->assertJsonPath('data.maplink', 'https://www.google.com/maps?q=10.50,99.10')
            ->assertJsonPath('data.website', 'school-a.example.test');

        $this->assertSame('12.3', DB::table('system_school')->where('smis', '86010001')->value('length_km'));
    }

    public function test_school_listing_can_export_xlsx(): void
    {
        $academicYearId = $this->seedAcademicYear();
        $this->seedSchool('86010001', 'โรงเรียน A', '01', 'เมืองชุมพร 1', 'เมืองชุมพร', [
            'percode' => '110001',
            'tambon' => 'ท่าตะเภา',
            'postcode' => '86000',
        ]);

        DB::table('schoolmis_records')->insert([
            'import_id' => null,
            'academic_year_id' => $academicYearId,
            'academic_year' => '2569',
            'term' => 1,
            'school_id' => 1,
            'school_smis' => '86010001',
            'schema_version' => '82',
            'raw_year_term' => '2569-1',
            'male_total' => 12,
            'female_total' => 15,
            'student_total' => 27,
            'room_total' => 2,
            'metrics' => json_encode([
                'k1' => ['male' => 2, 'female' => 3, 'total' => 5, 'rooms' => 1],
                'p1' => ['male' => 10, 'female' => 12, 'total' => 22, 'rooms' => 1],
                'pre_primary_total' => ['male' => 2, 'female' => 3, 'total' => 5, 'rooms' => 1],
                'primary_total' => ['male' => 10, 'female' => 12, 'total' => 22, 'rooms' => 1],
                'all_total' => ['male' => 12, 'female' => 15, 'total' => 27, 'rooms' => 2],
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/schools/export/xlsx?academic_year=2569&term=1&type=all_schools&value=all');

        $response->assertOk();
        $response->assertDownload('schools_2569_term1_all-schools.xlsx');

        $filePath = $response->baseResponse->getFile()->getPathname();
        $this->assertStringStartsWith('PK', file_get_contents($filePath));
    }

    private function seedAcademicYear(string $year = '2569', bool $active = true): int
    {
        DB::table('academic_years')->updateOrInsert(
            ['year' => $year],
            [
                'name' => 'ปีการศึกษา '.$year,
                'is_active' => $active,
                'sort_order' => (int) $year,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return (int) DB::table('academic_years')->where('year', $year)->value('id');
    }

    private function seedSchool(string $smis, string $name, string $groupCode, string $groupName, string $district, array $overrides = []): void
    {
        DB::table('system_group')->insertOrIgnore([
            'code' => $groupCode,
            'name' => $groupName,
        ]);

        DB::table('system_school')->insert(array_merge([
            'smis' => $smis,
            'percode' => '',
            'ministry' => '',
            'schoolname' => $name,
            'schoolname_eng' => '',
            'schoolgroup' => $groupCode,
            'muti' => '',
            'road' => '',
            'muban' => '',
            'tambon' => '',
            'amper' => $district,
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
        ], $overrides));
    }

    private function metricsJson(
        int $maleTotal,
        int $femaleTotal,
        int $studentTotal,
        int $roomTotal,
        int $primaryMale,
        int $primaryFemale,
        int $primaryTotal,
        int $primaryRooms,
        int $lowerMale,
        int $lowerFemale,
        int $lowerTotal,
        int $lowerRooms,
        int $upperMale,
        int $upperFemale,
        int $upperTotal,
        int $upperRooms
    ): string {
        return json_encode([
            'pre_primary_total' => ['male' => 0, 'female' => 0, 'total' => 0, 'rooms' => 0],
            'primary_total' => ['male' => $primaryMale, 'female' => $primaryFemale, 'total' => $primaryTotal, 'rooms' => $primaryRooms],
            'lower_secondary_total' => ['male' => $lowerMale, 'female' => $lowerFemale, 'total' => $lowerTotal, 'rooms' => $lowerRooms],
            'upper_secondary_total' => ['male' => $upperMale, 'female' => $upperFemale, 'total' => $upperTotal, 'rooms' => $upperRooms],
            'all_total' => ['male' => $maleTotal, 'female' => $femaleTotal, 'total' => $studentTotal, 'rooms' => $roomTotal],
        ], JSON_UNESCAPED_UNICODE);
    }
}
