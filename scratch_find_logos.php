<?php
function listImages($dir) {
    if (!is_dir($dir)) return;
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            if ($file !== 'vendor' && $file !== 'node_modules' && $file !== '.git') {
                listImages($path);
            }
        } else {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'svg'])) {
                if (str_contains($path, 'school') || str_contains($path, 'logo')) {
                    echo "Image: {$path} (" . filesize($path) . " bytes)\n";
                }
            }
        }
    }
}

listImages(__DIR__);
