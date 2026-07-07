<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$schools = DB::table('network_schools')->get();
$toScan = [];

foreach ($schools as $school) {
    $web = $school->website;
    // If website contains bopp-obec or is empty, we need to scan it!
    if (empty($web) || str_contains($web, 'bopp-obec.info') || str_contains($web, 'data.bopp')) {
        $toScan[] = [
            'id' => $school->id,
            'name' => $school->name
        ];
    }
}

file_put_contents('schools_to_scan.json', json_encode($toScan, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Saved " . count($toScan) . " schools to schools_to_scan.json\n";
