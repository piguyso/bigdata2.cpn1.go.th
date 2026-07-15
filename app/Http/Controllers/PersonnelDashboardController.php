<?php

namespace App\Http\Controllers;

use App\Services\LocalPersonnelDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PersonnelDashboardController extends Controller
{
    public function __construct(private readonly LocalPersonnelDashboardService $personnelDashboardService)
    {
    }

    public function index(Request $request): View
    {
        return view('personnel-dashboard', [
            'dashboardPayload' => $this->personnelDashboardService->getDashboardPayload(
                $request->integer('year') ?: null,
                $request->string('term')->toString() ?: null,
                $request->string('school_smis')->toString() ?: null
            ),
        ]);
    }

    public function schools(Request $request): View
    {
        return view('personnel-dashboard', [
            'dashboardPayload' => $this->personnelDashboardService->getSchoolDashboardPayload(
                $request->integer('year') ?: null,
                $request->string('term')->toString() ?: null,
                $request->string('school_smis')->toString() ?: null
            ),
            'pageTitle' => 'ภาพรวมบุคลากรในโรงเรียน',
            'pageDescription' => 'แดชบอร์ดสรุปข้อมูลบุคลากรเฉพาะโรงเรียนจาก snapshot ที่นำเข้าไว้ในฐานข้อมูล local',
            'reloadRoute' => 'personnel.schools',
            'schoolOverviewLabel' => 'ภาพรวมโรงเรียนทั้งหมด',
        ]);
    }

    public function area(Request $request): View
    {
        return view('personnel-area-dashboard', [
            'dashboardPayload' => $this->personnelDashboardService->getAreaPersonnelPayload(
                $request->integer('year') ?: null,
                $request->string('term')->toString() ?: null
            ),
        ]);
    }

    public function position(Request $request): View
    {
        return view('personnel-position-dashboard', [
            'dashboardPayload' => $this->personnelDashboardService->getPositionReportPayload(
                $request->integer('year') ?: null,
                $request->string('term')->toString() ?: null,
                $request->string('school_smis')->toString() ?: null
            ),
        ]);
    }

    public function gender(Request $request): View
    {
        return view('personnel-gender-dashboard', [
            'dashboardPayload' => $this->personnelDashboardService->getGenderReportPayload(
                $request->integer('year') ?: null,
                $request->string('term')->toString() ?: null,
                $request->string('school_smis')->toString() ?: null
            ),
        ]);
    }

    public function education(Request $request): View
    {
        return view('personnel-education-dashboard', [
            'dashboardPayload' => $this->personnelDashboardService->getEducationReportPayload(
                $request->integer('year') ?: null,
                $request->string('term')->toString() ?: null,
                $request->string('school_smis')->toString() ?: null
            ),
        ]);
    }

    public function academicStanding(Request $request): View
    {
        return view('personnel-academic-standing-dashboard', [
            'dashboardPayload' => $this->personnelDashboardService->getAcademicStandingReportPayload(
                $request->integer('year') ?: null,
                $request->string('term')->toString() ?: null,
                $request->string('school_smis')->toString() ?: null
            ),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        try {
            return response()->json(
                $this->personnelDashboardService->getDashboardPayload(
                    $request->integer('year') ?: null,
                    $request->string('term')->toString() ?: null,
                    $request->string('school_smis')->toString() ?: null
                )
            );
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'ไม่สามารถดึงข้อมูลบุคลากรจากฐานข้อมูล local ได้ในขณะนี้',
            ], 502);
        }
    }

    public function schoolsData(Request $request): JsonResponse
    {
        try {
            return response()->json($this->personnelDashboardService->getSchoolDashboardPayload(
                $request->integer('year') ?: null,
                $request->string('term')->toString() ?: null,
                $request->string('school_smis')->toString() ?: null
            ));
        } catch (Throwable $exception) {
            return $this->personnelApiError();
        }
    }

    public function areaData(Request $request): JsonResponse
    {
        try {
            return response()->json($this->personnelDashboardService->getAreaPersonnelPayload(
                $request->integer('year') ?: null,
                $request->string('term')->toString() ?: null
            ));
        } catch (Throwable $exception) {
            return $this->personnelApiError();
        }
    }

    public function positionData(Request $request): JsonResponse
    {
        try {
            return response()->json($this->personnelDashboardService->getPositionReportPayload(
                $request->integer('year') ?: null,
                $request->string('term')->toString() ?: null,
                $request->string('school_smis')->toString() ?: null
            ));
        } catch (Throwable $exception) {
            return $this->personnelApiError();
        }
    }

    public function genderData(Request $request): JsonResponse
    {
        try {
            return response()->json($this->personnelDashboardService->getGenderReportPayload(
                $request->integer('year') ?: null,
                $request->string('term')->toString() ?: null,
                $request->string('school_smis')->toString() ?: null
            ));
        } catch (Throwable $exception) {
            return $this->personnelApiError();
        }
    }

    public function educationData(Request $request): JsonResponse
    {
        try {
            return response()->json($this->personnelDashboardService->getEducationReportPayload(
                $request->integer('year') ?: null,
                $request->string('term')->toString() ?: null,
                $request->string('school_smis')->toString() ?: null
            ));
        } catch (Throwable $exception) {
            return $this->personnelApiError();
        }
    }

    public function academicStandingData(Request $request): JsonResponse
    {
        try {
            return response()->json($this->personnelDashboardService->getAcademicStandingReportPayload(
                $request->integer('year') ?: null,
                $request->string('term')->toString() ?: null,
                $request->string('school_smis')->toString() ?: null
            ));
        } catch (Throwable $exception) {
            return $this->personnelApiError();
        }
    }

    private function personnelApiError(): JsonResponse
    {
        return response()->json([
            'message' => 'ไม่สามารถดึงข้อมูลบุคลากรจากฐานข้อมูล local ได้ในขณะนี้',
        ], 502);
    }
}
