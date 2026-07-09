<?php
$html = file_get_contents('bing_test.html');
$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
$xpath = new DOMXPath($dom);

$links = $dom->getElementsByTagName('a');
echo "Total links in Bing test: " . $links->length . "\n";
$count = 0;
foreach ($links as $link) {
    $href = $link->getAttribute('href');
    $text = trim($link->textContent);
    if (str_starts_with($href, 'http') && !str_contains($href, 'microsoft.com') && !str_contains($href, 'bing.com')) {
        echo "- href: {$href} | text: {$text}\n";
        $count++;
        if ($count > 20) break;
    }
}
