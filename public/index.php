<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Check if the application is installed
if (!file_exists(__DIR__.'/../.env')) {
    if (php_sapi_name() !== 'cli') {
        header('Location: /install.php');
        exit;
    }
}

// Register the Composer autoloader...
if (!file_exists(__DIR__.'/../vendor/autoload.php')) {
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        echo '<html><head><title>Installation Error</title><link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@400;700&display=swap" rel="stylesheet"><style>body{font-family:\'Anuphan\',sans-serif;background:#f8fafc;color:#334155;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center}.card{background:#fff;padding:2.5rem;border-radius:1.5rem;box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);max-width:500px;border:1px solid #fee2e2}h1{color:#e11d48;font-size:1.3rem;margin-bottom:1rem;font-weight:700}p{font-size:0.825rem;line-height:1.6;margin-bottom:1.5rem}code{background:#f1f5f9;padding:0.25rem 0.5rem;border-radius:0.375rem;font-family:monospace;font-size:0.75rem;color:#e11d48;font-weight:bold}</style></head><body><div class="card"><h1>เกิดข้อผิดพลาดในการโหลดระบบ (Autoload Missing)</h1><p>ไม่พบโฟลเดอร์ <code>vendor</code> ในระบบ กรุณาอัปโหลดโฟลเดอร์ <code>vendor</code> ขึ้นเซิร์ฟเวอร์ หรือรันคำสั่ง <code>composer install</code> ก่อนเข้าใช้งาน</p></div></body></html>';
        exit;
    }
}
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
