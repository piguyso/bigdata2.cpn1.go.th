<?php

namespace App\Http\Controllers;

use App\Services\LocalAssetDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AssetDashboardController extends Controller
{
    public function __construct(private readonly LocalAssetDashboardService $assetDashboardService)
    {
    }

    public function index(): View
    {
        return view('asset-dashboard');
    }

    public function data(Request $request): JsonResponse
    {
        try {
            return response()->json($this->assetDashboardService->getDashboardPayload(
                $request->string('school_smis')->toString() ?: null,
                $request->string('building_type')->toString() ?: null,
                $request->string('condition')->toString() ?: null,
                $request->integer('school_page') ?: 1,
                $request->integer('type_page') ?: 1,
                $request->integer('building_page') ?: 1,
                $request->integer('per_page') ?: 15
            ));
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'ไม่สามารถดึงข้อมูล asset จากฐานข้อมูล local ได้ในขณะนี้',
            ], 502);
        }
    }
}
