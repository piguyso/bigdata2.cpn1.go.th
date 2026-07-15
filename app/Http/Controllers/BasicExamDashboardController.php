<?php

namespace App\Http\Controllers;

use App\Services\LocalBasicExamDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class BasicExamDashboardController extends Controller
{
    public function __construct(private readonly LocalBasicExamDashboardService $dashboardService)
    {
    }

    public function ntIndex(): View
    {
        return view('basic-exam-dashboard', [
            'examType' => 'nt',
            'examTitle' => 'NT',
            'apiRoute' => route('api.nt.dashboard'),
            'pageDescription' => 'แสดงข้อมูล NT ของ สพป.ชุมพร เขต 1 จากฐานข้อมูลภายใน',
        ]);
    }

    public function rtIndex(): View
    {
        return view('basic-exam-dashboard', [
            'examType' => 'rt',
            'examTitle' => 'RT',
            'apiRoute' => route('api.rt.dashboard'),
            'pageDescription' => 'แสดงข้อมูล RT ของ สพป.ชุมพร เขต 1 จากฐานข้อมูลภายใน',
        ]);
    }

    public function ntData(Request $request): JsonResponse
    {
        return $this->data('nt', $request);
    }

    public function rtData(Request $request): JsonResponse
    {
        return $this->data('rt', $request);
    }

    private function data(string $examType, Request $request): JsonResponse
    {
        try {
            return response()->json(
                $this->dashboardService->getDashboardPayload(
                    $examType,
                    $request->integer('year') ?: null,
                    $request->string('school_code')->toString() ?: null
                )
            );
        } catch (Throwable) {
            return response()->json([
                'message' => 'ไม่สามารถดึงข้อมูล '.strtoupper($examType).' จากฐานข้อมูลภายในได้ในขณะนี้',
            ], 502);
        }
    }
}
