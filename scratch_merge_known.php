<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$sysSchools = DB::table('system_school')->get();
$netSchools = DB::table('network_schools')->get();

$discoveredFile = 'discovered_school_websites.json';
$discovered = [];
if (file_exists($discoveredFile)) {
    $discovered = json_decode(file_get_contents($discoveredFile), true);
}

// Custom websites from scratch_update_schools.php
$customWebsites = [
    'อนุบาลชุมพร' => 'https://www.abcp.ac.th',
    'วัดขุนกระทิง' => 'http://www.watkhunkrating.ac.th',
    'ชุมชนบ้านถ้ำสิงห์' => 'http://www.banthamschool.ac.th',
    'เมืองชุมพรบ้านเขาถล่ม' => 'http://www.muangchumphonschool.ac.th',
    'ชุมชนวัดหาดพันไกร' => 'http://www.hadphankrai.ac.th',
    'วัดหูรอ' => 'http://www.whr.ac.th',
    'บ้านดอนไทรงาม' => 'http://www.bandonsaingam.ac.th',
    'บ้านท่ามะปริง' => 'http://www.banthamapring.com',
    'ประชานิคม 2' => 'http://www.prachanikom2.siamvip.com',
    'บ้านแก่งเพกา' => 'http://www.Kaengphekathaifasthost.com',
    'บ้านหาดส้มแป้น' => 'http://www.banhadsompan.com',
    'ชุมชนประชานิคม' => 'http://www.prachanikhom.ac.th',
    'บ้านคันธทรัพย์' => 'http://www.khansup.ac.th',
    'บ้านร้านตัดผม' => 'http://www.banrantadphom.ac.th',
    'ประชานิคม 4' => 'http://www.prachanikhom4.ac.th',
    'ไทยรัฐวิทยา ๗๖ (บ้านพละ)' => 'http://www.thairatwitthaya76.com',
    'ไทยรัฐวิทยา 76 (บ้านพละ)' => 'http://www.thairatwitthaya76.com',
    'บ้านวังช้าง' => 'http://www.banwangchang.ac.th',
    'ชุมชนมาบอำมฤต' => 'http://www.mapammarit.com',
    'อนุบาลปะทิว(บางสนพิพิธราษฏร์บำรุง)' => 'http://anubanpathiu.ac.th/',
    'วัดบางแหวน' => 'http://watbangwaen.ac.th',
    'ไทยรัฐวิทยา๗๘(วัดสามัคคีชัย)' => 'http://thairathwittaya78.ac.th',
    'ไทยรัฐวิทยา 78 (วัดสามัคคีชัย)' => 'http://thairathwittaya78.ac.th',
];

$merged = [];
foreach ($sysSchools as $s) {
    $id = $s->id;
    $name = trim(preg_replace('/\s+/', ' ', $s->schoolname));
    $cleanName = trim(str_replace(['โรงเรียน', "\r", "\n"], '', $name));

    // Start with whatever is in system_school table
    $website = trim($s->website ?? '');
    $email = trim($s->email ?? '');

    // 1. Check network_schools
    $netS = $netSchools->firstWhere('id', $id);
    if (!$netS) {
        // Try finding by name match
        $netS = $netSchools->first(function($item) use ($cleanName) {
            $n = trim(str_replace('โรงเรียน', '', $item->name));
            return $n === $cleanName;
        });
    }

    if ($netS) {
        if (empty($website) && !empty(trim($netS->website))) {
            $website = trim($netS->website);
        }
    }

    // 2. Check custom websites
    foreach ($customWebsites as $nameKey => $url) {
        $cleanKey = trim(str_replace('โรงเรียน', '', $nameKey));
        if ($cleanName === $cleanKey) {
            $website = $url;
            break;
        }
    }

    // 3. Check discovered json
    if (isset($discovered[$id]) && !empty(trim($discovered[$id]['website']))) {
        $website = trim($discovered[$id]['website']);
    } else {
        // Find in discovered by name
        foreach ($discovered as $discId => $discInfo) {
            $discClean = trim(str_replace('โรงเรียน', '', $discInfo['name']));
            if ($cleanName === $discClean && !empty(trim($discInfo['website']))) {
                $website = trim($discInfo['website']);
                break;
            }
        }
    }

    // Clean websites that look like emails (we saw website = "donroub@wdr.ac.th" and email = "www.wdr.ac.th" swapped in database)
    if (str_contains($website, '@') && !str_contains($website, '/')) {
        $temp = $website;
        $website = $email;
        $email = $temp;
    }

    // Normalize email & website
    if (!empty($website)) {
        if (!str_starts_with($website, 'http://') && !str_starts_with($website, 'https://') && !str_contains($website, 'bopp-obec.info')) {
            $website = 'http://' . $website;
        }
    }

    $merged[$id] = [
        'id' => $id,
        'smis' => $s->smis ?? '',
        'percode' => $s->percode ?? '',
        'name' => $name,
        'clean_name' => $cleanName,
        'website' => $website,
        'email' => $email,
        'logo' => ''
    ];
}

file_put_contents('scratch_merged_schools.json', json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
$hasWeb = count(array_filter($merged, fn($x) => !empty($x['website'])));
$hasEmail = count(array_filter($merged, fn($x) => !empty($x['email'])));
echo "Merged schools count: " . count($merged) . "\n";
echo "Schools with website after merge: {$hasWeb}\n";
echo "Schools with email after merge: {$hasEmail}\n";
