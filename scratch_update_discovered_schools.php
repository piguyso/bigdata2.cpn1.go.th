<?php
// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Discovered school websites mapping
$schoolWebsites = [
    2 => 'https://www.wathadsaikaew.ac.th',
    3 => 'http://www.bnhs.ac.th',
    7 => 'http://www.watpichaiyaram.ac.th',
    8 => 'http://www.bankohsonschool.ac.th',
    9 => 'http://www.banbangkoischool.ac.th',
    10 => 'http://www.banglong.ac.th',
    11 => 'http://www.banchongsai.ac.th',
    12 => 'http://www.banthunghong.ac.th',
    14 => 'http://www.waddonmamuang.ac.th',
    16 => 'http://www.wsbcpn.ac.th',
    18 => 'http://www.bansamseamschool.ac.th',
    20 => 'http://www.watnathungcpn1.ac.th',
    21 => 'http://www.bannongnean.ac.th',
    22 => 'http://www.watbangluek.ac.th',
    23 => 'http://www.bansalaloi.ac.th',
    24 => 'http://www.watkhukud.ac.th',
    26 => 'http://www.bankhotia.ac.th',
    28 => 'http://www.bankhaowong.ac.th',
    30 => 'http://www.wathuakrudschool.ac.th',
    32 => 'http://www.banhuathanon.ac.th',
    35 => 'http://www.watdonmuangschool.ac.th',
    36 => 'http://www.thairathvittaya66.ac.th',
    37 => 'http://www.watnomthawai.ac.th',
    38 => 'http://www.bannasae.ac.th',
    41 => 'http://www.banklongsoob.ac.th',
    42 => 'http://www.khaochunto.ac.th',
    43 => 'http://www.bankhaoyaoschool.ac.th',
    45 => 'http://www.thungmakham.ac.th',
    47 => 'http://www.banmaipattana.ac.th',
    49 => 'http://www.bantalantong.ac.th',
    50 => 'http://www.chumchonbankuring25.ac.th',
    52 => 'http://www.banhadhong.ac.th',
    54 => 'http://www.bnrschool.ac.th',
    57 => 'http://www.anubanthasae.ac.th',
    61 => 'http://www.bandonkiem.com',
    62 => 'http://www.banrubroschool.ac.th',
    63 => 'http://www.banhadnaischool.ac.th',
    68 => 'http://www.bansuansub.ac.th',
    69 => 'http://www.banpruthakieanschool.ac.th',
    70 => 'http://www.banmaisomboon.ac.th',
    72 => 'http://www.prachapat.ac.th',
    74 => 'http://www.bansaikhow.ac.th',
    75 => 'http://www.banthamjaroen.ac.th',
    76 => 'http://www.bantahong.ac.th',
    80 => 'http://www.banyaithaischool.com',
    82 => 'http://www.bannumyen.ac.th',
    83 => 'http://www.banjunthungcpn1.ac.th',
    84 => 'http://www.bansaikaew.com',
    86 => 'http://www.banchairachcpn1.ac.th',
    88 => 'http://www.banhinkob.ac.th',
    89 => 'http://www.banbangjark.ac.th',
    90 => 'http://www.banchumco.ac.th',
    91 => 'http://www.bandonsai.ac.th',
    92 => 'http://www.banboit.ac.th',
    93 => 'http://www.banthungria.ac.th',
    95 => 'http://www.watdonyang.ac.th',
    96 => 'http://www.banhuairakmai.ac.th',
    98 => 'http://www.bkms.site',
    101 => 'http://www.bannampu.com',
    102 => 'http://www.banthamthong.com',
    103 => 'http://www.banpakklong.ac.th',
    106 => 'http://www.noensumleeschool.com',
    107 => 'http://www.bansapalischool.ac.th',
    7202 => 'https://www.cpn1.go.th',
];

$updatedCount = 0;
$skippedCount = 0;

foreach ($schoolWebsites as $id => $websiteUrl) {
    // Check if the school exists in database
    $school = DB::table('network_schools')->where('id', $id)->first();
    if ($school) {
        $oldWebsite = $school->website;
        if ($oldWebsite !== $websiteUrl) {
            DB::table('network_schools')
                ->where('id', $id)
                ->update(['website' => $websiteUrl]);
            $updatedCount++;
            echo "Updated school ID {$id} ({$school->name}): '{$oldWebsite}' -> '{$websiteUrl}'\n";
        } else {
            $skippedCount++;
            echo "Skipped school ID {$id} ({$school->name}): Website is already '{$websiteUrl}'\n";
        }
    } else {
        echo "School with ID {$id} not found in database.\n";
    }
}

echo "\n--- Summary ---\n";
echo "Total updated: {$updatedCount}\n";
echo "Total skipped (already up-to-date): {$skippedCount}\n";
