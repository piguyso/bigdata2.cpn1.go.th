<?php
$data = json_decode(file_get_contents('scratch_merged_schools.json'), true);
$missingWeb = [];
$missingEmail = [];
foreach ($data as $id => $s) {
    if (empty($s['website']) || str_contains($s['website'], 'bopp-obec.info')) {
        $missingWeb[] = $s;
    }
    if (empty($s['email'])) {
        $missingEmail[] = $s;
    }
}
echo "Total schools: " . count($data) . "\n";
echo "Schools missing website or having bopp-obec: " . count($missingWeb) . "\n";
echo "Schools missing email: " . count($missingEmail) . "\n";

echo "\nFirst 10 missing website:\n";
foreach (array_slice($missingWeb, 0, 10) as $s) {
    echo "- ID: {$s['id']}, Name: {$s['name']}, Province: {$s['province']}\n";
}

echo "\nFirst 10 missing email:\n";
foreach (array_slice($missingEmail, 0, 10) as $s) {
    echo "- ID: {$s['id']}, Name: {$s['name']}, Province: {$s['province']}\n";
}
