<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\jwt;

Route::get('/dashboard', [jwt::class, 'dashboard'])->name('dashboard');
Route::post('/course', [jwt::class, 'show_course']);
Route::post('/courses', [jwt::class, 'courses']);
Route::post('/startCourse', [jwt::class, 'startCourse']);
Route::post('/restartCourse', [jwt::class, 'restartCourse']);
Route::post('/finish_lesson', [jwt::class, 'finish_lesson']);
Route::post('/submit_exam', [jwt::class, 'submit_exam']);
Route::post('/submit_user_info', [jwt::class, 'submit_user_info']);
Route::post('/get_progress_data', [jwt::class, 'get_progress_data']);

Route::post('/submitQuizAnswers', [jwt::class, 'submitQuizAnswers']);

// show photo 
Route::post('/photo', [jwt::class, 'show_photo']);