<?php
$html = file_get_contents('ddg_test.html');
$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
$xpath = new DOMXPath($dom);
$links = $dom->getElementsByTagName('a');
echo "Found " . $links->length . " total links:\n";
foreach ($links as $index => $link) {
    echo "- href: " . $link->getAttribute('href') . " | Text: " . trim($link->textContent) . "\n";
}
