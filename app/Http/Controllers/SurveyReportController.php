<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SurveyReportController extends Controller
{
    /**
     * Display the report page.
     */
    public function index(): View
    {
        return view('reports');
    }

    /**
     * Teacher survey tables were removed, so reports return an empty paginator.
     */
    public function getData(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'ระบบรายงานข้อมูลครูถูกปิดใช้งานแล้ว',
            'data' => [
                'current_page' => 1,
                'data' => [],
                'first_page_url' => null,
                'from' => null,
                'last_page' => 1,
                'last_page_url' => null,
                'links' => [],
                'next_page_url' => null,
                'path' => $request->url(),
                'per_page' => 8,
                'prev_page_url' => null,
                'to' => null,
                'total' => 0,
            ],
        ]);
    }

    /**
     * Deleting teacher survey records is no longer available.
     */
    public function destroy($id): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'ระบบรายงานข้อมูลครูถูกปิดใช้งานแล้ว',
        ], 410);
    }

    /**
     * Exporting teacher survey records is no longer available.
     */
    public function exportExcel(Request $request): JsonResponse
    {
        if (! $request->user() || $request->user()->role !== 'admin') {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงฟังก์ชันนี้');
        }

        return response()->json([
            'status' => 'error',
            'message' => 'ระบบรายงานข้อมูลครูถูกปิดใช้งานแล้ว',
        ], 410);
    }
}
