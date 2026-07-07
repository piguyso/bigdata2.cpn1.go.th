<?php
header('Content-Type: text/plain; charset=utf-8');

$opcache_status = 'Not Enabled';
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        $opcache_status = 'OPcache cleared successfully!';
    } else {
        $opcache_status = 'Failed to clear OPcache (it might be disabled or busy).';
    }
} else {
    $opcache_status = 'OPcache is not enabled or function opcache_reset() does not exist in this PHP environment.';
}

echo "--- Cache Clearance Status ---\n";
echo "OPcache: " . $opcache_status . "\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
