<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Show the website settings page.
     */
    public function edit()
    {
        // Still render the Blade view wrapper, but form data is loaded via API
        $webLogo = DB::table('settings')->where('key', 'web_logo')->value('value');
        return view('admin.settings', compact('webLogo'));
    }

    /**
     * Get all website settings as a JSON object.
     */
    public function getData()
    {
        try {
            $settings = DB::table('settings')->pluck('value', 'key');
            return response()->json([
                'status' => 'success',
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            Log::error('SettingsController@getData: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูลตั้งค่าได้'
            ], 500);
        }
    }

    /**
     * Save website settings via API (No reload).
     */
    public function saveSettings(Request $request)
    {
        try {
            $request->validate([
                'web_name'         => ['required', 'string', 'max:255'],
                'web_subtitle'     => ['nullable', 'string', 'max:255'],
                'area_code'        => ['nullable', 'string', 'regex:/^\d{1,20}$/'],
                'contact_email'    => ['nullable', 'email', 'max:255'],
                'contact_phone'    => ['nullable', 'string', 'max:50'],
                'contact_address'  => ['nullable', 'string', 'max:1000'],
                'web_logo_data'    => ['nullable', 'string'],
            ]);

            // 1. Process Logo Upload if sent
            if ($request->filled('web_logo_data')) {
                $data = $request->input('web_logo_data');
                
                if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                    $data = substr($data, strpos($data, ',') + 1);
                    $type = strtolower($type[1]); // png, jpeg, webp

                    if (in_array($type, ['jpg', 'jpeg', 'png', 'webp', 'svg'])) {
                        $decodedData = base64_decode($data);
                        
                        if ($decodedData !== false) {
                            $fileName = 'web_logo_' . time() . '_' . uniqid() . '.' . $type;
                            $filePath = 'web_logo/' . $fileName;
                            
                            // Save new logo file
                            Storage::disk('public')->put($filePath, $decodedData);
                            
                            // Delete old logo file if it exists
                            $oldLogo = DB::table('settings')->where('key', 'web_logo')->value('value');
                            if ($oldLogo) {
                                Storage::disk('public')->delete($oldLogo);
                            }
                            
                            // Save logo path to DB
                            DB::table('settings')->updateOrInsert(
                                ['key' => 'web_logo'],
                                [
                                    'value' => $filePath,
                                    'updated_at' => now()
                                ]
                            );
                        }
                    }
                }
            }

            // 2. Process other settings fields
            $fields = [
                'web_name',
                'web_subtitle',
                'area_code',
                'contact_email',
                'contact_phone',
                'contact_address',

            ];

            foreach ($fields as $field) {
                if ($request->has($field)) {
                    DB::table('settings')->updateOrInsert(
                        ['key' => $field],
                        [
                            'value' => $request->input($field),
                            'updated_at' => now()
                        ]
                    );
                }
            }

            // Fetch newly updated logo path to return to client (so we can update layout image instantly)
            $newLogoPath = DB::table('settings')->where('key', 'web_logo')->value('value');

            return response()->json([
                'status' => 'success',
                'message' => 'บันทึกการตั้งค่าเว็บไซต์เรียบร้อยแล้ว',
                'web_logo' => $newLogoPath ? asset('storage/' . $newLogoPath) : null,
                'web_name' => $request->input('web_name'),
                'web_subtitle' => $request->input('web_subtitle'),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('SettingsController@saveSettings: ' . $e->getMessage());
            
            // GDCC rule: Hide raw database or code exceptions. Return generic friendly message
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดภายในระบบ ไม่สามารถบันทึกข้อมูลตั้งค่าได้'
            ], 500);
        }
    }
}
