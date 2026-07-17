<?php

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $tables = DB::select('SHOW TABLES');
    $dbName = DB::getDatabaseName();
    $property = 'Tables_in_' . $dbName;

    $excludedTables = ['users', 'settings', 'migrations'];

    echo "Database: {$dbName}\n";

    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    foreach ($tables as $tableInfo) {
        if (!isset($tableInfo->$property)) {
            // Handle case-insensitivity or custom key formats
            $vars = get_object_vars($tableInfo);
            $tableName = reset($vars);
        } else {
            $tableName = $tableInfo->$property;
        }

        if (in_array($tableName, $excludedTables, true)) {
            echo "Skipping excluded table: {$tableName}\n";
            continue;
        }
        
        echo "Truncating table: {$tableName}...\n";
        DB::table($tableName)->truncate();
    }

    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    echo "Successfully cleared all data except users, settings, and migrations.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
