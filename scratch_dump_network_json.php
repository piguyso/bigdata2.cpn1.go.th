<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$schools = DB::table('network_schools')->get();
$data = [];
foreach ($schools as $s) {
    $data[] = [
        'id' => $s->id,
        'name' => $s->name,
        'logo' => $s->logo,
        'district' => $s->district,
        'school_group' => $s->school_group,
        'address' => $s->address,
        'website' => $s->website,
    ];
}

file_put_contents('scratch_network_schools_all.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Exported " . count($data) . " schools from network_schools to scratch_network_schools_all.json\n";
