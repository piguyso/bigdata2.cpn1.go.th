<?php
$q = "โรงเรียนวัดหาดทรายแก้ว";
$url = "https://lite.duckduckgo.com/lite/";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['q' => $q]));
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$html = curl_exec($ch);
curl_close($ch);

if ($html) {
    echo "SUCCESS: " . strlen($html) . " bytes\n";
    file_put_contents('ddg_lite.html', $html);
    if (str_contains($html, 'หาดทรายแก้ว')) {
        echo "FOUND MATCH!\n";
    } else {
        echo "NOT FOUND MATCH!\n";
    }
} else {
    echo "FAILED TO FETCH\n";
}
