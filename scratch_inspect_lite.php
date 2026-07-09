<?php
$html = file_get_contents('ddg_lite.html');
$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
$xpath = new DOMXPath($dom);

$tables = $dom->getElementsByTagName('table');
echo "Tables count: " . $tables->length . "\n";
foreach ($tables as $index => $table) {
    echo "Table {$index} class: " . $table->getAttribute('class') . "\n";
}

$rows = $dom->getElementsByTagName('tr');
echo "TR count: " . $rows->length . "\n";

$links = $dom->getElementsByTagName('a');
echo "A count: " . $links->length . "\n";
foreach ($links as $index => $link) {
    if ($index < 20) {
        echo "Link {$index}: href=" . $link->getAttribute('href') . " | class=" . $link->getAttribute('class') . " | text=" . trim($link->textContent) . "\n";
    }
}
