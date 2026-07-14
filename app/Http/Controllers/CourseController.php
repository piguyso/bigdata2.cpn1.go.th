<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
    private const ALLOWED_IMAGE_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    private const ALLOWED_REPORT_FILE_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
    private const ALLOWED_REPORT_FILE_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];
    private const MAX_IMAGE_BYTES = 5_242_880;
    private const MAX_REPORT_FILE_BYTES = 15_728_640;

    /**
     * Show the courses admin page.
     */
    public function index()
    {
        return view('admin.courses');
    }

    /**
     * Get all courses as a JSON list (Admin).
     */
    public function getData()
    {
        try {
            $courses = DB::table('courses')
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($course) {
                    $course->cover_image_url = $course->cover_image ? asset('storage/' . $course->cover_image) : null;
                    
                    $reportImages = json_decode($course->report_images, true) ?: [];
                    $course->report_images_urls = array_map(function ($path) {
                        return [
                            'path' => $path,
                            'url' => asset('storage/' . $path)
                        ];
                    }, $reportImages);

                    $reportFiles = json_decode($course->report_files, true) ?: [];
                    $course->report_files_urls = array_map(function ($file) {
                        return [
                            'name' => $file['name'],
                            'path' => $file['path'],
                            'url' => asset('storage/' . $file['path'])
                        ];
                    }, $reportFiles);

                    return $course;
                });

            $academicYears = DB::table('academic_years')
                ->orderByDesc('sort_order')
                ->orderByDesc('year')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $courses,
                'academic_years' => $academicYears,
                'active_academic_year' => $academicYears->firstWhere('is_active', true)->year ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('CourseController@getData: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูลหลักสูตรได้'
            ], 500);
        }
    }

    public function getPublicList(Request $request)
    {
        try {
            $query = DB::table('courses');

            if ($request->filled('academic_year')) {
                $query->where('academic_year', $request->query('academic_year'));
            }

            $courses = $query->orderBy('sort_order', 'asc')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($course) {
                    $course->cover_image_url = $course->cover_image ? asset('storage/' . $course->cover_image) : null;

                    return $course;
                });

            return response()->json([
                'status' => 'success',
                'data' => $courses,
                'active_academic_year' => DB::table('academic_years')
                    ->where('is_active', true)
                    ->orderByDesc('sort_order')
                    ->orderByDesc('year')
                    ->value('year'),
            ]);
        } catch (\Exception $e) {
            Log::error('CourseController@getPublicList: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการโหลดข้อมูลหลักสูตรอบรม'
            ], 500);
        }
    }

    /**
     * Create or update a course.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'id' => ['nullable', 'integer'],
                'title' => ['required', 'string', 'max:255'],
                'hours' => ['nullable', 'string', 'max:255'],
                'academic_year' => ['nullable', 'string', 'max:4'],
                'objectives' => ['nullable', 'string'],
                'registration_link' => ['nullable', 'url', 'max:500'],
                'target_group' => ['nullable', 'string', 'max:255'],
                'location' => ['nullable', 'string', 'max:255'],
                'status' => ['required', 'string', 'in:upcoming,open,ongoing,closed'],
                'sort_order' => ['nullable', 'integer'],
                'duration_text' => ['nullable', 'string', 'max:255'],
                'report_text' => ['nullable', 'string'],
                'cover_image_data' => ['nullable', 'string'],
                'delete_cover_image' => ['nullable', 'boolean'],
                'existing_report_images' => ['nullable', 'array'],
                'new_report_images' => ['nullable', 'array'],
                'existing_report_files' => ['nullable', 'array'],
                'new_report_files' => ['nullable', 'array'],
            ]);

            $id = $request->input('id');
            $coverPath = null;
            $reportImages = [];
            $reportFiles = [];

            if ($id) {
                $currentCourse = DB::table('courses')->where('id', $id)->first();
                if (!$currentCourse) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'ไม่พบข้อมูลหลักสูตรที่ระบุ'
                    ], 404);
                }
                $coverPath = $currentCourse->cover_image;
                $reportImages = json_decode($currentCourse->report_images, true) ?: [];
                $reportFiles = json_decode($currentCourse->report_files, true) ?: [];
            }

            // 1. Process cover image deletion
            if ($request->input('delete_cover_image') === true || $request->input('delete_cover_image') === 1) {
                if ($coverPath) {
                    Storage::disk('public')->delete($coverPath);
                    $coverPath = null;
                }
            }

            // 2. Process cover image upload (Base64)
            if ($request->filled('cover_image_data')) {
                $parsedImage = $this->parseBase64Image($request->input('cover_image_data'));

                if ($parsedImage !== null) {
                    if ($coverPath) {
                        Storage::disk('public')->delete($coverPath);
                    }

                    $fileName = 'course_cover_' . time() . '_' . uniqid() . '.' . $parsedImage['extension'];
                    $coverPath = 'courses/covers/' . $fileName;

                    Storage::disk('public')->put($coverPath, $parsedImage['data']);
                }
            }

            // 3. Process report images (Multiple attachments)
            $existingToKeep = $request->input('existing_report_images', []);
            $normalizedKeep = [];

            foreach ($existingToKeep as $img) {
                if (str_contains($img, 'storage/')) {
                    $parts = explode('storage/', $img);
                    $normalizedKeep[] = end($parts);
                } else {
                    $normalizedKeep[] = $img;
                }
            }

            // Delete removed files from storage
            foreach ($reportImages as $img) {
                if (!in_array($img, $normalizedKeep)) {
                    Storage::disk('public')->delete($img);
                }
            }
            $reportImages = $normalizedKeep;

            // Save new base64 report images
            $newImages = $request->input('new_report_images', []);
            foreach ($newImages as $base64Img) {
                $parsedImage = $this->parseBase64Image($base64Img);

                if ($parsedImage !== null) {
                    $fileName = 'report_img_' . time() . '_' . uniqid() . '.' . $parsedImage['extension'];
                    $filePath = 'courses/reports/' . $fileName;

                    Storage::disk('public')->put($filePath, $parsedImage['data']);
                    $reportImages[] = $filePath;
                }
            }

            // 4. Process report files (Multiple attachments)
            $existingFilesToKeep = $request->input('existing_report_files', []);
            $normalizedFilesKeep = [];
            $keepPaths = [];

            foreach ($existingFilesToKeep as $file) {
                if (isset($file['path'])) {
                    $path = $file['path'];
                    if (str_contains($path, 'storage/')) {
                        $parts = explode('storage/', $path);
                        $path = end($parts);
                    }
                    $keepPaths[] = $path;
                    $normalizedFilesKeep[] = [
                        'name' => $file['name'],
                        'path' => $path
                    ];
                }
            }

            // Delete removed files from storage
            foreach ($reportFiles as $file) {
                if (isset($file['path']) && !in_array($file['path'], $keepPaths)) {
                    Storage::disk('public')->delete($file['path']);
                }
            }
            $reportFiles = $normalizedFilesKeep;

            // Save new base64 report files
            $newFiles = $request->input('new_report_files', []);
            foreach ($newFiles as $newFile) {
                if (isset($newFile['name']) && isset($newFile['data'])) {
                    $parsedFile = $this->parseBase64ReportFile($newFile['name'], $newFile['data']);

                    if ($parsedFile !== null) {
                        $safeFileName = 'report_file_' . time() . '_' . uniqid() . '.' . $parsedFile['extension'];
                        $filePath = 'courses/files/' . $safeFileName;

                        Storage::disk('public')->put($filePath, $parsedFile['data']);
                        $reportFiles[] = [
                            'name' => $parsedFile['original_name'],
                            'path' => $filePath
                        ];
                    }
                }
            }

            $dataToSave = [
                'title' => $request->input('title'),
                'hours' => $request->input('hours'),
                'academic_year' => $request->input('academic_year'),
                'objectives' => $request->input('objectives'),
                'registration_link' => $request->input('registration_link'),
                'target_group' => $request->input('target_group'),
                'location' => $request->input('location'),
                'status' => $request->input('status'),
                'sort_order' => intval($request->input('sort_order', 0)),
                'duration_text' => $request->input('duration_text'),
                'report_text' => $request->input('report_text'),
                'cover_image' => $coverPath,
                'report_images' => !empty($reportImages) ? json_encode($reportImages) : null,
                'report_files' => !empty($reportFiles) ? json_encode($reportFiles) : null,
                'updated_at' => now(),
            ];

            if ($id) {
                DB::table('courses')->where('id', $id)->update($dataToSave);
                $message = 'แก้ไขข้อมูลหลักสูตรอบรมเรียบร้อยแล้ว';
            } else {
                $dataToSave['created_at'] = now();
                DB::table('courses')->insert($dataToSave);
                $message = 'เพิ่มหลักสูตรอบรมเรียบร้อยแล้ว';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('CourseController@store: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูลหลักสูตรอบรม'
            ], 500);
        }
    }

    /**
     * Delete a course.
     */
    public function destroy($id)
    {
        try {
            $course = DB::table('courses')->where('id', $id)->first();
            
            if (!$course) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลหลักสูตร'
                ], 404);
            }

            // Delete cover image
            if ($course->cover_image) {
                Storage::disk('public')->delete($course->cover_image);
            }

            // Delete all report images
            $reportImages = json_decode($course->report_images, true) ?: [];
            foreach ($reportImages as $img) {
                Storage::disk('public')->delete($img);
            }

            // Delete all report files
            $reportFiles = json_decode($course->report_files, true) ?: [];
            foreach ($reportFiles as $file) {
                if (isset($file['path'])) {
                    Storage::disk('public')->delete($file['path']);
                }
            }

            DB::table('courses')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'ลบหลักสูตรอบรมเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            Log::error('CourseController@destroy: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบหลักสูตรอบรม'
            ], 500);
        }
    }

    /**
     * Show the public standalone page of a course.
     */
    public function publicShow($id)
    {
        try {
            $course = DB::table('courses')->where('id', $id)->first();

            if (!$course) {
                return abort(404, 'ไม่พบหลักสูตรอบรมที่ต้องการ');
            }

            $course->cover_image_url = $course->cover_image ? asset('storage/' . $course->cover_image) : null;
            
            $reportImages = json_decode($course->report_images, true) ?: [];
            $course->report_images_urls = array_map(function ($path) {
                return asset('storage/' . $path);
            }, $reportImages);

            $reportFiles = json_decode($course->report_files, true) ?: [];
            $course->report_files_urls = array_map(function ($file) {
                return [
                    'name' => $file['name'],
                    'url' => asset('storage/' . $file['path'])
                ];
            }, $reportFiles);

            return view('course-detail', compact('course'));
        } catch (\Exception $e) {
            Log::error('CourseController@publicShow: ' . $e->getMessage());
            return redirect('/');
        }
    }

    private function parseBase64Image(?string $payload): ?array
    {
        if (! is_string($payload) || ! preg_match('/^data:(image\/[a-z0-9.+-]+);base64,/i', $payload, $matches)) {
            return null;
        }

        $mimeType = strtolower($matches[1]);
        if (! in_array($mimeType, self::ALLOWED_IMAGE_MIME_TYPES, true)) {
            return null;
        }

        $data = substr($payload, strpos($payload, ',') + 1);
        $decodedData = base64_decode($data, true);

        if ($decodedData === false || strlen($decodedData) > self::MAX_IMAGE_BYTES) {
            return null;
        }

        $imageInfo = @getimagesizefromstring($decodedData);
        $detectedMime = strtolower((string) ($imageInfo['mime'] ?? ''));

        if (! in_array($detectedMime, self::ALLOWED_IMAGE_MIME_TYPES, true)) {
            return null;
        }

        $extension = match ($detectedMime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };

        if ($extension === null || ! in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS, true)) {
            return null;
        }

        return [
            'data' => $decodedData,
            'extension' => $extension,
            'mime' => $detectedMime,
        ];
    }

    private function parseBase64ReportFile(string $originalName, string $payload): ?array
    {
        if (! preg_match('/^data:([^;]+);base64,/i', $payload, $matches)) {
            return null;
        }

        $mimeType = strtolower($matches[1]);
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));

        if ($extension === '' || ! in_array($extension, self::ALLOWED_REPORT_FILE_EXTENSIONS, true) || ! in_array($mimeType, self::ALLOWED_REPORT_FILE_MIME_TYPES, true)) {
            return null;
        }

        $data = substr($payload, strpos($payload, ',') + 1);
        $decodedData = base64_decode($data, true);

        if ($decodedData === false || strlen($decodedData) > self::MAX_REPORT_FILE_BYTES) {
            return null;
        }

        return [
            'data' => $decodedData,
            'extension' => $extension,
            'mime' => $mimeType,
            'original_name' => basename($originalName),
        ];
    }
}
