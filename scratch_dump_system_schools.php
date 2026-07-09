<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$schools = DB::table('system_school')->get();
echo "Total schools in system_school: " . count($schools) . "\n";
if (count($schools) > 0) {
    echo "First 5 schools:\n";
    foreach ($schools->slice(0, 5) as $s) {
        echo "- ID: {$s->id}, Name: {$s->schoolname}, Email: {$s->email}, Website: {$s->website}\n";
    }
}
