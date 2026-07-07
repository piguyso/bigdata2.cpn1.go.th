<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SlideController extends Controller
{
    /**
     * Get all slides as a JSON list.
     */
    public function getData()
    {
        try {
            $slides = DB::table('slides')
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->map(function ($slide) {
                    // Check if it's an external URL (e.g. Unsplash) or local storage path
                    if (str_starts_with($slide->image, 'http')) {
                        $slide->image_url = $slide->image;
                    } else {
                        $slide->image_url = asset('storage/' . $slide->image);
                    }
                    return $slide;
                });

            return response()->json([
                'status' => 'success',
                'data' => $slides
            ]);
        } catch (\Exception $e) {
            Log::error('SlideController@getData: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการโหลดข้อมูลสไลด์'
            ], 500);
        }
    }

    /**
     * Add a new slide.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'highlight' => ['nullable', 'string', 'max:255'],
                'slogan' => ['nullable', 'string', 'max:1000'],
                'badge' => ['nullable', 'string', 'max:255'],
                'link' => ['nullable', 'string', 'max:255'],
                'btn_text' => ['nullable', 'string', 'max:255'],
                'btn2_text' => ['nullable', 'string', 'max:255'],
                'btn2_link' => ['nullable', 'string', 'max:255'],
                'image_data' => ['required', 'string'], // base64 cropped image
            ]);

            $imagePath = '';
            if ($request->filled('image_data')) {
                $data = $request->input('image_data');
                if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                    $data = substr($data, strpos($data, ',') + 1);
                    $type = strtolower($type[1]);

                    if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $decodedData = base64_decode($data);
                        if ($decodedData !== false) {
                            $fileName = 'slide_' . time() . '_' . uniqid() . '.' . $type;
                            $imagePath = 'slides/' . $fileName;
                            Storage::disk('public')->put($imagePath, $decodedData);
                        }
                    }
                }
            }

            if (empty($imagePath)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ข้อมูลรูปภาพไม่ถูกต้อง'
                ], 422);
            }

            // Find next sort order
            $nextOrder = DB::table('slides')->max('sort_order') + 1;

            $slideId = DB::table('slides')->insertGetId([
                'title' => $request->input('title'),
                'highlight' => $request->input('highlight'),
                'slogan' => $request->input('slogan'),
                'badge' => $request->input('badge'),
                'link' => $request->input('link'),
                'btn_text' => $request->input('btn_text'),
                'btn2_text' => $request->input('btn2_text'),
                'btn2_link' => $request->input('btn2_link'),
                'image' => $imagePath,
                'sort_order' => $nextOrder,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'เพิ่มสไลด์หน้าแรกเรียบร้อยแล้ว',
                'id' => $slideId
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('SlideController@store: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถบันทึกข้อมูลสไลด์ได้'
            ], 500);
        }
    }

    /**
     * Update a slide.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'highlight' => ['nullable', 'string', 'max:255'],
                'slogan' => ['nullable', 'string', 'max:1000'],
                'badge' => ['nullable', 'string', 'max:255'],
                'link' => ['nullable', 'string', 'max:255'],
                'btn_text' => ['nullable', 'string', 'max:255'],
                'btn2_text' => ['nullable', 'string', 'max:255'],
                'btn2_link' => ['nullable', 'string', 'max:255'],
                'image_data' => ['nullable', 'string'], // base64 cropped image (optional if not changing)
            ]);

            $slide = DB::table('slides')->where('id', $id)->first();
            if (!$slide) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลสไลด์ที่ต้องการแก้ไข'
                ], 404);
            }

            $updateData = [
                'title' => $request->input('title'),
                'highlight' => $request->input('highlight'),
                'slogan' => $request->input('slogan'),
                'badge' => $request->input('badge'),
                'link' => $request->input('link'),
                'btn_text' => $request->input('btn_text'),
                'btn2_text' => $request->input('btn2_text'),
                'btn2_link' => $request->input('btn2_link'),
                'updated_at' => now(),
            ];

            if ($request->filled('image_data')) {
                $data = $request->input('image_data');
                if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                    $data = substr($data, strpos($data, ',') + 1);
                    $type = strtolower($type[1]);

                    if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $decodedData = base64_decode($data);
                        if ($decodedData !== false) {
                            $fileName = 'slide_' . time() . '_' . uniqid() . '.' . $type;
                            $imagePath = 'slides/' . $fileName;
                            Storage::disk('public')->put($imagePath, $decodedData);
                            
                            $updateData['image'] = $imagePath;

                            // Delete old image if it's a local file
                            if (!str_starts_with($slide->image, 'http')) {
                                Storage::disk('public')->delete($slide->image);
                            }
                        }
                    }
                }
            }

            DB::table('slides')->where('id', $id)->update($updateData);

            return response()->json([
                'status' => 'success',
                'message' => 'แก้ไขสไลด์เรียบร้อยแล้ว'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('SlideController@update: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถบันทึกการแก้ไขสไลด์ได้'
            ], 500);
        }
    }

    /**
     * Delete a slide.
     */
    public function destroy($id)
    {
        try {
            $slide = DB::table('slides')->where('id', $id)->first();
            if (!$slide) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลสไลด์ที่ต้องการลบ'
                ], 404);
            }

            // Delete old image if it's a local file
            if (!str_starts_with($slide->image, 'http')) {
                Storage::disk('public')->delete($slide->image);
            }

            DB::table('slides')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'ลบสไลด์เรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            Log::error('SlideController@destroy: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถลบสไลด์ได้'
            ], 500);
        }
    }

    /**
     * Save the order of slides.
     */
    public function saveOrder(Request $request)
    {
        try {
            $request->validate([
                'orders' => ['required', 'array'],
                'orders.*.id' => ['required', 'integer'],
                'orders.*.sort_order' => ['required', 'integer'],
            ]);

            DB::transaction(function () use ($request) {
                foreach ($request->input('orders') as $order) {
                    DB::table('slides')
                        ->where('id', $order['id'])
                        ->update(['sort_order' => $order['sort_order'], 'updated_at' => now()]);
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'บันทึกลำดับสไลด์เรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            Log::error('SlideController@saveOrder: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถบันทึกลำดับสไลด์ได้'
            ], 500);
        }
    }
}
