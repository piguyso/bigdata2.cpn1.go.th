<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NetworkSchoolTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

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

        // Insert mock school
        DB::table('network_schools')->insert([
            'name' => 'Test School',
            'district' => 'อำเภอเมืองชุมพร',
            'address' => '123 test address',
            'website' => 'http://test-school.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/schools');
        $response->assertOk();

        $dataResponse = $this->actingAs($admin)->getJson('/admin/schools/data');
        $dataResponse->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id', 'name', 'district', 'address', 'website', 'logo', 'created_at', 'updated_at'
                    ]
                ]
            ]);
    }

    public function test_public_user_can_fetch_schools_list(): void
    {
        DB::table('network_schools')->insert([
            'name' => 'Public School',
            'district' => 'อำเภอหลังสวน',
            'address' => 'Test address',
            'website' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/schools');
        $response->assertOk()
            ->assertJson([
                'status' => 'success'
            ]);
    }

    public function test_admin_can_create_new_school_without_logo(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = [
            'name' => 'New Academy',
            'district' => 'อำเภอหลังสวน',
            'address' => '456 main road',
            'website' => 'http://academy.ac.th',
        ];

        $response = $this->actingAs($admin)->postJson('/admin/schools/save', $payload);
        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'เพิ่มโรงเรียนเครือข่ายเรียบร้อยแล้ว'
            ]);

        $this->assertDatabaseHas('network_schools', [
            'name' => 'New Academy',
            'district' => 'อำเภอหลังสวน',
            'website' => 'http://academy.ac.th',
        ]);
    }

    public function test_admin_can_update_existing_school(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $id = DB::table('network_schools')->insertGetId([
            'name' => 'Old School Name',
            'district' => 'อำเภอเมืองชุมพร',
            'address' => 'Old Address',
            'website' => 'http://old.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'id' => $id,
            'name' => 'Updated School Name',
            'district' => 'อำเภอท่าแซะ',
            'address' => 'New Address',
            'website' => 'http://new.com',
        ];

        $response = $this->actingAs($admin)->postJson('/admin/schools/save', $payload);
        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'แก้ไขข้อมูลโรงเรียนเครือข่ายเรียบร้อยแล้ว'
            ]);

        $this->assertDatabaseHas('network_schools', [
            'id' => $id,
            'name' => 'Updated School Name',
            'district' => 'อำเภอท่าแซะ',
            'address' => 'New Address',
            'website' => 'http://new.com',
        ]);
    }

    public function test_school_creation_validation_requirements(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = [
            'name' => '', // Required
            'district' => 'Invalid District', // Must be in allowed list
            'website' => 'not-a-url', // Must be URL
        ];

        $response = $this->actingAs($admin)->postJson('/admin/schools/save', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'district', 'website']);
    }

    public function test_admin_can_create_school_with_base64_logo(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $base64Logo = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $payload = [
            'name' => 'School with Logo',
            'district' => 'อำเภอสวี',
            'logo_data' => $base64Logo,
        ];

        $response = $this->actingAs($admin)->postJson('/admin/schools/save', $payload);
        $response->assertOk();

        $logoPath = DB::table('network_schools')->where('name', 'School with Logo')->value('logo');
        $this->assertNotNull($logoPath);
        $this->assertStringStartsWith('school_logos/school_logo_', $logoPath);

        Storage::disk('public')->assertExists($logoPath);
    }

    public function test_admin_can_delete_school(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $id = DB::table('network_schools')->insertGetId([
            'name' => 'To Be Deleted',
            'district' => 'อำเภอสวี',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->deleteJson("/admin/schools/{$id}");
        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'ลบโรงเรียนเครือข่ายเรียบร้อยแล้ว'
            ]);

        $this->assertDatabaseMissing('network_schools', ['id' => $id]);
    }
}
