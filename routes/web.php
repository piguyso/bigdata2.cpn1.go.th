<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OnetDashboardController;
use App\Http\Controllers\OnetImportController;
use App\Http\Controllers\PersonnelDashboardController;
use App\Http\Controllers\PersonnelOverviewImportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SurveyDashboardController;

Route::get('/', [SurveyDashboardController::class, 'index'])->name('dashboard');
Route::redirect('/dashboard', '/');
Route::get('/onet', [OnetDashboardController::class, 'index'])->name('onet.dashboard');
Route::get('/personnel', [PersonnelDashboardController::class, 'index'])->name('personnel.dashboard');
Route::get('/personnel/area', [PersonnelDashboardController::class, 'area'])->name('personnel.area');
Route::get('/personnel/schools', [PersonnelDashboardController::class, 'schools'])->name('personnel.schools');
Route::get('/personnel/position', [PersonnelDashboardController::class, 'position'])->name('personnel.position');
Route::get('/personnel/gender', [PersonnelDashboardController::class, 'gender'])->name('personnel.gender');
Route::get('/personnel/education', [PersonnelDashboardController::class, 'education'])->name('personnel.education');
Route::get('/personnel/academic-standing', [PersonnelDashboardController::class, 'academicStanding'])->name('personnel.academic-standing');
Route::get('/personal', [PersonnelDashboardController::class, 'index'])->name('personnel.dashboard.alias');
Route::get('/schools', [SurveyDashboardController::class, 'schools'])->name('dashboard.schools');
Route::get('/schools/size/{size}', [SurveyDashboardController::class, 'schoolsBySize'])->name('dashboard.schools-by-size');
Route::get('/schools/network/{network}', [SurveyDashboardController::class, 'networkSchools'])->name('dashboard.network-schools');
Route::get('/schools/network/{network}/size/{size}', [SurveyDashboardController::class, 'networkSchoolsBySize'])->name('dashboard.network-schools-by-size');
Route::get('/schools/district/{district}', [SurveyDashboardController::class, 'districtSchools'])->name('dashboard.district-schools');
Route::get('/schools/district/{district}/size/{size}', [SurveyDashboardController::class, 'districtSchoolsBySize'])->name('dashboard.district-schools-by-size');
Route::get('/schools/opportunity', [SurveyDashboardController::class, 'opportunitySchools'])->name('dashboard.opportunity-schools');
Route::get('/schools/opportunity/size/{size}', [SurveyDashboardController::class, 'opportunitySchoolsBySize'])->name('dashboard.opportunity-schools-by-size');
Route::get('/schools/export/xlsx', [SurveyDashboardController::class, 'exportSchoolsXlsx'])->name('dashboard.schools.export');



use App\Http\Controllers\SettingsController;


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

});

// Admin settings routes
use App\Http\Controllers\AdminSchoolController;
use App\Http\Controllers\SchoolGroupController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\OrgMemberController;
use App\Http\Controllers\SchoolmisController;

Route::get('/api/academic-years', [AcademicYearController::class, 'getPublicList'])->name('api.academic-years.list');
Route::get('/api/courses', [CourseController::class, 'getPublicList'])->name('api.courses.list');
Route::get('/api/org', [OrgMemberController::class, 'getPublicList'])->name('api.org.list');
Route::get('/api/documents', [App\Http\Controllers\DocumentController::class, 'getPublicList'])->name('api.documents.list');
Route::get('/api/dashboard/stats', [SurveyDashboardController::class, 'getStats'])->name('api.dashboard.stats');
Route::get('/api/dashboard/drilldown', [SurveyDashboardController::class, 'getDrilldownData'])->name('api.dashboard.drilldown');
Route::get('/api/dashboard/student-trend', [SurveyDashboardController::class, 'getStudentTrend'])->name('api.dashboard.student-trend');
Route::get('/api/dashboard/level-trend', [SurveyDashboardController::class, 'getLevelTrend'])->name('api.dashboard.level-trend');
Route::get('/api/dashboard/school-trend', [SurveyDashboardController::class, 'getSchoolTrend'])->name('api.dashboard.school-trend');
Route::get('/api/dashboard/school-student-detail', [SurveyDashboardController::class, 'getSchoolStudentDetail'])->name('api.dashboard.school-student-detail');
Route::get('/api/dashboard/school-info', [SurveyDashboardController::class, 'getSchoolInfo'])->name('api.dashboard.school-info');
Route::get('/api/onet/dashboard', [OnetDashboardController::class, 'data'])->name('api.onet.dashboard');
Route::get('/api/personnel/dashboard', [PersonnelDashboardController::class, 'data'])->name('api.personnel.dashboard');


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
    

    
    Route::get('/admin/schools', [AdminSchoolController::class, 'index'])->name('admin.schools.index');
    Route::get('/admin/schools/data', [AdminSchoolController::class, 'getData'])->name('admin.schools.data');
    Route::post('/admin/schools/save', [AdminSchoolController::class, 'store'])->name('admin.schools.save');
    Route::delete('/admin/schools/{id}', [AdminSchoolController::class, 'destroy'])->name('admin.schools.delete');

    Route::get('/admin/school-group', [SchoolGroupController::class, 'index'])->name('admin.school-group.index');
    Route::get('/admin/school-group/data', [SchoolGroupController::class, 'getData'])->name('admin.school-group.data');
    Route::post('/admin/school-group/save', [SchoolGroupController::class, 'store'])->name('admin.school-group.save');
    Route::delete('/admin/school-group/{id}', [SchoolGroupController::class, 'destroy'])->name('admin.school-group.delete');

    Route::get('/admin/academic-years', [AcademicYearController::class, 'index'])->name('admin.academic-years.index');
    Route::get('/admin/academic-years/data', [AcademicYearController::class, 'getData'])->name('admin.academic-years.data');
    Route::post('/admin/academic-years/save', [AcademicYearController::class, 'store'])->name('admin.academic-years.save');
    Route::post('/admin/academic-years/{id}/active', [AcademicYearController::class, 'setActive'])->name('admin.academic-years.active');
    Route::delete('/admin/academic-years/{id}', [AcademicYearController::class, 'destroy'])->name('admin.academic-years.delete');

    Route::get('/admin/schoolmis', [SchoolmisController::class, 'index'])->name('admin.schoolmis.index');
    Route::get('/admin/schoolmis/data', [SchoolmisController::class, 'getData'])->name('admin.schoolmis.data');
    Route::post('/admin/schoolmis/preview', [SchoolmisController::class, 'preview'])->name('admin.schoolmis.preview');
    Route::post('/admin/schoolmis/import', [SchoolmisController::class, 'import'])->name('admin.schoolmis.import');
    Route::delete('/admin/schoolmis/data-set', [SchoolmisController::class, 'destroy'])->name('admin.schoolmis.delete');

    Route::get('/admin/onet', [OnetImportController::class, 'index'])->name('admin.onet.index');
    Route::get('/admin/onet/data', [OnetImportController::class, 'getData'])->name('admin.onet.data');
    Route::post('/admin/onet/preview', [OnetImportController::class, 'preview'])->name('admin.onet.preview');
    Route::post('/admin/onet/import', [OnetImportController::class, 'import'])->name('admin.onet.import');
    Route::delete('/admin/onet/data-set', [OnetImportController::class, 'destroy'])->name('admin.onet.delete');

    Route::get('/admin/personnel-overview', [PersonnelOverviewImportController::class, 'index'])->name('admin.personnel-overview.index');
    Route::get('/admin/personnel-overview/data', [PersonnelOverviewImportController::class, 'getData'])->name('admin.personnel-overview.data');
    Route::post('/admin/personnel-overview/preview', [PersonnelOverviewImportController::class, 'preview'])->name('admin.personnel-overview.preview');
    Route::post('/admin/personnel-overview/import', [PersonnelOverviewImportController::class, 'import'])->name('admin.personnel-overview.import');
    Route::delete('/admin/personnel-overview/data-set', [PersonnelOverviewImportController::class, 'destroy'])->name('admin.personnel-overview.delete');
    
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
Route::post('/logout', function (\Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

require __DIR__.'/auth.php';
