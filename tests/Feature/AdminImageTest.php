<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class AdminImageTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // ایجاد کاربر ادمین
        $this->adminUser = User::factory()->create([
            'role' => 'admin'
        ]);
        
        // ایجاد کاربر معمولی
        $this->regularUser = User::factory()->create([
            'role' => 'user'
        ]);
    }

    public function test_admin_can_upload_image()
    {
        $this->actingAs($this->adminUser);

        // ایجاد یک دوره برای تست آپلود تصویر
        $courseId = DB::table('courses')->insertGetId([
            'title' => 'Test Course',
            'grade' => 10,
            'description' => 'Test Description',
            'is_pro' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $image = UploadedFile::fake()->image('test.jpg');

        $response = $this->post('/admin/upmg', [
            'id' => $courseId,
            'img' => $image,
            'type' => 'course_img'
        ]);

        $response->assertStatus(302); // redirect after successful upload
    }

    public function test_non_admin_cannot_upload_image()
    {
        $this->actingAs($this->regularUser);

        $courseId = DB::table('courses')->insertGetId([
            'title' => 'Test Course',
            'grade' => 10,
            'description' => 'Test Description',
            'is_pro' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $image = UploadedFile::fake()->image('test.jpg');

        $response = $this->post('/admin/upmg', [
            'id' => $courseId,
            'img' => $image,
            'type' => 'course_img'
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_image()
    {
        $this->actingAs($this->adminUser);

        // ایجاد یک دوره با تصویر
        $courseId = DB::table('courses')->insertGetId([
            'title' => 'Test Course',
            'grade' => 10,
            'description' => 'Test Description',
            'is_pro' => false,
            'imgurl' => '1',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->post('/admin/delmg', [
            'id' => $courseId,
            'type' => 'course_img'
        ]);

        $response->assertStatus(302); // redirect after successful deletion
        
        $this->assertDatabaseHas('courses', [
            'id' => $courseId,
            'imgurl' => ""
        ]);
    }

    public function test_non_admin_cannot_delete_image()
    {
        $this->actingAs($this->regularUser);

        $courseId = DB::table('courses')->insertGetId([
            'title' => 'Test Course',
            'grade' => 10,
            'description' => 'Test Description',
            'is_pro' => false,
            'imgurl' => '1',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->post('/admin/delmg', [
            'id' => $courseId,
            'type' => 'course_img'
        ]);

        $response->assertStatus(403);
        
        $this->assertDatabaseHas('courses', [
            'id' => $courseId,
            'imgurl' => "1"
        ]);
    }
}