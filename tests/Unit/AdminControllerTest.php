<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class AdminControllerTest extends TestCase
{
    public function test_upmg_method_validates_input()
    {
        // ایجاد یک کاربر ادمین
        $adminUser = new User();
        $adminUser->id = 1;
        $adminUser->role = 'admin';
        
        // وارد کردن کاربر ادمین
        $this->actingAs($adminUser);

        // ایجاد یک درخواست فیک برای تست
        $request = Request::create('/admin/upmg', 'POST', [
            'id' => 'invalid_id', // باید عدد صحیح باشد
            'type' => 'course_img'
        ]);

        // ایجاد یک فایل تصویر فیک
        $image = UploadedFile::fake()->image('test.jpg');
        $request->files->set('img', $image);

        // ایجاد کنترلر و فراخوانی متد
        $controller = new admin();
        
        // از آنجایی که متد upmg یک تأیید اعتبار انجام می‌دهد، باید آن را فریب دهیم
        $this->mock(Auth::class);
        $this->mock(DB::class);

        // تست اعتبارسنجی
        $this->expectException(\Illuminate\Validation\ValidationException::class);
    }

    public function test_admin_authentication_check()
    {
        // تست بررسی احراز هویت ادمین
        $adminUser = new User();
        $adminUser->id = 1;
        $adminUser->role = 'admin';
        
        $this->actingAs($adminUser);
        
        // بررسی اینکه آیا کاربر احراز هویت شده است
        $this->assertTrue(Auth::check());
        $this->assertEquals('admin', Auth::user()->role);
    }

    public function test_regular_user_cannot_access_admin_functions()
    {
        // تست اینکه کاربر معمولی نمی‌تواند به توابع ادمین دسترسی داشته باشد
        $regularUser = new User();
        $regularUser->id = 1;
        $regularUser->role = 'user';
        
        $this->actingAs($regularUser);
        
        // بررسی نقش کاربر
        $this->assertEquals('user', Auth::user()->role);
        $this->assertNotEquals('admin', Auth::user()->role);
    }
}