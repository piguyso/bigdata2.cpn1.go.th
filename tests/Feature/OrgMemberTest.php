<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrgMemberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_guest_is_redirected_from_org_management(): void
    {
        $this->get('/admin/org')->assertRedirect('/login');
        $this->get('/admin/org/data')->assertRedirect('/login');
        $this->post('/admin/org/save', [])->assertRedirect('/login');
        $this->delete('/admin/org/1')->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_org_management(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get('/admin/org')->assertRedirect('/');
        $this->actingAs($user)->get('/admin/org/data')->assertRedirect('/');
        $this->actingAs($user)->post('/admin/org/save', [])->assertRedirect('/');
        $this->actingAs($user)->delete('/admin/org/1')->assertRedirect('/');
    }

    public function test_admin_can_access_org_index_and_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Seed a mock member
        DB::table('org_members')->insert([
            'name' => 'John Doe',
            'position' => 'Teacher',
            'role' => 'member',
            'committee' => 'executive',
            'role_title' => 'Director',
            'level' => 1,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Test admin access
        $this->actingAs($admin)->get('/admin/org')->assertOk();
        $this->actingAs($admin)->getJson('/admin/org/data')
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id', 'name', 'position', 'role', 'committee', 'role_title', 'level', 'sort_order', 'created_at'
                    ]
                ]
            ]);
    }

    public function test_admin_can_create_new_org_member(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = [
            'name' => 'Jane Smith',
            'position' => 'Coordinator',
            'role' => 'member',
            'committee' => 'academic',
            'role_title' => 'Secretary',
            'level' => 2,
            'sort_order' => 10,
        ];

        $response = $this->actingAs($admin)->postJson('/admin/org/save', $payload);
        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'เพิ่มบุคลากรในโครงสร้างเรียบร้อยแล้ว'
            ]);

        $this->assertDatabaseHas('org_members', [
            'name' => 'Jane Smith',
            'committee' => 'academic',
            'role_title' => 'Secretary',
            'level' => 2,
        ]);
    }

    public function test_admin_can_edit_org_member(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Seed a member
        $id = DB::table('org_members')->insertGetId([
            'name' => 'Old Name',
            'position' => 'Old Pos',
            'role' => 'member',
            'committee' => 'operations',
            'role_title' => 'Old Title',
            'level' => 1,
            'sort_order' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'id' => $id,
            'name' => 'New Name',
            'position' => 'New Pos',
            'role' => 'advisor',
            'committee' => 'finance',
            'role_title' => 'New Title',
            'level' => 3,
            'sort_order' => 12,
        ];

        $response = $this->actingAs($admin)->postJson('/admin/org/save', $payload);
        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'แก้ไขข้อมูลบุคลากรเรียบร้อยแล้ว'
            ]);

        $this->assertDatabaseHas('org_members', [
            'id' => $id,
            'name' => 'New Name',
            'committee' => 'finance',
            'role_title' => 'New Title',
            'role' => 'advisor',
            'level' => 3,
        ]);
    }

    public function test_admin_can_delete_org_member(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $id = DB::table('org_members')->insertGetId([
            'name' => 'To Delete',
            'position' => 'Staff',
            'role' => 'member',
            'level' => 1,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->deleteJson("/admin/org/{$id}");
        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'ลบข้อมูลบุคลากรเรียบร้อยแล้ว'
            ]);

        $this->assertDatabaseMissing('org_members', ['id' => $id]);
    }
}
