<?php
// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Custom websites override map for well-known schools
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

$schools = DB::table('network_schools')->get();
$updated = 0;

foreach ($schools as $school) {
    $cleanName = trim(str_replace(['โรงเรียน', "\r", "\n"], '', $school->name));
    
    // 1. Check if custom website exists
    $web = null;
    foreach ($customWebsites as $nameKey => $url) {
        $cleanKey = trim(str_replace('โรงเรียน', '', $nameKey));
        if ($cleanName === $cleanKey) {
            $web = $url;
            break;
        }
    }
    
    // 2. If no custom website, search in system_school to get the percode and build bopp-obec URL
    if (!$web) {
        $sysSchool = DB::table('system_school')
            ->where(function($query) use ($cleanName) {
                $query->where('schoolname', 'LIKE', '%' . $cleanName . '%')
                      ->orWhere('schoolname_eng', 'LIKE', '%' . $cleanName . '%');
            })
            ->first();
            
        if ($sysSchool && !empty($sysSchool->percode)) {
            $web = 'https://data.bopp-obec.info/web/home.php?School_ID=1086' . trim($sysSchool->percode);
        }
    }
    
    // 3. Update the website URL in database
    if ($web) {
        DB::table('network_schools')
            ->where('id', $school->id)
            ->update(['website' => $web]);
        $updated++;
        echo "Updated: {$school->name} -> {$web}\n";
    } else {
        echo "No URL found for: {$school->name}\n";
    }
}

echo "Successfully updated {$updated} school websites.\n";
