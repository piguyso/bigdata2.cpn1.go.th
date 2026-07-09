<?php
$file = 'database/old_db_full_dump.sql';
if (!file_exists($file)) {
    die("File not found");
}

$handle = fopen($file, 'r');
$lines = [];
while (($line = fgets($handle)) !== false) {
    if (str_contains($line, 'CREATE TABLE')) {
        echo $line;
    }
}
fclose($handle);
