<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin;

use Laravel\Jetstream\Jetstream;

Route::group(['middleware' => config('jetstream.middleware', ['web'])], function () {

    $authMiddleware = config('jetstream.guard')
        ? 'auth:' . config('jetstream.guard')
        : 'auth';

    $authSessionMiddleware = config('jetstream.auth_session', false)
        ? config('jetstream.auth_session')
        : null;

    Route::group(['middleware' => array_values(array_filter([$authMiddleware, $authSessionMiddleware]))], function () {

        // main
        Route::get('/', [admin::class, 'admin'])->name('admin.panel');
        Route::get('/adm/users', [admin::class, 'users'])->name('admin.users');
        Route::get('/adm/user/{id}', [admin::class, 'user'])->name('admin.user');
        Route::post('/adm/upmg', [admin::class, 'upmg'])->name('admin.upmg');
        Route::post('/adm/delmg', [admin::class, 'delmg'])->name('admin.delmg');
        
        Route::get('/adm/courses', [admin::class, 'courses'])->name('admin.courses');
        Route::get('/adm/add_course', [admin::class, 'add_course'])->name('admin.add_course');
        Route::post('/adm/add_course_full', [admin::class, 'add_course_full'])->name('admin.add_course_full');
        Route::get('/adm/edit_course/{id}', [admin::class, 'edit_course'])->name('admin.edit_course');
        Route::post('/adm/update_course/{id}', [admin::class, 'update_course'])->name('admin.update_course');
    });
});
