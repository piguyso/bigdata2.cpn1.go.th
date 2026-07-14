<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminSchoolTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_school_management(): void
    {
        $this->get('/admin/schools')->assertRedirect('/login');
        $this->get('/admin/schools/data')->assertRedirect('/login');
        $this->post('/admin/schools/save', [])->assertRedirect('/login');
        $this->delete('/admin/schools/1')->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_school_management(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get('/admin/schools')->assertRedirect('/');
        $this->actingAs($user)->get('/admin/schools/data')->assertRedirect('/');
        $this->actingAs($user)->post('/admin/schools/save', [])->assertRedirect('/');
        $this->actingAs($user)->delete('/admin/schools/1')->assertRedirect('/');
    }

    public function test_admin_can_access_school_index_and_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createGroup();

        DB::table('system_school')->insert($this->schoolRow([
            'smis' => '86010001',
            'schoolname' => 'โรงเรียนทดสอบ',
        ]));

        $this->actingAs($admin)->get('/admin/schools')->assertOk();

        $this->actingAs($admin)->getJson('/admin/schools/data')
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'smis',
                        'schoolname',
                        'schoolgroup',
                        'schoolgroup_name',
                        'tambon',
                        'amper',
                        'tel',
                        'email',
                        'website',
                    ],
                ],
                'groups',
            ]);
    }

    public function test_admin_can_create_new_school(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createGroup();

        $payload = [
            'smis' => '86010002',
            'schoolname' => 'โรงเรียนใหม่',
            'schoolgroup' => '01',
            'tambon' => 'ท่าตะเภา',
            'amper' => 'เมืองชุมพร',
            'email' => 'school@example.test',
        ];

        $this->actingAs($admin)->postJson('/admin/schools/save', $payload)
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'เพิ่มข้อมูลโรงเรียนเรียบร้อยแล้ว',
            ]);

        $this->assertDatabaseHas('system_school', [
            'smis' => '86010002',
            'schoolname' => 'โรงเรียนใหม่',
            'schoolgroup' => '01',
            'province' => 'ชุมพร',
        ]);
    }

    public function test_admin_can_update_existing_school(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createGroup();

        $id = DB::table('system_school')->insertGetId($this->schoolRow([
            'smis' => '86010003',
            'schoolname' => 'ชื่อเดิม',
        ]));

        $payload = [
            'id' => $id,
            'smis' => '86010003',
            'schoolname' => 'ชื่อใหม่',
            'schoolgroup' => '01',
            'tel' => '077000000',
        ];

        $this->actingAs($admin)->postJson('/admin/schools/save', $payload)
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'แก้ไขข้อมูลโรงเรียนเรียบร้อยแล้ว',
            ]);

        $this->assertDatabaseHas('system_school', [
            'id' => $id,
            'schoolname' => 'ชื่อใหม่',
            'tel' => '077000000',
        ]);
    }

    public function test_school_creation_validation_requirements(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->postJson('/admin/schools/save', [
            'smis' => '',
            'schoolname' => '',
            'schoolgroup' => '99',
            'email' => 'not-email',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['smis', 'schoolname', 'schoolgroup', 'email']);
    }

    public function test_admin_can_delete_school(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createGroup();

        $id = DB::table('system_school')->insertGetId($this->schoolRow([
            'smis' => '86010004',
            'schoolname' => 'โรงเรียนที่จะลบ',
        ]));

        $this->actingAs($admin)->deleteJson("/admin/schools/{$id}")
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'ลบข้อมูลโรงเรียนเรียบร้อยแล้ว',
            ]);

        $this->assertDatabaseMissing('system_school', ['id' => $id]);
    }

    private function createGroup(array $overrides = []): void
    {
        DB::table('system_group')->insert(array_merge([
            'code' => '01',
            'name' => 'เมืองชุมพร 1',
        ], $overrides));
    }

    private function schoolRow(array $overrides = []): array
    {
        return array_merge([
            'smis' => '86010000',
            'percode' => '',
            'ministry' => '',
            'schoolname' => 'โรงเรียนตัวอย่าง',
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
        ], $overrides);
    }
}
