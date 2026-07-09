<?php
$q = "โรงเรียนวัดหาดทรายแก้ว";
$url = "https://search.yahoo.com/search?p=" . urlencode($q);

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
    file_put_contents('yahoo_test.html', $html);
    if (str_contains($html, 'wathadsaikaew') || str_contains($html, 'facebook.com')) {
        echo "FOUND EXPECTED KEYWORDS!\n";
    } else {
        echo "KEYWORDS NOT FOUND!\n";
    }
    
    // Parse using DOMDocument
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    $xpath = new DOMXPath($dom);
    
    // Yahoo search results are typically inside elements with class "d-flex" or "lh-20" or standard links inside result divs.
    // Let's dump all external URLs
    $links = $dom->getElementsByTagName('a');
    echo "Found " . $links->length . " total links:\n";
    $count = 0;
    foreach ($links as $link) {
        $href = $link->getAttribute('href');
        $text = trim($link->textContent);
        if (str_contains($href, 'RU=') || str_contains($href, 'wathadsaikaew') || str_contains($href, 'facebook.com')) {
            echo "- href: {$href} | text: {$text}\n";
            $count++;
            if ($count > 10) break;
        }
    }
} else {
    echo "FAILED TO FETCH\n";
}
