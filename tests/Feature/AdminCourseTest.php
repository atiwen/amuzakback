<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class AdminCourseTest extends TestCase
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

        $response->assertStatus(403);
        $this->assertDatabaseMissing('courses', [
            'title' => 'Test Course'
        ]);
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

        $response->assertStatus(403);
        $this->assertDatabaseMissing('courses', [
            'title' => 'Updated Title'
        ]);
    }
}