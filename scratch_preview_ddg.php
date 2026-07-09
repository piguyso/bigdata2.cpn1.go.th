<?php
$html = file_get_contents('ddg_test.html');
echo "HTML Length: " . strlen($html) . "\n";
if (preg_match('/<title>(.*?)<\/title>/si', $html, $matches)) {
    echo "Title: " . $matches[1] . "\n";
}
// Show first 1000 characters
echo "Preview:\n" . substr($html, 0, 1000) . "\n";
