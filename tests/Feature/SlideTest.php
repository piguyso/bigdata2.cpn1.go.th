<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SlideTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed default settings and slides
        $this->artisan('db:seed');
    }

    public function test_guest_cannot_access_admin_slide_routes(): void
    {
        $response = $this->getJson(route('admin.slides.data'));
        $response->assertStatus(401);

        $response = $this->postJson(route('admin.slides.save'), []);
        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_access_admin_slide_routes(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($user)->getJson(route('admin.slides.data'));
        $response->assertRedirect('/');
    }

    public function test_admin_can_fetch_slides_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson(route('admin.slides.data'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'highlight',
                    'slogan',
                    'badge',
                    'image',
                    'image_url',
                    'link',
                    'btn_text',
                    'btn2_text',
                    'btn2_link',
                    'sort_order'
                ]
            ]
        ]);
    }

    public function test_admin_can_create_slide(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        // 1x1 Transparent PNG base64
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($admin)->postJson(route('admin.slides.save'), [
            'title' => 'Test Slide Title',
            'highlight' => 'Test Highlight',
            'slogan' => 'Test Slogan Description',
            'badge' => 'Test Badge',
            'link' => '#test',
            'btn_text' => 'Btn 1',
            'btn2_text' => 'Btn 2',
            'btn2_link' => '#btn2',
            'image_data' => $base64Image,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('slides', [
            'title' => 'Test Slide Title',
            'badge' => 'Test Badge',
        ]);

        // Check if file was saved in mock public storage
        $slide = DB::table('slides')->where('title', 'Test Slide Title')->first();
        Storage::disk('public')->assertExists($slide->image);
    }

    public function test_admin_can_update_slide(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $slideId = DB::table('slides')->insertGetId([
            'title' => 'Old Title',
            'image' => 'slides/old_image.png',
            'sort_order' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Mock old file
        Storage::disk('public')->put('slides/old_image.png', 'fake image content');

        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($admin)->postJson("/admin/slides/{$slideId}/save", [
            'title' => 'Updated Title',
            'image_data' => $base64Image,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('slides', [
            'id' => $slideId,
            'title' => 'Updated Title',
        ]);

        // Verify old image was deleted and new image exists
        Storage::disk('public')->assertMissing('slides/old_image.png');
        $updatedSlide = DB::table('slides')->where('id', $slideId)->first();
        Storage::disk('public')->assertExists($updatedSlide->image);
    }

    public function test_admin_can_delete_slide(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        
        $slideId = DB::table('slides')->insertGetId([
            'title' => 'Delete Me',
            'image' => 'slides/delete_me.png',
            'sort_order' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Storage::disk('public')->put('slides/delete_me.png', 'fake content');

        $response = $this->actingAs($admin)->deleteJson("/admin/slides/{$slideId}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('slides', ['id' => $slideId]);
        Storage::disk('public')->assertMissing('slides/delete_me.png');
    }

    public function test_admin_can_reorder_slides(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $slides = DB::table('slides')->orderBy('id')->get();
        $payload = [
            'orders' => [
                ['id' => $slides[0]->id, 'sort_order' => 3],
                ['id' => $slides[1]->id, 'sort_order' => 1],
                ['id' => $slides[2]->id, 'sort_order' => 2],
            ]
        ];

        $response = $this->actingAs($admin)->postJson(route('admin.slides.order'), $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('slides', ['id' => $slides[0]->id, 'sort_order' => 3]);
        $this->assertDatabaseHas('slides', ['id' => $slides[1]->id, 'sort_order' => 1]);
        $this->assertDatabaseHas('slides', ['id' => $slides[2]->id, 'sort_order' => 2]);
    }
}
