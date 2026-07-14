<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SchoolGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_school_group_management(): void
    {
        $this->get('/admin/school-group')->assertRedirect('/login');
        $this->get('/admin/school-group/data')->assertRedirect('/login');
        $this->post('/admin/school-group/save', [])->assertRedirect('/login');
        $this->delete('/admin/school-group/1')->assertRedirect('/login');
    }

    public function test_admin_can_create_update_and_delete_school_group(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->postJson('/admin/school-group/save', [
            'code' => '01',
            'name' => 'เมืองชุมพร 1',
        ])->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'เพิ่มเครือข่ายสถานศึกษาเรียบร้อยแล้ว',
            ]);

        $id = DB::table('system_group')->where('code', '01')->value('id');

        $this->actingAs($admin)->postJson('/admin/school-group/save', [
            'id' => $id,
            'code' => '01',
            'name' => 'เมืองชุมพรหนึ่ง',
        ])->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'แก้ไขข้อมูลเครือข่ายสถานศึกษาเรียบร้อยแล้ว',
            ]);

        $this->assertDatabaseHas('system_group', [
            'id' => $id,
            'name' => 'เมืองชุมพรหนึ่ง',
        ]);

        $this->actingAs($admin)->deleteJson("/admin/school-group/{$id}")
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'ลบข้อมูลเครือข่ายสถานศึกษาเรียบร้อยแล้ว',
            ]);

        $this->assertDatabaseMissing('system_group', ['id' => $id]);
    }

    public function test_school_group_cannot_be_deleted_when_schools_use_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $id = DB::table('system_group')->insertGetId([
            'code' => '01',
            'name' => 'เมืองชุมพร 1',
        ]);

        DB::table('system_school')->insert([
            'smis' => '86010001',
            'percode' => '',
            'ministry' => '',
            'schoolname' => 'โรงเรียนในเครือข่าย',
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

        $this->actingAs($admin)->deleteJson("/admin/school-group/{$id}")
            ->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'ไม่สามารถลบได้ เนื่องจากยังมีโรงเรียนอยู่ในเครือข่ายนี้',
            ]);

        $this->assertDatabaseHas('system_group', ['id' => $id]);
    }
}
