<?php
$html = file_get_contents('ddg_test.html');
$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
$xpath = new DOMXPath($dom);

$forms = $dom->getElementsByTagName('form');
echo "Forms count: " . $forms->length . "\n";
foreach ($forms as $form) {
    echo "Form Action: " . $form->getAttribute('action') . " | Method: " . $form->getAttribute('method') . "\n";
    $inputs = $form->getElementsByTagName('input');
    foreach ($inputs as $input) {
        echo "  Input Name: " . $input->getAttribute('name') . " | Type: " . $input->getAttribute('type') . " | Value: " . $input->getAttribute('value') . "\n";
    }
}
