<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$schools = DB::table('network_schools')->whereNotNull('logo')->where('logo', '<>', '')->get();
echo "Schools with logo in network_schools: " . count($schools) . "\n";
foreach ($schools as $s) {
    echo "- ID: {$s->id}, Name: {$s->name}, Logo: {$s->logo}\n";
}
