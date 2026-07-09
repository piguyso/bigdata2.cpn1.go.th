<?php
$q = "โรงเรียนวัดหาดทรายแก้ว";
$url = "https://html.duckduckgo.com/html/?q=" . urlencode($q);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$html = curl_exec($ch);
curl_close($ch);

if ($html) {
    file_put_contents('ddg_test.html', $html);
    // Parse using DOMDocument
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    $xpath = new DOMXPath($dom);
    
    // DuckDuckGo results are inside <a class="result__url"> or similar
    $links = $xpath->query("//a[contains(@class, 'result__url')]");
    echo "Found " . $links->length . " result__url links:\n";
    foreach ($links as $link) {
        echo "Link: " . $link->getAttribute('href') . " -> " . trim($link->textContent) . "\n";
    }

    $snippets = $xpath->query("//td[@class='result__snippet']");
    echo "\nFound " . $snippets->length . " snippets:\n";
    foreach ($snippets as $index => $snippet) {
        if ($index < 5) {
            echo "Snippet {$index}: " . trim($snippet->textContent) . "\n";
        }
    }
} else {
    echo "Failed to fetch\n";
}
