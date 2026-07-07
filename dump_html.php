<?php
$q = "โรงเรียนวัดหาดทรายแก้ว เว็บไซต์";
$url = "https://html.duckduckgo.com/html/?q=" . urlencode($q);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$html = curl_exec($ch);
curl_close($ch);

file_put_contents("C:\\inetpub\\wwwroot\\ee.cpn1.go.th\\ddg_response.html", $html);
echo "Wrote " . strlen($html) . " bytes to ddg_response.html\n";
