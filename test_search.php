<?php
$q = "โรงเรียนวัดหาดทรายแก้ว เว็บไซต์";
$url = "https://html.duckduckgo.com/html/?q=" . urlencode($q);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore SSL verify errors
$html = curl_exec($ch);

if ($html === false) {
    echo "FAILED TO FETCH HTML\n";
    echo "Curl Error: " . curl_error($ch) . "\n";
} else {
    echo "SUCCESSFULLY FETCHED HTML (Length: " . strlen($html) . ")\n";
    preg_match_all('/href="([^"]+)"/', $html, $matches);
    print_r(array_slice($matches[1], 0, 25));
}
curl_close($ch);
