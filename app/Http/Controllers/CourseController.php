<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
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

            return response()->json([
                'status' => 'success',
                'data' => $courses
            ]);
        } catch (\Exception $e) {
            Log::error('CourseController@getData: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูลหลักสูตรได้'
            ], 500);
        }
    }

    /**
     * Get all courses for public landing page display.
     */
    public function getPublicList(Request $request)
    {
        try {
            $userId = Auth::id() ?: 0;
            
            $courses = DB::table('lms_courses as c')
                ->select([
                    'c.id',
                    'c.title',
                    'c.description',
                    'c.cover_url',
                    'c.status',
                    DB::raw("(SELECT COUNT(*) FROM lms_lessons l WHERE l.course_id = c.id) as lesson_count"),
                    DB::raw("(SELECT COUNT(*) FROM lms_enrollments e WHERE e.course_id = c.id) as learner_count"),
                    DB::raw($userId ? "(SELECT COUNT(*) FROM lms_lesson_progress p WHERE p.user_id = $userId AND p.course_id = c.id) as completed_lessons" : "0 as completed_lessons"),
                    DB::raw($userId ? "(SELECT COUNT(*) FROM lms_enrollments e WHERE e.user_id = $userId AND e.course_id = c.id) as enrolled" : "0 as enrolled")
                ])
                ->where('c.status', 'published')
                ->orderBy('c.created_at', 'desc')
                ->get()
                ->map(function ($course) use ($userId) {
                    $courseId = (int)$course->id;
                    $lessonCount = (int)$course->lesson_count;
                    $completed = (int)$course->completed_lessons;
                    $course->progress_pct = $lessonCount > 0 ? (int)round(($completed / $lessonCount) * 100) : 0;
                    $course->enrolled = (int)$course->enrolled > 0;
                    
                    // Fallback cover image
                    $course->cover_image_url = $course->cover_url ?: null;

                    // Get pre-quiz & post-quiz percents
                    $prePercent = null;
                    $postPercent = null;
                    $improvement = null;

                    if ($userId > 0) {
                        $quizzes = DB::table('lms_quizzes')
                            ->where('course_id', $courseId)
                            ->where('is_active', 1)
                            ->get();

                        $preQuiz = $quizzes->where('quiz_type', 'pre')->first();
                        $postQuiz = $quizzes->where('quiz_type', 'post')->first();

                        if ($preQuiz) {
                            $preAttempt = DB::table('lms_quiz_attempts')
                                ->where('quiz_id', $preQuiz->id)
                                ->where('user_id', $userId)
                                ->orderBy('id', 'desc')
                                ->first();
                            if ($preAttempt) {
                                $prePercent = (float)$preAttempt->percent;
                            }
                        }

                        if ($postQuiz) {
                            $postAttempt = DB::table('lms_quiz_attempts')
                                ->where('quiz_id', $postQuiz->id)
                                ->where('user_id', $userId)
                                ->orderBy('id', 'desc')
                                ->first();
                            if ($postAttempt) {
                                $postPercent = (float)$postAttempt->percent;
                            }
                        }

                        if ($prePercent !== null && $postPercent !== null) {
                            $improvement = $postPercent - $prePercent;
                        }
                    }

                    $course->pre_percent = $prePercent;
                    $course->post_percent = $postPercent;
                    $course->improvement = $improvement;

                    return $course;
                });

            return response()->json([
                'status' => 'success',
                'data' => $courses
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
                'academic_year' => ['nullable', 'string', 'max:255'],
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
                $data = $request->input('cover_image_data');
                
                if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                    $data = substr($data, strpos($data, ',') + 1);
                    $type = strtolower($type[1]);

                    if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $decodedData = base64_decode($data);
                        
                        if ($decodedData !== false) {
                            if ($coverPath) {
                                Storage::disk('public')->delete($coverPath);
                            }

                            $fileName = 'course_cover_' . time() . '_' . uniqid() . '.' . $type;
                            $coverPath = 'courses/covers/' . $fileName;
                            
                            Storage::disk('public')->put($coverPath, $decodedData);
                        }
                    }
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
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Img, $type)) {
                    $data = substr($base64Img, strpos($base64Img, ',') + 1);
                    $type = strtolower($type[1]);

                    if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $decodedData = base64_decode($data);
                        
                        if ($decodedData !== false) {
                            $fileName = 'report_img_' . time() . '_' . uniqid() . '.' . $type;
                            $filePath = 'courses/reports/' . $fileName;
                            
                            Storage::disk('public')->put($filePath, $decodedData);
                            $reportImages[] = $filePath;
                        }
                    }
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
                    $fileName = $newFile['name'];
                    $base64Data = $newFile['data'];

                    if (preg_match('/^data:([^;]+);base64,/', $base64Data, $mime)) {
                        $data = substr($base64Data, strpos($base64Data, ',') + 1);
                        $decodedData = base64_decode($data);

                        if ($decodedData !== false) {
                            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                            if (!$ext) {
                                $ext = 'bin';
                            }

                            $safeFileName = 'report_file_' . time() . '_' . uniqid() . '.' . $ext;
                            $filePath = 'courses/files/' . $safeFileName;

                            Storage::disk('public')->put($filePath, $decodedData);
                            $reportFiles[] = [
                                'name' => $fileName,
                                'path' => $filePath
                            ];
                        }
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
}
