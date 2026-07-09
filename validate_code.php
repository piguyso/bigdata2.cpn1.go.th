<?php
/**
 * Local Code Validation Script
 * Checks all modified PHP files in git status for syntax errors,
 * and runs artisan route:list to ensure Laravel compiles without issues.
 */

echo "==========================================\n";
echo "   STARTING CODE STAGE VALIDATION CHECK   \n";
echo "==========================================\n";

$phpPath = 'C:\\php\\php-8.4.19-nts-Win32-vs17-x64\\php.exe';
if (!file_exists($phpPath)) {
    $phpPath = 'php'; // Fallback to global PATH
}

// 1. Get modified files in Git
exec("git status --porcelain", $output, $returnCode);

$phpFilesToCheck = [];

if ($returnCode === 0) {
    foreach ($output as $line) {
        $status = substr($line, 0, 2);
        $file = trim(substr($line, 2));
        // Only check PHP files that exist
        if (preg_match('/\.php$/', $file) && file_exists($file)) {
            $phpFilesToCheck[] = $file;
        }
    }
}

// Fallback to checking main application files if git status is empty or failed
if (empty($phpFilesToCheck)) {
    echo "No modified PHP files detected via git status. Scanning routes and recently modified files...\n";
    if (file_exists('routes/web.php')) $phpFilesToCheck[] = 'routes/web.php';
    if (file_exists('app/Http/Controllers/PlcController.php')) $phpFilesToCheck[] = 'app/Http/Controllers/PlcController.php';
}

$errors = 0;

// 2. Syntax check PHP files
echo "\n--- [1/2] Linting PHP Files (php -l) ---\n";
foreach ($phpFilesToCheck as $file) {
    $cmd = escapeshellarg($phpPath) . ' -l ' . escapeshellarg($file) . ' 2>&1';
    $outLines = [];
    exec($cmd, $outLines, $retCode);
    
    if ($retCode !== 0) {
        echo "❌ [ERROR] in $file:\n";
        echo "   " . implode("\n   ", $outLines) . "\n\n";
        $errors++;
    } else {
        echo "✅ [PASS] $file is syntactically correct.\n";
    }
}

// 3. Check Laravel compilation via artisan route:list
echo "\n--- [2/2] Checking Laravel Routing (artisan route:list) ---\n";
$artisanCmd = escapeshellarg($phpPath) . ' artisan route:list 2>&1';
$artisanOut = [];
exec($artisanCmd, $artisanOut, $artisanRet);

if ($artisanRet !== 0) {
    echo "❌ [ERROR] Laravel bootstrapping or route listing failed:\n";
    // Show last 15 lines of output for traceback
    $traceback = array_slice($artisanOut, -15);
    echo "   " . implode("\n   ", $traceback) . "\n\n";
    $errors++;
} else {
    echo "✅ [PASS] Laravel bootstrapped successfully and routes compile.\n";
}

echo "==========================================\n";
if ($errors > 0) {
    echo "❌ VALIDATION FAILED: $errors error(s) found.\n";
    echo "==========================================\n";
    exit(1);
} else {
    echo "🎉 VALIDATION PASSED: All code checks are clean!\n";
    echo "==========================================\n";
    exit(0);
}
