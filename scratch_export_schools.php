<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$schools = DB::table('system_school')->get();
$data = [];
foreach ($schools as $s) {
    $data[] = [
        'id' => $s->id,
        'smis' => $s->smis ?? '',
        'percode' => $s->percode ?? '',
        'schoolname' => trim(preg_replace('/\s+/', ' ', $s->schoolname)),
        'schoolname_eng' => trim(preg_replace('/\s+/', ' ', $s->schoolname_eng ?? '')),
        'email_db' => trim($s->email ?? ''),
        'website_db' => trim($s->website ?? ''),
        'tambon' => trim($s->tambon ?? ''),
        'amper' => trim($s->amper ?? ''),
        'province' => trim($s->province ?? ''),
    ];
}

file_put_contents('scratch_system_schools.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Exported " . count($data) . " schools to scratch_system_schools.json\n";
