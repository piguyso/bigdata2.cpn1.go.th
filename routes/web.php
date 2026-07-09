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
        $slideInterval = (int) \Illuminate\Support\Facades\DB::table('settings')
        ->where('key', 'slide_interval')->value('value') ?: 7;
    return view('welcome', compact('slides', 'slideInterval'));
});



use App\Http\Controllers\SettingsController;
use App\Http\Controllers\LmsController;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.password.edit');
    Route::get('/api/school-search', [ProfileController::class, 'searchSchools'])->name('api.schools.search');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Axios / API — no page refresh
    Route::post('/api/profile/update', [ProfileController::class, 'updateApi'])->name('api.profile.update');
    Route::post('/api/profile/password', [ProfileController::class, 'updatePasswordApi'])->name('api.profile.password');
    Route::post('/api/profile/teacher', [ProfileController::class, 'updateTeacherApi'])->name('api.profile.teacher');
    Route::get('/api/profile/teacher', [ProfileController::class, 'getTeacherData'])->name('api.profile.teacher.get');

    // Reports & Directory
    Route::get('/reports', [App\Http\Controllers\SurveyReportController::class, 'index'])->name('reports.index');
    Route::get('/api/reports/data', [App\Http\Controllers\SurveyReportController::class, 'getData'])->name('api.reports.data');

    // Dashboard Statistics & Drilldown (Public for logged in users)
    Route::get('/dashboard', [App\Http\Controllers\SurveyDashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/stats', [App\Http\Controllers\SurveyDashboardController::class, 'getStats'])->name('api.dashboard.stats');
    Route::get('/api/dashboard/drilldown', [App\Http\Controllers\SurveyDashboardController::class, 'getDrilldownData'])->name('api.dashboard.drilldown');

    // LMS routes
    Route::get('/lms/courses/{id}', [LmsController::class, 'courseShow'])->name('lms.courses.show');
    Route::post('/lms/courses/{id}/enroll', [LmsController::class, 'enroll'])->name('lms.courses.enroll');
    Route::post('/lms/courses/{id}/unenroll', [LmsController::class, 'unenroll'])->name('lms.courses.unenroll');
    Route::get('/lms/lessons/{id}', [LmsController::class, 'lessonShow'])->name('lms.lessons.show');
    Route::post('/lms/lessons/{id}/complete', [LmsController::class, 'completeLesson'])->name('lms.lessons.complete');
    Route::post('/lms/lessons/{id}/submit', [LmsController::class, 'submitAssignment'])->name('lms.lessons.submit');
    Route::get('/lms/quiz', [LmsController::class, 'quizShow'])->name('lms.quiz.show');
    Route::post('/lms/quizzes/{id}/submit', [LmsController::class, 'submitQuiz'])->name('lms.quizzes.submit');
    Route::get('/lms/courses/{id}/certificate', [LmsController::class, 'downloadCertificate'])->name('lms.courses.certificate');

    // PLC Management Routes (Accessible by any authenticated user)
    Route::get('/plc', [App\Http\Controllers\PlcController::class, 'index'])->name('plc.index');
    Route::get('/plc/data', [App\Http\Controllers\PlcController::class, 'getData'])->name('plc.data');
    Route::post('/plc/save', [App\Http\Controllers\PlcController::class, 'storeGroup'])->name('plc.save');
    Route::delete('/plc/{id}', [App\Http\Controllers\PlcController::class, 'destroyGroup'])->name('plc.delete');
    Route::post('/plc/steps/save', [App\Http\Controllers\PlcController::class, 'saveStep'])->name('plc.steps.save');
    Route::post('/plc/steps/upload', [App\Http\Controllers\PlcController::class, 'uploadStepFiles'])->name('plc.steps.upload');
    Route::post('/plc/steps/delete-file', [App\Http\Controllers\PlcController::class, 'deleteStepFile'])->name('plc.steps.delete_file');
    Route::get('/plc/teacher/{userId}', [App\Http\Controllers\PlcController::class, 'getTeacherDetail'])->name('plc.teacher.detail');
    Route::post('/plc/steps/comment', [App\Http\Controllers\PlcController::class, 'saveComment'])->name('plc.steps.comment');
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
    // Admin Report Operations
    Route::get('/admin/reports/export', [App\Http\Controllers\SurveyReportController::class, 'exportExcel'])->name('admin.reports.export');
    Route::delete('/admin/reports/{id}', [App\Http\Controllers\SurveyReportController::class, 'destroy'])->name('admin.reports.delete');

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

use App\Http\Controllers\LmsAdminController;

Route::middleware(['auth', 'role:admin,teacher'])->group(function () {
    Route::get('/admin/courses', [CourseController::class, 'index'])->name('admin.courses.index');
    Route::get('/admin/courses/data', [CourseController::class, 'getData'])->name('admin.courses.data');
    Route::post('/admin/courses/save', [CourseController::class, 'store'])->name('admin.courses.save');
    Route::delete('/admin/courses/{id}', [CourseController::class, 'destroy'])->name('admin.courses.delete');

    // LMS Admin Management
    Route::get('/admin/lms/courses', [LmsAdminController::class, 'coursesIndex'])->name('admin.lms.courses.index');
    Route::get('/admin/lms/courses/data', [LmsAdminController::class, 'coursesData'])->name('admin.lms.courses.data');
    Route::post('/admin/lms/courses/save', [LmsAdminController::class, 'courseStore'])->name('admin.lms.courses.save');
    Route::delete('/admin/lms/courses/{id}', [LmsAdminController::class, 'courseDestroy'])->name('admin.lms.courses.delete');

    Route::get('/admin/lms/lessons', [LmsAdminController::class, 'lessonsIndex'])->name('admin.lms.lessons.index');
    Route::get('/admin/lms/lessons/data', [LmsAdminController::class, 'lessonsData'])->name('admin.lms.lessons.data');
    Route::post('/admin/lms/lessons/save', [LmsAdminController::class, 'lessonStore'])->name('admin.lms.lessons.save');
    Route::delete('/admin/lms/lessons/{id}', [LmsAdminController::class, 'lessonDestroy'])->name('admin.lms.lessons.delete');

    Route::get('/admin/lms/quizzes', [LmsAdminController::class, 'quizzesIndex'])->name('admin.lms.quizzes.index');
    Route::get('/admin/lms/quizzes/data', [LmsAdminController::class, 'quizzesData'])->name('admin.lms.quizzes.data');
    Route::post('/admin/lms/quizzes/save', [LmsAdminController::class, 'quizStore'])->name('admin.lms.quizzes.save');
    Route::delete('/admin/lms/quizzes/{id}', [LmsAdminController::class, 'quizDestroy'])->name('admin.lms.quizzes.delete');

    Route::get('/admin/lms/questions', [LmsAdminController::class, 'questionsIndex'])->name('admin.lms.questions.index');
    Route::get('/admin/lms/questions/data', [LmsAdminController::class, 'questionsData'])->name('admin.lms.questions.data');
    Route::post('/admin/lms/questions/save', [LmsAdminController::class, 'questionStore'])->name('admin.lms.questions.save');
    Route::delete('/admin/lms/questions/{id}', [LmsAdminController::class, 'questionDestroy'])->name('admin.lms.questions.delete');

    Route::get('/admin/lms/submissions', [LmsAdminController::class, 'submissionsIndex'])->name('admin.lms.submissions.index');
    Route::get('/admin/lms/submissions/data', [LmsAdminController::class, 'submissionsData'])->name('admin.lms.submissions.data');
    Route::post('/admin/lms/submissions/{id}/evaluate', [LmsAdminController::class, 'submissionEvaluate'])->name('admin.lms.submissions.evaluate');
});


// Route สำหรับออกจากระบบ
Route::post('/logout', function (\Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

require __DIR__.'/auth.php';
