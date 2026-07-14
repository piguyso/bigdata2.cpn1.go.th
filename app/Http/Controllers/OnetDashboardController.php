<?php

namespace App\Http\Controllers;

use App\Services\LocalOnetDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class OnetDashboardController extends Controller
{
    public function __construct(private readonly LocalOnetDashboardService $onetDashboardService)
    {
    }

    public function index(): View
    {
        return view('onet-dashboard');
    }

    public function data(Request $request): JsonResponse
    {
        try {
            return response()->json(
                $this->onetDashboardService->getDashboardPayload(
                    $request->string('grade')->toString() ?: null,
                    $request->integer('year') ?: null,
                    $request->string('school_code')->toString() ?: null
                )
            );
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'ไม่สามารถดึงข้อมูล O-NET จากฐานข้อมูลภายในได้ในขณะนี้',
            ], 502);
        }
    }
}
