<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $slides = \Illuminate\Support\Facades\DB::table('slides')
        ->orderBy('sort_order', 'asc')
        ->orderBy('id', 'asc')
        ->get()
        ->map(function ($slide) {
            return [
                'id' => $slide->id,
                'title' => $slide->title,
                'highlight' => $slide->highlight,
                'slogan' => $slide->slogan,
                'image' => str_starts_with($slide->image, 'http') ? $slide->image : asset('storage/' . $slide->image),
                'badge' => $slide->badge,
                'link' => $slide->link,
                'btnText' => $slide->btn_text ?: 'ดูรายละเอียด',
                'btn2Text' => $slide->btn2_text ?: '',
                'btn2Link' => $slide->btn2_link ?: ''
            ];
        });
    return view('welcome', compact('slides'));
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

use App\Http\Controllers\SettingsController;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin settings routes
use App\Http\Controllers\NetworkSchoolController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\OrgMemberController;
use App\Http\Controllers\SlideController;

Route::get('/api/schools', [NetworkSchoolController::class, 'getPublicList'])->name('api.schools.list');
Route::get('/api/courses', [CourseController::class, 'getPublicList'])->name('api.courses.list');
Route::get('/api/org', [OrgMemberController::class, 'getPublicList'])->name('api.org.list');
Route::get('/api/documents', [App\Http\Controllers\DocumentController::class, 'getPublicList'])->name('api.documents.list');
Route::get('/api/slides', [SlideController::class, 'getData'])->name('api.slides.list');

Route::get('/org', [OrgMemberController::class, 'publicIndex'])->name('org.public');
Route::get('/documents', [App\Http\Controllers\DocumentController::class, 'publicIndex'])->name('documents.public');
Route::get('/documents/download/{id}', [App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');
Route::get('/courses/{id}', [App\Http\Controllers\CourseController::class, 'publicShow'])->name('courses.show');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/settings', [SettingsController::class, 'edit'])->name('admin.settings.edit');
    Route::get('/admin/settings/data', [SettingsController::class, 'getData'])->name('admin.settings.data');
    Route::post('/admin/settings/save', [SettingsController::class, 'saveSettings'])->name('admin.settings.save');
    
    Route::get('/admin/slides/data', [SlideController::class, 'getData'])->name('admin.slides.data');
    Route::post('/admin/slides/save', [SlideController::class, 'store'])->name('admin.slides.save');
    Route::post('/admin/slides/{id}/save', [SlideController::class, 'update'])->name('admin.slides.update');
    Route::delete('/admin/slides/{id}', [SlideController::class, 'destroy'])->name('admin.slides.delete');
    Route::post('/admin/slides/order', [SlideController::class, 'saveOrder'])->name('admin.slides.order');
    
    Route::get('/admin/schools', [NetworkSchoolController::class, 'index'])->name('admin.schools.index');
    Route::get('/admin/schools/data', [NetworkSchoolController::class, 'getData'])->name('admin.schools.data');
    Route::post('/admin/schools/save', [NetworkSchoolController::class, 'store'])->name('admin.schools.save');
    Route::delete('/admin/schools/{id}', [NetworkSchoolController::class, 'destroy'])->name('admin.schools.delete');
    
    Route::get('/admin/org', [OrgMemberController::class, 'index'])->name('admin.org.index');
    Route::get('/admin/org/data', [OrgMemberController::class, 'getData'])->name('admin.org.data');
    Route::post('/admin/org/save', [OrgMemberController::class, 'store'])->name('admin.org.save');
    Route::delete('/admin/org/{id}', [OrgMemberController::class, 'destroy'])->name('admin.org.delete');

    Route::get('/admin/documents', [App\Http\Controllers\DocumentController::class, 'index'])->name('admin.documents.index');
    Route::get('/admin/documents/data', [App\Http\Controllers\DocumentController::class, 'getData'])->name('admin.documents.data');
    Route::post('/admin/documents/save', [App\Http\Controllers\DocumentController::class, 'store'])->name('admin.documents.save');
    Route::delete('/admin/documents/{id}', [App\Http\Controllers\DocumentController::class, 'destroy'])->name('admin.documents.delete');

    // Admin users management routes
    Route::get('/admin/users', [\App\Http\Controllers\UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/data', [\App\Http\Controllers\UserController::class, 'getData'])->name('admin.users.data');
    Route::post('/admin/users/save', [\App\Http\Controllers\UserController::class, 'store'])->name('admin.users.save');
    Route::post('/admin/users/{id}/save', [\App\Http\Controllers\UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{id}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('admin.users.delete');
});

Route::middleware(['auth', 'role:admin,teacher'])->group(function () {
    Route::get('/admin/courses', [CourseController::class, 'index'])->name('admin.courses.index');
    Route::get('/admin/courses/data', [CourseController::class, 'getData'])->name('admin.courses.data');
    Route::post('/admin/courses/save', [CourseController::class, 'store'])->name('admin.courses.save');
    Route::delete('/admin/courses/{id}', [CourseController::class, 'destroy'])->name('admin.courses.delete');
});

// Route สำหรับออกจากระบบ
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Route สำหรับหน้า Dashboard (เบื้องต้นให้แสดง view ชื่อ dashboard)
Route::get('/dashboard', function () {
    return view('dashboard'); // คุณต้องมีไฟล์ resources/views/dashboard.blade.php
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';
