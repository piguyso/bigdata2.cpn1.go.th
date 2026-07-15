<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AssetDashboardController;
use App\Http\Controllers\BasicExamDashboardController;
use App\Http\Controllers\NtImportController;
use App\Http\Controllers\OnetDashboardController;
use App\Http\Controllers\OnetImportController;
use App\Http\Controllers\ObecAssetImportController;
use App\Http\Controllers\PersonnelDashboardController;
use App\Http\Controllers\RtImportController;
use App\Http\Controllers\PersonnelOverviewImportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SurveyDashboardController;
use App\Http\Controllers\StudentDataDashboardController;
use App\Http\Controllers\StudentDataImportController;

Route::get('/', [SurveyDashboardController::class, 'index'])->name('dashboard');
Route::redirect('/dashboard', '/');
Route::get('/onet', [OnetDashboardController::class, 'index'])->name('onet.dashboard');
Route::get('/nt', [BasicExamDashboardController::class, 'ntIndex'])->name('nt.dashboard');
Route::get('/rt', [BasicExamDashboardController::class, 'rtIndex'])->name('rt.dashboard');
Route::get('/assets', [AssetDashboardController::class, 'index'])->name('asset.dashboard');
Route::get('/personnel', [PersonnelDashboardController::class, 'index'])->name('personnel.dashboard');
Route::get('/students', [StudentDataDashboardController::class, 'index'])->name('student-data.dashboard');
Route::get('/students/export/xlsx', [StudentDataDashboardController::class, 'exportXlsx'])->name('student-data.export');
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
Route::get('/api/nt/dashboard', [BasicExamDashboardController::class, 'ntData'])->name('api.nt.dashboard');
Route::get('/api/rt/dashboard', [BasicExamDashboardController::class, 'rtData'])->name('api.rt.dashboard');
Route::get('/api/assets/dashboard', [AssetDashboardController::class, 'data'])->name('api.assets.dashboard');
Route::get('/api/personnel/dashboard', [PersonnelDashboardController::class, 'data'])->name('api.personnel.dashboard');
Route::get('/api/personnel/schools', [PersonnelDashboardController::class, 'schoolsData'])->name('api.personnel.schools');
Route::get('/api/personnel/area', [PersonnelDashboardController::class, 'areaData'])->name('api.personnel.area');
Route::get('/api/personnel/position', [PersonnelDashboardController::class, 'positionData'])->name('api.personnel.position');
Route::get('/api/personnel/gender', [PersonnelDashboardController::class, 'genderData'])->name('api.personnel.gender');
Route::get('/api/personnel/education', [PersonnelDashboardController::class, 'educationData'])->name('api.personnel.education');
Route::get('/api/personnel/academic-standing', [PersonnelDashboardController::class, 'academicStandingData'])->name('api.personnel.academic-standing');
Route::get('/api/student-data/dashboard', [StudentDataDashboardController::class, 'data'])->name('api.student-data.dashboard');


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
    Route::get('/admin/schools/template', [AdminSchoolController::class, 'downloadTemplate'])->name('admin.schools.template');
    Route::post('/admin/schools/import', [AdminSchoolController::class, 'import'])->name('admin.schools.import');
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

    Route::get('/admin/student-data-imports', [StudentDataImportController::class, 'index'])->name('admin.student-data-imports.index');
    Route::get('/admin/student-data-imports/data', [StudentDataImportController::class, 'getData'])->name('admin.student-data-imports.data');
    Route::get('/admin/student-data-imports/template/{dataType}', [StudentDataImportController::class, 'downloadTemplate'])->name('admin.student-data-imports.template');
    Route::post('/admin/student-data-imports/preview', [StudentDataImportController::class, 'preview'])->name('admin.student-data-imports.preview');
    Route::post('/admin/student-data-imports/import', [StudentDataImportController::class, 'import'])->name('admin.student-data-imports.import');
    Route::delete('/admin/student-data-imports/{id}', [StudentDataImportController::class, 'delete'])->name('admin.student-data-imports.delete');

    Route::get('/admin/nt', [NtImportController::class, 'index'])->name('admin.nt.index');
    Route::get('/admin/nt/data', [NtImportController::class, 'getData'])->name('admin.nt.data');
    Route::get('/admin/nt/template', [NtImportController::class, 'downloadTemplate'])->name('admin.nt.template');
    Route::post('/admin/nt/preview', [NtImportController::class, 'preview'])->name('admin.nt.preview');
    Route::post('/admin/nt/import', [NtImportController::class, 'import'])->name('admin.nt.import');
    Route::delete('/admin/nt/data-set', [NtImportController::class, 'destroy'])->name('admin.nt.delete');

    Route::get('/admin/rt', [RtImportController::class, 'index'])->name('admin.rt.index');
    Route::get('/admin/rt/data', [RtImportController::class, 'getData'])->name('admin.rt.data');
    Route::get('/admin/rt/template', [RtImportController::class, 'downloadTemplate'])->name('admin.rt.template');
    Route::post('/admin/rt/preview', [RtImportController::class, 'preview'])->name('admin.rt.preview');
    Route::post('/admin/rt/import', [RtImportController::class, 'import'])->name('admin.rt.import');
    Route::delete('/admin/rt/data-set', [RtImportController::class, 'destroy'])->name('admin.rt.delete');

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

    Route::get('/admin/obec-asset', [ObecAssetImportController::class, 'index'])->name('admin.obec-asset.index');
    Route::get('/admin/obec-asset/data', [ObecAssetImportController::class, 'getData'])->name('admin.obec-asset.data');
    Route::post('/admin/obec-asset/preview', [ObecAssetImportController::class, 'preview'])->name('admin.obec-asset.preview');
    Route::post('/admin/obec-asset/import', [ObecAssetImportController::class, 'import'])->name('admin.obec-asset.import');
    Route::delete('/admin/obec-asset/data-set', [ObecAssetImportController::class, 'destroy'])->name('admin.obec-asset.delete');
    
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
