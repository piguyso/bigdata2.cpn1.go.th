<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$schools = DB::table('system_school')->get();
$has_website = 0;
$has_email = 0;
foreach ($schools as $s) {
    if (!empty(trim($s->website))) $has_website++;
    if (!empty(trim($s->email))) $has_email++;
}

echo "Total schools: " . count($schools) . "\n";
echo "Schools with website in DB: {$has_website}\n";
echo "Schools with email in DB: {$has_email}\n";
