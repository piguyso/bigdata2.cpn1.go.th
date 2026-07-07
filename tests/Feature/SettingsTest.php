<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_guest_is_redirected_from_settings_routes(): void
    {
        $this->get('/admin/settings')->assertRedirect('/login');
        $this->get('/admin/settings/data')->assertRedirect('/login');
        $this->post('/admin/settings/save', [])->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_settings_routes(): void
    {
        $nonAdmin = User::factory()->create(['role' => 'user']);

        $this->actingAs($nonAdmin)->get('/admin/settings')->assertRedirect('/');
        $this->actingAs($nonAdmin)->get('/admin/settings/data')->assertRedirect('/');
        $this->actingAs($nonAdmin)->post('/admin/settings/save', [])->assertRedirect('/');
    }

    public function test_admin_can_access_settings_edit_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/settings');

        $response->assertOk();
    }

    public function test_admin_can_fetch_settings_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        DB::table('settings')->insert([
            ['key' => 'web_name', 'value' => 'Test Web Name'],
            ['key' => 'contact_email', 'value' => 'test@example.com'],
        ]);

        $response = $this->actingAs($admin)->getJson('/admin/settings/data');

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'web_name' => 'Test Web Name',
                    'contact_email' => 'test@example.com',
                ]
            ]);
    }

    public function test_admin_can_save_settings_data_without_logo(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = [
            'web_name' => 'New Web Name',
            'contact_email' => 'contact@test.com',
            'contact_phone' => '123456789',
            'contact_address' => '123 Test Rd',
            'stat_teachers' => '1,500+',
            'stat_schools' => '60+',
            'stat_districts' => '10+',
            'stat_courses' => '20+',
        ];

        $response = $this->actingAs($admin)->postJson('/admin/settings/save', $payload);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'บันทึกการตั้งค่าเว็บไซต์เรียบร้อยแล้ว',
                'web_name' => 'New Web Name'
            ]);

        $this->assertDatabaseHas('settings', ['key' => 'web_name', 'value' => 'New Web Name']);
        $this->assertDatabaseHas('settings', ['key' => 'contact_email', 'value' => 'contact@test.com']);
        $this->assertDatabaseHas('settings', ['key' => 'stat_teachers', 'value' => '1,500+']);
    }

    public function test_admin_saving_settings_requires_web_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = [
            'web_name' => '', // Required
        ];

        $response = $this->actingAs($admin)->postJson('/admin/settings/save', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['web_name']);
    }

    public function test_admin_can_save_settings_with_base64_logo(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // A valid 1x1 transparent PNG base64 string
        $base64Logo = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $payload = [
            'web_name' => 'Logo Test Site',
            'web_logo_data' => $base64Logo
        ];

        $response = $this->actingAs($admin)->postJson('/admin/settings/save', $payload);

        $response->assertOk();

        $logoPath = DB::table('settings')->where('key', 'web_logo')->value('value');
        $this->assertNotNull($logoPath);
        $this->assertStringStartsWith('web_logo/web_logo_', $logoPath);

        // Verify the file was saved
        Storage::disk('public')->assertExists($logoPath);
    }
}
