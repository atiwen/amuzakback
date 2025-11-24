<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\User;

class AdminControllerFullTest extends TestCase
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
            'role' => 'student'
        ]);
    }

    public function test_admin_can_add_course()
    {
        $this->actingAs($this->adminUser);

        $response = $this->postJson('/admin/add_course_full', [
            'title' => 'Test Course',
            'grade' => 10,
            'description' => 'Test Description',
            'is_pro' => false,
            'chapters' => [
                [
                    'title' => 'Chapter 1',
                    'lessons' => [
                        [
                            'title' => 'Lesson 1',
                            'sections' => [
                                [
                                    'title' => 'Section 1',
                                    'contents' => ['content' => 'test content']
                                ]
                            ]
                        ]
                    ],
                    'exam_questions' => [
                        [
                            'question' => 'Test Question?',
                            'option1' => 'Option 1',
                            'option2' => 'Option 2',
                            'option3' => 'Option 3',
                            'option4' => 'Option 4',
                            'correct_option' => 'option1'
                        ]
                    ]
                ]
            ]
        ]);

        $response->assertStatus(302); // redirect after successful creation
        
        $this->assertDatabaseHas('courses', [
            'title' => 'Test Course',
            'grade' => 10,
            'description' => 'Test Description',
            'is_pro' => false
        ]);
    }

    public function test_non_admin_cannot_add_course()
    {
        $this->actingAs($this->regularUser);

        $response = $this->postJson('/admin/add_course_full', [
            'title' => 'Test Course',
            'grade' => 10,
            'description' => 'Test Description',
            'is_pro' => false,
            'chapters' => []
        ]);

        $response->assertStatus(302); // redirect to login page
    }

    public function test_admin_can_update_course()
    {
        $this->actingAs($this->adminUser);

        // ایجاد یک دوره برای تست بروزرسانی
        $courseId = DB::table('courses')->insertGetId([
            'title' => 'Old Title',
            'grade' => 9,
            'description' => 'Old Description',
            'is_pro' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->putJson("/admin/update_course/{$courseId}", [
            'title' => 'Updated Title',
            'grade' => 10,
            'description' => 'Updated Description',
            'is_pro' => true,
            'intro_contents' => [],
            'chapters' => [
                [
                    'title' => 'Updated Chapter 1',
                    'lessons' => [
                        [
                            'title' => 'Updated Lesson 1',
                            'sections' => [
                                [
                                    'title' => 'Updated Section 1',
                                    'contents' => ['content' => 'updated content']
                                ]
                            ]
                        ]
                    ],
                    'exam_questions' => []
                ]
            ]
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('courses', [
            'id' => $courseId,
            'title' => 'Updated Title',
            'grade' => 10,
            'description' => 'Updated Description',
            'is_pro' => true
        ]);
    }

    public function test_non_admin_cannot_update_course()
    {
        $this->actingAs($this->regularUser);

        $courseId = DB::table('courses')->insertGetId([
            'title' => 'Old Title',
            'grade' => 9,
            'description' => 'Old Description',
            'is_pro' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->putJson("/admin/update_course/{$courseId}", [
            'title' => 'Updated Title',
            'grade' => 10,
            'description' => 'Updated Description',
            'is_pro' => true,
            'intro_contents' => [],
            'chapters' => []
        ]);

        $response->assertStatus(302); // redirect to login page
        $this->assertDatabaseMissing('courses', [
            'title' => 'Updated Title'
        ]);
    }

    public function test_admin_can_update_notification_status()
    {
        $this->actingAs($this->adminUser);

        // ایجاد یک اعلان برای تست
        $notificationId = DB::table('notifs')->insertGetId([
            'user_id' => $this->regularUser->id,
            'massage' => 'Test notification',
            'link' => '/test',
            'is_readed' => false,
            'is_noticed' => false,
            'created' => now()
        ]);

        $response = $this->postJson("/admin/adm_noticed/{$notificationId}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'success']);
        
        $this->assertDatabaseHas('notifs', [
            'id' => $notificationId,
            'is_noticed' => 1
        ]);
    }

    public function test_non_admin_cannot_update_notification_status()
    {
        $this->actingAs($this->regularUser);

        // ایجاد یک اعلان برای تست
        $notificationId = DB::table('notifs')->insertGetId([
            'user_id' => $this->regularUser->id,
            'massage' => 'Test notification',
            'link' => '/test',
            'is_readed' => false,
            'is_noticed' => false,
            'created' => now()
        ]);

        $response = $this->postJson("/admin/adm_noticed/{$notificationId}");

        $response->assertStatus(302); // redirect to login page
    }

    public function test_admin_can_update_notification_item_status()
    {
        $this->actingAs($this->adminUser);

        // ایجاد یک اعلان برای تست
        $notificationId = DB::table('notifs')->insertGetId([
            'user_id' => $this->regularUser->id,
            'massage' => 'Test notification',
            'link' => '/test',
            'is_readed' => false,
            'is_noticed' => false,
            'created' => now()
        ]);

        $response = $this->postJson("/admin/adm_notif_item/{$notificationId}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'success']);
        
        $this->assertDatabaseHas('notifs', [
            'id' => $notificationId,
            'is_noticed' => 1
        ]);
    }

    public function test_non_admin_cannot_update_notification_item_status()
    {
        $this->actingAs($this->regularUser);

        // ایجاد یک اعلان برای تست
        $notificationId = DB::table('notifs')->insertGetId([
            'user_id' => $this->regularUser->id,
            'massage' => 'Test notification',
            'link' => '/test',
            'is_readed' => false,
            'is_noticed' => false,
            'created' => now()
        ]);

        $response = $this->postJson("/admin/adm_notif_item/{$notificationId}");

        $response->assertStatus(302); // redirect to login page
    }

    public function test_admin_can_delete_course_image_reference()
    {
        $this->actingAs($this->adminUser);

        // ایجاد یک دوره با تصویر
        $courseId = DB::table('courses')->insertGetId([
            'title' => 'Test Course with Image',
            'imgurl' => '123', // ID تصویر
            'grade' => 10,
            'description' => 'Test Description',
            'is_pro' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->postJson('/admin/delmg', [
            'id' => $courseId,
            'type' => 'course_img'
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('courses', [
            'id' => $courseId,
            'imgurl' => "" // تصویر باید حذف شده باشد
        ]);
    }

    public function test_non_admin_cannot_delete_course_image_reference()
    {
        $this->actingAs($this->regularUser);

        // ایجاد یک دوره با تصویر
        $courseId = DB::table('courses')->insertGetId([
            'title' => 'Test Course with Image',
            'imgurl' => '123',
            'grade' => 10,
            'description' => 'Test Description',
            'is_pro' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->postJson('/admin/delmg', [
            'id' => $courseId,
            'type' => 'course_img'
        ]);

        $response->assertStatus(302); // redirect to login page
    }

    public function test_add_course_validation()
    {
        $this->actingAs($this->adminUser);

        $response = $this->postJson('/admin/add_course_full', [
            'title' => '', // باید خالی نباشد
            'grade' => 'invalid_grade', // باید عدد صحیح باشد
            'chapters' => 'not_an_array' // باید آرایه باشد
        ]);

        $response->assertStatus(302); // validation errors redirect back
    }

    public function test_update_course_validation()
    {
        $this->actingAs($this->adminUser);

        // ایجاد یک دوره برای تست
        $courseId = DB::table('courses')->insertGetId([
            'title' => 'Old Title',
            'grade' => 9,
            'description' => 'Old Description',
            'is_pro' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->putJson("/admin/update_course/{$courseId}", [
            'title' => '', // باید خالی نباشد
            'grade' => 'invalid_grade', // باید عدد صحیح باشد
            'chapters' => 'not_an_array' // باید آرایه باشد
        ]);

        $response->assertStatus(302); // validation errors redirect back
    }

    public function test_delete_image_reference_validation()
    {
        $this->actingAs($this->adminUser);

        $response = $this->postJson('/admin/delmg', [
            'id' => 'invalid_id', // باید عدد صحیح باشد
            'type' => '' // باید خالی نباشد
        ]);

        $response->assertStatus(302); // validation errors redirect back
    }

    public function test_update_notification_validation()
    {
        $this->actingAs($this->adminUser);

        $response = $this->postJson('/admin/adm_noticed/invalid_id');

        $response->assertStatus(500); // should return error for invalid ID
    }

    public function test_admin_status_endpoint()
    {
        $this->actingAs($this->adminUser);

        $response = $this->getJson('/admin/status');

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_admin_status()
    {
        $this->actingAs($this->regularUser);

        $response = $this->getJson('/admin/status');

        $response->assertStatus(302); // redirect to login
    }
}