<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\User;

class AdminNotificationTest extends TestCase
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

    public function test_admin_can_mark_notification_as_noticed()
    {
        $this->actingAs($this->adminUser);

        // ایجاد یک اعلان برای تست
        $notificationId = DB::table('notifs')->insertGetId([
            'title' => 'Test Notification',
            'content' => 'Test Content',
            'is_readed' => false,
            'is_user' => 0,
            'created' => now(),
            'is_noticed' => 0
        ]);

        $response = $this->postJson("/admin/adm_noticed/{$notificationId}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'success']);
        
        $this->assertDatabaseHas('notifs', [
            'id' => $notificationId,
            'is_noticed' => 1
        ]);
    }

    public function test_non_admin_cannot_mark_notification_as_noticed()
    {
        $this->actingAs($this->regularUser);

        $notificationId = DB::table('notifs')->insertGetId([
            'title' => 'Test Notification',
            'content' => 'Test Content',
            'is_readed' => false,
            'is_user' => 0,
            'created' => now(),
            'is_noticed' => 0
        ]);

        $response = $this->postJson("/admin/adm_noticed/{$notificationId}");

        $response->assertStatus(403);
        
        $this->assertDatabaseHas('notifs', [
            'id' => $notificationId,
            'is_noticed' => 0
        ]);
    }

    public function test_admin_can_mark_notification_item_as_noticed()
    {
        $this->actingAs($this->adminUser);

        // ایجاد یک اعلان برای تست
        $notificationId = DB::table('notifs')->insertGetId([
            'title' => 'Test Notification',
            'content' => 'Test Content',
            'is_readed' => false,
            'is_user' => 0,
            'created' => now(),
            'is_noticed' => 0
        ]);

        $response = $this->postJson("/admin/adm_notif_item/{$notificationId}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'success']);
        
        $this->assertDatabaseHas('notifs', [
            'id' => $notificationId,
            'is_noticed' => 1
        ]);
    }

    public function test_non_admin_cannot_mark_notification_item_as_noticed()
    {
        $this->actingAs($this->regularUser);

        $notificationId = DB::table('notifs')->insertGetId([
            'title' => 'Test Notification',
            'content' => 'Test Content',
            'is_readed' => false,
            'is_user' => 0,
            'created' => now(),
            'is_noticed' => 0
        ]);

        $response = $this->postJson("/admin/adm_notif_item/{$notificationId}");

        $response->assertStatus(403);
        
        $this->assertDatabaseHas('notifs', [
            'id' => $notificationId,
            'is_noticed' => 0
        ]);
    }
}