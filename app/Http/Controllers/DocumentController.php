<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    private const ALLOWED_DOCUMENT_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'webp',
    ];

    private const ALLOWED_DOCUMENT_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Show the documents management admin page.
     */
    public function index()
    {
        return view('admin.documents');
    }

    /**
     * Show the public documents library page.
     */
    public function publicIndex()
    {
        try {
            $documents = DB::table('documents')
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            return view('documents', compact('documents'));
        } catch (\Exception $e) {
            Log::error('DocumentController@publicIndex: ' . $e->getMessage());
            return redirect('/');
        }
    }

    /**
     * Get all documents as JSON (Admin).
     */
    public function getData()
    {
        try {
            $documents = DB::table('documents')
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $documents
            ]);
        } catch (\Exception $e) {
            Log::error('DocumentController@getData: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูลเอกสารได้'
            ], 500);
        }
    }

    /**
     * Create or update a document.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'id' => ['nullable', 'integer'],
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'sort_order' => ['required', 'integer'],
                'file' => ['nullable', 'file', 'max:15360', 'mimes:' . implode(',', self::ALLOWED_DOCUMENT_EXTENSIONS), 'mimetypes:' . implode(',', self::ALLOWED_DOCUMENT_MIME_TYPES)],
            ]);

            $id = $request->input('id');
            $filePath = null;
            $fileType = null;
            $fileSize = null;

            if ($id) {
                $currentDoc = DB::table('documents')->where('id', $id)->first();
                if (!$currentDoc) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'ไม่พบข้อมูลเอกสารที่ระบุ'
                    ], 404);
                }
                $filePath = $currentDoc->file_path;
                $fileType = $currentDoc->file_type;
                $fileSize = $currentDoc->file_size;
            } else {
                // If it's a new document, the file is required
                $request->validate([
                    'file' => ['required', 'file', 'max:15360', 'mimes:' . implode(',', self::ALLOWED_DOCUMENT_EXTENSIONS), 'mimetypes:' . implode(',', self::ALLOWED_DOCUMENT_MIME_TYPES)],
                ]);
            }

            // Handle file upload if present
            if ($request->hasFile('file')) {
                $uploadedFile = $request->file('file');
                
                // Delete old file if updating
                if ($id && $filePath) {
                    Storage::disk('public')->delete($filePath);
                }

                // Get metadata
                $extension = strtolower($uploadedFile->getClientOriginalExtension());
                $sizeBytes = $uploadedFile->getSize();
                $mimeType = strtolower((string) $uploadedFile->getMimeType());

                if (! in_array($extension, self::ALLOWED_DOCUMENT_EXTENSIONS, true) || ! in_array($mimeType, self::ALLOWED_DOCUMENT_MIME_TYPES, true)) {
                    return response()->json([
                        'status' => 'validation_error',
                        'errors' => [
                            'file' => ['ชนิดไฟล์นี้ไม่ได้รับอนุญาต'],
                        ],
                    ], 422);
                }

                // Format size
                if ($sizeBytes >= 1048576) {
                    $fileSize = number_format($sizeBytes / 1048576, 2) . ' MB';
                } elseif ($sizeBytes >= 1024) {
                    $fileSize = number_format($sizeBytes / 1024, 2) . ' KB';
                } else {
                    $fileSize = $sizeBytes . ' Bytes';
                }

                $fileType = $extension;

                // Save file
                $fileName = 'doc_' . time() . '_' . uniqid() . '.' . $extension;
                $filePath = $uploadedFile->storeAs('documents', $fileName, 'public');
            }

            $dataToSave = [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'sort_order' => $request->input('sort_order'),
                'file_path' => $filePath,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'updated_at' => now(),
            ];

            if ($id) {
                DB::table('documents')->where('id', $id)->update($dataToSave);
                $message = 'แก้ไขข้อมูลเอกสารเรียบร้อยแล้ว';
            } else {
                $dataToSave['download_count'] = 0;
                $dataToSave['created_at'] = now();
                DB::table('documents')->insert($dataToSave);
                $message = 'เพิ่มเอกสารเรียบร้อยแล้ว';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('DocumentController@store: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการบันทึกเอกสาร'
            ], 500);
        }
    }

    /**
     * Download document file and increment download count.
     */
    public function download($id)
    {
        try {
            $document = DB::table('documents')->where('id', $id)->first();

            if (!$document || !Storage::disk('public')->exists($document->file_path)) {
                return abort(404, 'ไม่พบไฟล์เอกสาร');
            }

            // Increment download count
            DB::table('documents')->where('id', $id)->increment('download_count');

            // Download file
            return Storage::disk('public')->download($document->file_path, $document->title . '.' . $document->file_type);
        } catch (\Exception $e) {
            Log::error('DocumentController@download: ' . $e->getMessage());
            return abort(500, 'เกิดข้อผิดพลาดในการดาวน์โหลด');
        }
    }

    /**
     * Delete a document.
     */
    public function destroy($id)
    {
        try {
            $document = DB::table('documents')->where('id', $id)->first();

            if (!$document) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลเอกสาร'
                ], 404);
            }

            // Delete actual file
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            // Delete record
            DB::table('documents')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'ลบเอกสารเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            Log::error('DocumentController@destroy: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบเอกสาร'
            ], 500);
        }
    }

    /**
     * Get documents for public listing.
     */
    public function getPublicList()
    {
        try {
            $documents = DB::table('documents')
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'desc')
                ->take(4) // Show top 4 latest documents on the homepage
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $documents
            ]);
        } catch (\Exception $e) {
            Log::error('DocumentController@getPublicList: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการโหลดคลังเอกสาร'
            ], 500);
        }
    }
}
