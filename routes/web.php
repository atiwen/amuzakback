<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\forget;
use App\Http\Controllers\jwt;
use App\Http\Controllers\auth_controller;
use App\Http\Controllers\admin;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\RoutePath;

Route::post('/register/1', [auth_controller::class, 'reg_step_1']);
Route::post('/register/2', [auth_controller::class, 'reg_step_2']);
Route::post('/register/3', [auth_controller::class, 'reg_step_3']);

Route::post('/jlogin', [auth_controller::class, 'jlogin']);
Route::post('/elogin', [auth_controller::class, 'jelogin']);
Route::post('/api/refresh-token', [jwt::class, 'retoken']);
Route::get('/adm/login', [admin::class, 'login'])->name('login');
Route::post(RoutePath::for('logout', '/logout'), [AuthenticatedSessionController::class, 'destroy'])->name('logout');
Route::get('/show_photo/{id}', [jwt::class, 'show_photo'])->name('show_photo');
Route::post('/adm/login', [auth_controller::class, 'adminLogin'])->name('adminLogin');
Route::post('/forget-password/1', [forget::class, 'forget_1']);
Route::post('/forget-password/2', [forget::class, 'forget_2']);
Route::post('/forget-password/3', [forget::class, 'forget_3']);

Route::post('/login/phone', [auth_controller::class, 'phone'])->name('login.phone');
Route::post('/login/phone/code', [auth_controller::class, 'code'])->name('login.phone.code');
Route::post('/login/phone/2', [auth_controller::class, 'verify_otp'])->name('login.phone.2');


require_once __DIR__ . '/jetstream.php';
