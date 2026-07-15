<?php

namespace App\Http\Controllers;

use App\Support\SchoolLogo;
use App\Support\SimpleXlsxExporter;
use App\Support\TabularFileReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminSchoolController extends Controller
{
    private const IMPORT_COLUMNS = [
        'group_code',
        'group_name',
        'smis',
        'percode',
        'ministry',
        'schoolname',
        'schoolname_eng',
        'muti',
        'road',
        'muban',
        'tambon',
        'amper',
        'province',
        'postcode',
        'lat',
        'lng',
        'length_km',
        'maplink',
        'tel',
        'email',
        'website',
        'statusID',
        'statusDetail',
    ];

    public function index()
    {
        return view('admin.schools');
    }

    public function getData()
    {
        try {
            $schools = DB::table('system_school as schools')
                ->leftJoin('system_group as groups', 'schools.schoolgroup', '=', 'groups.code')
                ->select('schools.*', 'groups.name as schoolgroup_name')
                ->orderBy('schools.schoolgroup')
                ->orderBy('schools.schoolname')
                ->get()
                ->map(function ($school) {
                    $school->logo_url = SchoolLogo::url($school->logo_path ?? null);

                    return $school;
                });

            $groups = DB::table('system_group')
                ->orderBy('code')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $schools,
                'groups' => $groups,
            ]);
        } catch (\Exception $e) {
            Log::error('AdminSchoolController@getData: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูลโรงเรียนได้',
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        return SimpleXlsxExporter::downloadCsv(
            'school-import-template.csv',
            self::IMPORT_COLUMNS,
            []
        );
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        try {
            $rows = TabularFileReader::rows(
                $validated['file']->getRealPath(),
                $validated['file']->getClientOriginalName()
            );

            if (count($rows) < 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไฟล์ไม่มีข้อมูลโรงเรียนสำหรับนำเข้า',
                ], 422);
            }

            $headers = array_map(fn ($header) => trim((string) $header), $rows[0]);
            $missingHeaders = array_values(array_diff(self::IMPORT_COLUMNS, $headers));

            if ($missingHeaders !== []) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'โครงสร้างไฟล์ไม่ถูกต้อง: หัวคอลัมน์ไม่ครบ '.implode(', ', $missingHeaders),
                ], 422);
            }

            $headerMap = array_flip($headers);
            $summary = [
                'groups_created' => 0,
                'groups_updated' => 0,
                'schools_created' => 0,
                'schools_updated' => 0,
                'skipped_rows' => 0,
                'warnings' => [],
            ];

            DB::transaction(function () use ($rows, $headerMap, &$summary) {
                foreach (array_slice($rows, 1) as $index => $row) {
                    $rowNumber = $index + 2;
                    $data = $this->normalizeImportRow($row, $headerMap);

                    if ($this->isBlankImportRow($data)) {
                        $summary['skipped_rows']++;
                        continue;
                    }

                    $required = [
                        'group_code' => 'group_code',
                        'group_name' => 'group_name',
                        'smis' => 'smis',
                        'schoolname' => 'schoolname',
                    ];

                    foreach ($required as $field => $label) {
                        if ($data[$field] === '') {
                            $summary['warnings'][] = "แถว {$rowNumber}: ขาด {$label}";
                            $summary['skipped_rows']++;
                            continue 2;
                        }
                    }

                    if (mb_strlen($data['group_code']) > 2) {
                        $summary['warnings'][] = "แถว {$rowNumber}: group_code ต้องไม่เกิน 2 ตัวอักษร";
                        $summary['skipped_rows']++;
                        continue;
                    }

                    $existingGroup = DB::table('system_group')->where('code', $data['group_code'])->first();
                    DB::table('system_group')->updateOrInsert(
                        ['code' => $data['group_code']],
                        ['name' => $data['group_name']]
                    );
                    $existingGroup ? $summary['groups_updated']++ : $summary['groups_created']++;

                    $schoolData = [
                        'smis' => $data['smis'],
                        'percode' => $data['percode'],
                        'ministry' => $data['ministry'],
                        'schoolname' => $data['schoolname'],
                        'schoolname_eng' => $data['schoolname_eng'],
                        'schoolgroup' => $data['group_code'],
                        'muti' => $data['muti'],
                        'road' => $data['road'],
                        'muban' => $data['muban'],
                        'tambon' => $data['tambon'],
                        'amper' => $data['amper'],
                        'province' => $data['province'] !== '' ? $data['province'] : 'ชุมพร',
                        'postcode' => $data['postcode'],
                        'lat' => $data['lat'],
                        'lng' => $data['lng'],
                        'length_km' => $data['length_km'],
                        'maplink' => $data['maplink'],
                        'tel' => $data['tel'],
                        'email' => $data['email'],
                        'website' => $data['website'],
                        'statusID' => $data['statusID'] !== '' ? $data['statusID'] : '1',
                        'statusDetail' => $data['statusDetail'] !== '' ? $data['statusDetail'] : 'เปิด',
                    ];

                    $existingSchool = DB::table('system_school')->where('smis', $data['smis'])->first();
                    DB::table('system_school')->updateOrInsert(
                        ['smis' => $data['smis']],
                        $schoolData
                    );
                    $existingSchool ? $summary['schools_updated']++ : $summary['schools_created']++;
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'นำเข้าข้อมูลโรงเรียนเรียบร้อยแล้ว',
                'summary' => $summary,
            ]);
        } catch (\Exception $e) {
            Log::error('AdminSchoolController@import: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'เกิดข้อผิดพลาดในการนำเข้าข้อมูลโรงเรียน',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $id = $request->input('id');
        $existingSchool = $id ? DB::table('system_school')->where('id', $id)->first() : null;

        try {
            $validated = $request->validate([
                'id' => ['nullable', 'integer'],
                'smis' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('system_school', 'smis')->ignore($id),
                ],
                'percode' => ['nullable', 'string', 'max:20'],
                'ministry' => ['nullable', 'string', 'max:20'],
                'schoolname' => ['required', 'string', 'max:1500'],
                'schoolname_eng' => ['nullable', 'string', 'max:999'],
                'schoolgroup' => ['required', 'string', Rule::exists('system_group', 'code')],
                'muti' => ['nullable', 'string', 'max:10'],
                'road' => ['nullable', 'string', 'max:100'],
                'muban' => ['nullable', 'string', 'max:100'],
                'tambon' => ['nullable', 'string', 'max:100'],
                'amper' => ['nullable', 'string', 'max:100'],
                'province' => ['nullable', 'string', 'max:100'],
                'postcode' => ['nullable', 'string', 'max:100'],
                'lat' => ['nullable', 'string', 'max:80'],
                'lng' => ['nullable', 'string', 'max:80'],
                'length_km' => ['nullable', 'string', 'max:10'],
                'maplink' => ['nullable', 'string', 'max:255'],
                'tel' => ['nullable', 'string', 'max:20'],
                'email' => ['nullable', 'email', 'max:150'],
                'website' => ['nullable', 'string', 'max:150'],
                'statusID' => ['nullable', 'string', 'max:1'],
                'statusDetail' => ['nullable', 'string', 'max:20'],
            ]);

            if ($id && ! DB::table('system_school')->where('id', $id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลโรงเรียนที่ระบุ',
                ], 404);
            }

            $dataToSave = collect([
                'smis', 'percode', 'ministry', 'schoolname', 'schoolname_eng', 'schoolgroup',
                'muti', 'road', 'muban', 'tambon', 'amper', 'province', 'postcode',
                'lat', 'lng', 'length_km', 'maplink', 'tel', 'email', 'website',
                'statusID', 'statusDetail',
            ])->mapWithKeys(function ($field) use ($validated) {
                return [$field => $validated[$field] ?? ''];
            })->all();

            $dataToSave['province'] = $dataToSave['province'] ?: 'ชุมพร';
            $dataToSave['statusID'] = $dataToSave['statusID'] ?: '1';
            $dataToSave['statusDetail'] = $dataToSave['statusDetail'] ?: 'เปิด';

            if ($existingSchool) {
                $latChanged = trim((string) $existingSchool->lat) !== trim((string) $dataToSave['lat']);
                $lngChanged = trim((string) $existingSchool->lng) !== trim((string) $dataToSave['lng']);

                if ($latChanged || $lngChanged) {
                    $dataToSave['length_km'] = '';
                }
            }

            if ($id) {
                DB::table('system_school')->where('id', $id)->update($dataToSave);
                $message = 'แก้ไขข้อมูลโรงเรียนเรียบร้อยแล้ว';
            } else {
                DB::table('system_school')->insert($dataToSave);
                $message = 'เพิ่มข้อมูลโรงเรียนเรียบร้อยแล้ว';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('AdminSchoolController@store: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูลโรงเรียน',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $school = DB::table('system_school')->where('id', $id)->first();

            if (! $school) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลโรงเรียน',
                ], 404);
            }

            DB::table('system_school')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'ลบข้อมูลโรงเรียนเรียบร้อยแล้ว',
            ]);
        } catch (\Exception $e) {
            Log::error('AdminSchoolController@destroy: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูลโรงเรียน',
            ], 500);
        }
    }

    private function normalizeImportRow(array $row, array $headerMap): array
    {
        return collect(self::IMPORT_COLUMNS)
            ->mapWithKeys(function (string $column) use ($row, $headerMap) {
                $index = $headerMap[$column] ?? null;

                return [$column => trim((string) ($index !== null ? ($row[$index] ?? '') : ''))];
            })
            ->all();
    }

    private function isBlankImportRow(array $data): bool
    {
        foreach ($data as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
