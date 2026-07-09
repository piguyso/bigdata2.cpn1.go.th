<?php
$q = "โรงเรียนวัดหาดทรายแก้ว";
$url = "https://www.ask.com/web?q=" . urlencode($q);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$html = curl_exec($ch);
curl_close($ch);

if ($html) {
    echo "SUCCESS: " . strlen($html) . " bytes\n";
    file_put_contents('ask_test.html', $html);
    if (str_contains($html, 'wathadsaikaew') || str_contains($html, 'facebook.com')) {
        echo "FOUND EXPECTED KEYWORDS!\n";
    } else {
        echo "KEYWORDS NOT FOUND!\n";
    }
    
    // Parse using DOMDocument
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    $xpath = new DOMXPath($dom);
    
    // Ask.com results are typically inside elements with class "PartialSearchResults-item-title-link"
    $links = $xpath->query("//a[contains(@class, 'PartialSearchResults-item-title-link')]");
    echo "Found " . $links->length . " search result links:\n";
    foreach ($links as $link) {
        echo "- " . $link->getAttribute('href') . " -> " . trim($link->textContent) . "\n";
    }
} else {
    echo "FAILED TO FETCH\n";
}
