<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_guest_is_redirected_from_course_management(): void
    {
        $this->get('/admin/courses')->assertRedirect('/login');
        $this->get('/admin/courses/data')->assertRedirect('/login');
        $this->post('/admin/courses/save', [])->assertRedirect('/login');
        $this->delete('/admin/courses/1')->assertRedirect('/login');
    }

    public function test_non_admin_or_teacher_cannot_access_course_management(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get('/admin/courses')->assertRedirect('/');
        $this->actingAs($user)->get('/admin/courses/data')->assertRedirect('/');
        $this->actingAs($user)->post('/admin/courses/save', [])->assertRedirect('/');
        $this->actingAs($user)->delete('/admin/courses/1')->assertRedirect('/');
    }

    public function test_admin_and_teacher_can_access_course_index_and_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        // Seed a mock course
        DB::table('courses')->insert([
            'title' => 'Test Course',
            'status' => 'upcoming',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Test admin access
        $this->actingAs($admin)->get('/admin/courses')->assertOk();
        $this->actingAs($admin)->getJson('/admin/courses/data')
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id', 'title', 'cover_image', 'objectives', 'hours', 'status', 'created_at'
                    ]
                ]
            ]);

        // Test teacher access
        $this->actingAs($teacher)->get('/admin/courses')->assertOk();
        $this->actingAs($teacher)->getJson('/admin/courses/data')->assertOk();
    }

    public function test_public_user_can_fetch_courses_list(): void
    {
        DB::table('courses')->insert([
            'title' => 'Public Course',
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/courses');
        $response->assertOk()
            ->assertJson([
                'status' => 'success'
            ]);
    }

    public function test_admin_can_create_new_course_without_cover(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = [
            'title' => 'AI Training',
            'hours' => '12',
            'academic_year' => '2569',
            'status' => 'open',
            'location' => 'School computer lab',
        ];

        $response = $this->actingAs($admin)->postJson('/admin/courses/save', $payload);
        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'เพิ่มหลักสูตรอบรมเรียบร้อยแล้ว'
            ]);

        $this->assertDatabaseHas('courses', [
            'title' => 'AI Training',
            'hours' => '12',
            'academic_year' => '2569',
            'status' => 'open',
            'location' => 'School computer lab',
        ]);
    }

    public function test_admin_can_update_existing_course(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $id = DB::table('courses')->insertGetId([
            'title' => 'Old Title',
            'status' => 'upcoming',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'id' => $id,
            'title' => 'New Title',
            'hours' => '40',
            'status' => 'closed',
            'report_text' => 'This is a summary report',
        ];

        $response = $this->actingAs($teacher)->postJson('/admin/courses/save', $payload);
        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'แก้ไขข้อมูลหลักสูตรอบรมเรียบร้อยแล้ว'
            ]);

        $this->assertDatabaseHas('courses', [
            'id' => $id,
            'title' => 'New Title',
            'hours' => '40',
            'status' => 'closed',
            'report_text' => 'This is a summary report',
        ]);
    }

    public function test_course_creation_validation_requirements(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = [
            'title' => '', // Required
            'status' => 'invalid-status', // Must be in open, upcoming, ongoing, closed
            'registration_link' => 'not-a-url', // Must be URL
        ];

        $response = $this->actingAs($admin)->postJson('/admin/courses/save', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'status', 'registration_link']);
    }

    public function test_admin_can_create_course_with_base64_cover_and_report_images(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $payload = [
            'title' => 'Course with Media',
            'status' => 'closed',
            'cover_image_data' => $base64Image,
            'new_report_images' => [
                $base64Image,
                $base64Image
            ]
        ];

        $response = $this->actingAs($admin)->postJson('/admin/courses/save', $payload);
        $response->assertOk();

        $course = DB::table('courses')->where('title', 'Course with Media')->first();
        $this->assertNotNull($course);
        
        $this->assertNotNull($course->cover_image);
        $this->assertStringStartsWith('courses/covers/course_cover_', $course->cover_image);
        Storage::disk('public')->assertExists($course->cover_image);

        $reportImages = json_decode($course->report_images, true);
        $this->assertCount(2, $reportImages);
        foreach ($reportImages as $img) {
            $this->assertStringStartsWith('courses/reports/report_img_', $img);
            Storage::disk('public')->assertExists($img);
        }
    }

    public function test_admin_can_delete_course(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $id = DB::table('courses')->insertGetId([
            'title' => 'To Be Deleted',
            'status' => 'upcoming',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->deleteJson("/admin/courses/{$id}");
        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'ลบหลักสูตรอบรมเรียบร้อยแล้ว'
            ]);

        $this->assertDatabaseMissing('courses', ['id' => $id]);
    }

    public function test_courses_are_ordered_by_sort_order_then_id_desc(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Insert courses in specific order
        DB::table('courses')->insert([
            ['title' => 'Course A', 'status' => 'upcoming', 'sort_order' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Course B', 'status' => 'upcoming', 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Course C', 'status' => 'upcoming', 'sort_order' => 5, 'created_at' => now()->addMinutes(5), 'updated_at' => now()],
        ]);

        $response = $this->actingAs($admin)->getJson('/admin/courses/data');
        $response->assertOk();
        
        $data = $response->json('data');
        
        // Expected order:
        // 1. Course C (sort_order = 5, higher id/newest)
        // 2. Course B (sort_order = 5, lower id)
        // 3. Course A (sort_order = 10)
        $this->assertEquals('Course C', $data[0]['title']);
        $this->assertEquals('Course B', $data[1]['title']);
        $this->assertEquals('Course A', $data[2]['title']);
    }

    public function test_admin_can_create_course_with_report_files(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $base64File = 'data:application/pdf;base64,JVBERi0xLjQKJcFSg4K...'; // Mock base64 data URL

        $payload = [
            'title' => 'Course with Files',
            'status' => 'closed',
            'new_report_files' => [
                [
                    'name' => 'document1.pdf',
                    'data' => $base64File
                ],
                [
                    'name' => 'notes.docx',
                    'data' => $base64File
                ]
            ]
        ];

        $response = $this->actingAs($admin)->postJson('/admin/courses/save', $payload);
        $response->assertOk();

        $course = DB::table('courses')->where('title', 'Course with Files')->first();
        $this->assertNotNull($course);
        
        $reportFiles = json_decode($course->report_files, true);
        $this->assertCount(2, $reportFiles);
        
        $this->assertEquals('document1.pdf', $reportFiles[0]['name']);
        $this->assertStringStartsWith('courses/files/report_file_', $reportFiles[0]['path']);
        Storage::disk('public')->assertExists($reportFiles[0]['path']);

        $this->assertEquals('notes.docx', $reportFiles[1]['name']);
        $this->assertStringStartsWith('courses/files/report_file_', $reportFiles[1]['path']);
        Storage::disk('public')->assertExists($reportFiles[1]['path']);
    }

    public function test_admin_can_update_course_keeping_and_deleting_files(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Insert initial files
        Storage::disk('public')->put('courses/files/file1.pdf', 'content1');
        Storage::disk('public')->put('courses/files/file2.docx', 'content2');

        $initialFiles = [
            ['name' => 'file1.pdf', 'path' => 'courses/files/file1.pdf'],
            ['name' => 'file2.docx', 'path' => 'courses/files/file2.docx']
        ];

        $id = DB::table('courses')->insertGetId([
            'title' => 'Update Files Course',
            'status' => 'closed',
            'report_files' => json_encode($initialFiles),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Storage::disk('public')->assertExists('courses/files/file1.pdf');
        Storage::disk('public')->assertExists('courses/files/file2.docx');

        // Keep file1, delete file2, add file3
        $base64File = 'data:application/pdf;base64,JVBERi0xLjQKJcFSg4K...';
        
        $payload = [
            'id' => $id,
            'title' => 'Update Files Course',
            'status' => 'closed',
            'existing_report_files' => [
                ['name' => 'file1.pdf', 'path' => 'courses/files/file1.pdf']
            ],
            'new_report_files' => [
                [
                    'name' => 'file3.xlsx',
                    'data' => $base64File
                ]
            ]
        ];

        $response = $this->actingAs($admin)->postJson('/admin/courses/save', $payload);
        $response->assertOk();

        $course = DB::table('courses')->where('id', $id)->first();
        $reportFiles = json_decode($course->report_files, true);
        $this->assertCount(2, $reportFiles);

        $this->assertEquals('file1.pdf', $reportFiles[0]['name']);
        $this->assertEquals('courses/files/file1.pdf', $reportFiles[0]['path']);
        Storage::disk('public')->assertExists('courses/files/file1.pdf');

        // file2.docx should be deleted from disk
        Storage::disk('public')->assertMissing('courses/files/file2.docx');

        // file3.xlsx should be stored on disk
        $this->assertEquals('file3.xlsx', $reportFiles[1]['name']);
        Storage::disk('public')->assertExists($reportFiles[1]['path']);
    }
}
