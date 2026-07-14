<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AcademicYearTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_academic_year_management(): void
    {
        $this->get('/admin/academic-years')->assertRedirect('/login');
        $this->get('/admin/academic-years/data')->assertRedirect('/login');
        $this->post('/admin/academic-years/save', [])->assertRedirect('/login');
        $this->post('/admin/academic-years/1/active')->assertRedirect('/login');
        $this->delete('/admin/academic-years/1')->assertRedirect('/login');
    }

    public function test_admin_can_create_update_and_set_active_academic_year(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->postJson('/admin/academic-years/save', [
            'year' => '2570',
            'name' => 'ปีการศึกษา 2570',
            'starts_at' => '2026-05-16',
            'ends_at' => '2027-03-31',
            'is_active' => true,
            'sort_order' => 2570,
        ])->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'เพิ่มปีการศึกษาเรียบร้อยแล้ว',
            ]);

        $id = DB::table('academic_years')->where('year', '2570')->value('id');

        $this->assertDatabaseHas('academic_years', [
            'id' => $id,
            'year' => '2570',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->postJson('/admin/academic-years/save', [
            'id' => $id,
            'year' => '2570',
            'name' => 'ปีการศึกษา 2570 (ปรับปรุง)',
            'is_active' => false,
            'sort_order' => 2570,
        ])->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'แก้ไขปีการศึกษาเรียบร้อยแล้ว',
            ]);

        $this->actingAs($admin)->postJson("/admin/academic-years/{$id}/active")
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'ตั้งปีการศึกษาปัจจุบันเรียบร้อยแล้ว',
            ]);
    }

    public function test_cannot_delete_active_or_used_academic_year(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $activeId = DB::table('academic_years')->insertGetId([
            'year' => '2570',
            'name' => 'ปีการศึกษา 2570',
            'is_active' => true,
            'sort_order' => 2570,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->deleteJson("/admin/academic-years/{$activeId}")
            ->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'ไม่สามารถลบปีการศึกษาปัจจุบันได้',
            ]);

        $usedId = DB::table('academic_years')->insertGetId([
            'year' => '2568',
            'name' => 'ปีการศึกษา 2568',
            'is_active' => false,
            'sort_order' => 2568,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('courses')->insert([
            'title' => 'หลักสูตรทดสอบ',
            'academic_year' => '2568',
            'status' => 'open',
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->deleteJson("/admin/academic-years/{$usedId}")
            ->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'ไม่สามารถลบได้ เนื่องจากมีข้อมูลหลักสูตรอยู่ในปีการศึกษานี้',
            ]);
    }

    public function test_public_can_fetch_academic_years(): void
    {
        DB::table('academic_years')->insert([
            'year' => '2570',
            'name' => 'ปีการศึกษา 2570',
            'is_active' => true,
            'sort_order' => 2570,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/academic-years')
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'active_year' => '2570',
            ]);
    }
}
