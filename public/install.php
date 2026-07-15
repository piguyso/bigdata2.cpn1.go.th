<?php
/**
 * Laravel Web Application Installer
 * Securely sets up the .env file, database, migrations, seeders, and storage link.
 * Design matched to the application's premium UX/UI.
 */

define('INSTALLER_LOCK_FILE', __DIR__ . '/../storage/installed.lock');
define('ENV_FILE', __DIR__ . '/../.env');
define('ENV_EXAMPLE_FILE', __DIR__ . '/../.env.example');

// 1. Security Check: Block if already installed
if (file_exists(ENV_FILE) || file_exists(INSTALLER_LOCK_FILE)) {
    http_response_code(403);
    echo '<!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>ระบบได้รับการติดตั้งแล้ว</title>
        <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@400;700&display=swap" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        <style>body { font-family: "Anuphan", sans-serif; }</style>
    </head>
    <body class="bg-slate-50 text-slate-650 min-h-screen flex items-center justify-center p-6 relative overflow-hidden">
        <div class="absolute top-20 left-10 w-64 h-64 bg-orange-200 rounded-full blur-[100px] opacity-30 animate-pulse"></div>
        <div class="absolute bottom-10 right-10 w-80 h-80 bg-sky-200 rounded-full blur-[120px] opacity-30"></div>
        
        <div class="max-w-md w-full bg-white/70 backdrop-blur-2xl border border-white rounded-[3rem] p-10 text-center shadow-2xl shadow-orange-100/50 relative z-10">
            <div class="w-16 h-16 bg-orange-500/10 border border-orange-500/20 text-orange-500 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
            </div>
            <h2 class="text-slate-800 text-xl font-bold mb-3">ระบบได้รับการติดตั้งเรียบร้อยแล้ว</h2>
            <p class="text-sm text-slate-400 leading-relaxed mb-6">หากคุณต้องการทำการติดตั้งใหม่อีกครั้ง กรุณาลบไฟล์ <code>.env</code> หรือไฟล์ล็อกการติดตั้งที่ <code>storage/installed.lock</code> ออกก่อน</p>
            <a href="/" class="inline-block bg-slate-900 hover:bg-orange-600 text-white font-bold text-sm px-8 py-4 rounded-2xl transition duration-200 shadow-xl shadow-slate-200 active:scale-95 w-full">เข้าสู่หน้าแรกของระบบ</a>
        </div>
    </body>
    </html>';
    exit;
}

// 2. Requirements & Permissions check logic helper
function checkServerRequirements() {
    $requirements = [
        'php' => [
            'name' => 'PHP >= 8.3',
            'passed' => version_compare(PHP_VERSION, '8.3.0', '>='),
            'current' => PHP_VERSION
        ],
        'pdo' => [
            'name' => 'PDO PHP Extension',
            'passed' => extension_loaded('pdo'),
        ],
        'openssl' => [
            'name' => 'OpenSSL PHP Extension',
            'passed' => extension_loaded('openssl'),
        ],
        'mbstring' => [
            'name' => 'Mbstring PHP Extension',
            'passed' => extension_loaded('mbstring'),
        ],
        'tokenizer' => [
            'name' => 'Tokenizer PHP Extension',
            'passed' => extension_loaded('tokenizer'),
        ],
        'xml' => [
            'name' => 'XML PHP Extension',
            'passed' => extension_loaded('xml'),
        ],
        'ctype' => [
            'name' => 'Ctype PHP Extension',
            'passed' => extension_loaded('ctype'),
        ],
        'json' => [
            'name' => 'JSON PHP Extension',
            'passed' => extension_loaded('json'),
        ],
        'fileinfo' => [
            'name' => 'FileInfo PHP Extension',
            'passed' => extension_loaded('fileinfo'),
        ],
        'zip' => [
            'name' => 'Zip PHP Extension (for XLSX files)',
            'passed' => extension_loaded('zip'),
            'warning' => 'คำเตือน: เซิร์ฟเวอร์นี้ไม่มี Zip extension จะทำให้ไม่สามารถนำเข้า/ส่งออกไฟล์ Excel (.xlsx) ได้ แต่ระบบจะยังสามารถใช้งานและนำเข้าข้อมูลผ่านไฟล์ .csv ได้ตามปกติ'
        ],
        'autoload' => [
            'name' => 'Laravel Vendor Autoloader',
            'passed' => file_exists(__DIR__ . '/../vendor/autoload.php'),
            'warning' => 'กรุณาตรวจสอบว่าได้ทำการรัน composer install หรืออัปโหลดโฟลเดอร์ vendor แล้ว'
        ]
    ];

    // Directories to check write permissions
    $writePaths = [
        'env' => [
            'name' => 'Root Directory (for .env)',
            'path' => __DIR__ . '/../',
            'passed' => is_writable(file_exists(ENV_FILE) ? ENV_FILE : __DIR__ . '/../')
        ],
        'storage' => [
            'name' => 'Storage Directory',
            'path' => __DIR__ . '/../storage',
            'passed' => is_writable(__DIR__ . '/../storage')
        ],
        'bootstrap_cache' => [
            'name' => 'Bootstrap Cache Directory',
            'path' => __DIR__ . '/../bootstrap/cache',
            'passed' => is_writable(__DIR__ . '/../bootstrap/cache')
        ]
    ];

    $allPassed = true;
    foreach ($requirements as $key => $req) {
        if ($key === 'zip') {
            continue; // Zip is optional, does not block installation
        }
        if (!$req['passed']) $allPassed = false;
    }
    foreach ($writePaths as $path) {
        if (!$path['passed']) $allPassed = false;
    }

    return [
        'passed' => $allPassed,
        'requirements' => $requirements,
        'writePaths' => $writePaths
    ];
}

// 3. AJAX Actions handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');

    $action = $_GET['action'];

    if ($action === 'check_requirements') {
        echo json_encode(checkServerRequirements());
        exit;
    }

    if ($action === 'run_composer') {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $disabled_functions = explode(',', ini_get('disable_functions'));
        $disabled_functions = array_map('trim', $disabled_functions);
        
        $has_exec = function_exists('exec') && !in_array('exec', $disabled_functions);
        $has_shell = function_exists('shell_exec') && !in_array('shell_exec', $disabled_functions);
        
        if (!$has_exec && !$has_shell) {
            echo json_encode([
                'status' => 'error',
                'message' => 'ฟังก์ชันการรันคำสั่ง (exec/shell_exec) ถูกปิดใช้งานในเซิร์ฟเวอร์นี้ กรุณาอัปโหลดโฟลเดอร์ vendor ขึ้นมาด้วยตนเอง'
            ]);
            exit;
        }

        $root_dir = realpath(__DIR__ . '/../');
        $composer_phar = $root_dir . '/composer.phar';
        
        try {
            $command = 'composer --version 2>&1';
            $output = [];
            $retval = -1;
            
            if ($has_exec) {
                @exec($command, $output, $retval);
            }
            
            $use_global = ($retval === 0);
            
            if (!$use_global) {
                if (!file_exists($composer_phar)) {
                    $url = 'https://getcomposer.org/composer-stable.phar';
                    $composer_data = @file_get_contents($url);
                    if ($composer_data === false) {
                        throw new Exception('ไม่สามารถดาวน์โหลด composer.phar ได้ กรุณาเชื่อมต่ออินเทอร์เน็ตหรืออัปโหลดโฟลเดอร์ vendor ด้วยตนเอง');
                    }
                    file_put_contents($composer_phar, $composer_data);
                }
                
                if (function_exists('chmod')) {
                    @chmod($composer_phar, 0755);
                }
                
                $php_binary = PHP_BINARY ? PHP_BINARY : 'php';
                $run_cmd = "$php_binary $composer_phar install --no-dev --optimize-autoloader --no-interaction 2>&1";
            } else {
                $run_cmd = "composer install --no-dev --optimize-autoloader --no-interaction 2>&1";
            }
            
            $cmd_output = [];
            $cmd_retval = -1;
            
            if ($has_exec) {
                @exec("cd " . escapeshellarg($root_dir) . " && $run_cmd", $cmd_output, $cmd_retval);
            } else {
                $output_str = @shell_exec("cd " . escapeshellarg($root_dir) . " && $run_cmd");
                $cmd_output = explode("\n", $output_str);
            }
            
            if (file_exists($composer_phar)) {
                @unlink($composer_phar);
            }
            
            if (file_exists($root_dir . '/vendor/autoload.php')) {
                echo json_encode(['status' => 'success', 'message' => 'ดาวน์โหลด Components สำเร็จ!']);
            } else {
                $output_log = implode("\n", $cmd_output);
                throw new Exception("รันคำสั่งดาวน์โหลดล้มเหลว (มักเกิดจาก php memory_limit ต่ำเกินไป หรือเซิร์ฟเวอร์ถูกจำกัดสิทธิ์):\n" . $output_log);
            }
            
        } catch (Exception $e) {
            if (file_exists($composer_phar)) {
                @unlink($composer_phar);
            }
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'test_db') {
        $host = $_POST['host'] ?? '127.0.0.1';
        $port = $_POST['port'] ?? '3306';
        $database = $_POST['database'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        try {
            $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 4,
            ]);
            
            // Try to create database if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            echo json_encode(['status' => 'success', 'message' => 'เชื่อมต่อฐานข้อมูลเรียบร้อย']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'execute') {
        $host = $_POST['host'] ?? '127.0.0.1';
        $port = $_POST['port'] ?? '3306';
        $database = $_POST['database'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        try {
            // Check autoload again
            if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
                throw new Exception('ไม่พบโฟลเดอร์ vendor/ กรุณาติดตั้ง composer dependencies ก่อน');
            }

            // 1. Create .env file from .env.example
            $envExample = file_get_contents(ENV_EXAMPLE_FILE);
            if ($envExample === false) {
                throw new Exception('ไม่พบไฟล์ .env.example ในโปรเจกต์');
            }

            // Replace environment variables
            $envReplacements = [
                'DB_CONNECTION=sqlite' => 'DB_CONNECTION=mysql',
                '# DB_HOST=127.0.0.1' => "DB_HOST=$host",
                '# DB_PORT=3306' => "DB_PORT=$port",
                '# DB_DATABASE=laravel' => "DB_DATABASE=$database",
                '# DB_USERNAME=root' => "DB_USERNAME=$username",
                '# DB_PASSWORD=' => "DB_PASSWORD=$password",
                'DB_HOST=127.0.0.1' => "DB_HOST=$host",
                'DB_PORT=3306' => "DB_PORT=$port",
                'DB_DATABASE=laravel' => "DB_DATABASE=$database",
                'DB_USERNAME=root' => "DB_USERNAME=$username",
                'DB_PASSWORD=' => "DB_PASSWORD=$password",
            ];

            $newEnvContent = strtr($envExample, $envReplacements);
            
            // Set App URL based on current host
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $currentUrl = $protocol . "://" . $_SERVER['HTTP_HOST'];
            $newEnvContent = preg_replace('/APP_URL=http:\/\/localhost/', 'APP_URL=' . $currentUrl, $newEnvContent);

            if (!file_put_contents(ENV_FILE, $newEnvContent)) {
                throw new Exception('ไม่สามารถเขียนไฟล์ .env ได้ กรุณาตรวจสอบสิทธิ์โฟลเดอร์');
            }

            // 2. Boot Laravel and execute Artisan commands programmatically
            define('LARAVEL_START', microtime(true));
            require __DIR__ . '/../vendor/autoload.php';
            
            $app = require_once __DIR__ . '/../bootstrap/app.php';
            $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

            // Override configuration to ensure it uses the posted credentials directly
            config([
                'database.connections.mysql.host' => $host,
                'database.connections.mysql.port' => $port,
                'database.connections.mysql.database' => $database,
                'database.connections.mysql.username' => $username,
                'database.connections.mysql.password' => $password,
            ]);

            // Generate Key
            $kernel->call('key:generate', ['--force' => true]);

            // Run Migrations and Seeding
            $kernel->call('migrate:fresh', ['--force' => true, '--seed' => true]);

            // Run Storage Link (wrapped in try/catch to avoid crash if symlinks are restricted)
            try {
                $kernel->call('storage:link', ['--force' => true]);
            } catch (Exception $e) {
                // Log or ignore warning
            }

            // Write lock file
            file_put_contents(INSTALLER_LOCK_FILE, json_encode([
                'installed_at' => date('Y-m-d H:i:s'),
                'ip' => $_SERVER['REMOTE_ADDR']
            ], JSON_PRETTY_PRINT));

            echo json_encode(['status' => 'success', 'message' => 'ติดตั้งระบบเสร็จสมบูรณ์!']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
}

$state = checkServerRequirements();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดตั้งระบบ | Setup Wizard</title>
    <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700;850&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; background-color: #f8fafc; color: #475569; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between py-12 px-6 relative overflow-hidden">
    <!-- Blurred circles -->
    <div class="absolute top-20 left-10 w-64 h-64 bg-orange-200 rounded-full blur-[100px] opacity-35 animate-pulse"></div>
    <div class="absolute bottom-10 right-10 w-80 h-80 bg-sky-200 rounded-full blur-[120px] opacity-35"></div>

    <div class="max-w-2xl w-full mx-auto bg-white/70 backdrop-blur-2xl border border-white rounded-[3.5rem] shadow-2xl shadow-orange-100/50 overflow-hidden flex flex-col relative z-10" x-data="installerWizard(@json($state))">
        <!-- Progress Bar Header -->
        <div class="bg-white/50 border-b border-slate-100 px-8 py-6 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" /></svg>
                </div>
                <div>
                    <h1 class="text-slate-800 text-base font-extrabold tracking-tight">ติดตั้งระบบบริหารจัดการข้อมูล</h1>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Setup Wizard</p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 text-xs font-extrabold text-slate-400">
                <span :class="step >= 1 ? 'text-orange-500' : ''">1</span>
                <span>/</span>
                <span :class="step >= 2 ? 'text-orange-500' : ''">2</span>
                <span>/</span>
                <span :class="step >= 3 ? 'text-orange-500' : ''">3</span>
                <span>/</span>
                <span :class="step >= 4 ? 'text-orange-500' : ''">4</span>
            </div>
        </div>

        <div class="p-8 md:p-12 flex-1">
            <!-- STEP 1: Requirements Check -->
            <div x-show="step === 1" class="space-y-6" x-cloak>
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-slate-800 text-lg font-extrabold">ขั้นตอนที่ 1: ตรวจสอบความพร้อมของเซิร์ฟเวอร์</h2>
                    <p class="text-xs text-slate-400 mt-1">โปรแกรมจะตรวจสอบเวอร์ชัน PHP ส่วนขยายที่สำคัญ และสิทธิ์การเขียนโฟลเดอร์</p>
                </div>

                <div class="space-y-3">
                    <h3 class="text-xs font-extrabold uppercase tracking-widest text-slate-400 ml-1">การติดตั้ง & PHP Extensions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <template x-for="(req, key) in state.requirements" :key="key">
                            <div class="flex items-center justify-between p-4 bg-white border border-slate-100 rounded-2xl shadow-sm">
                                <span class="text-xs font-bold text-slate-700" x-text="req.name"></span>
                                <div class="flex items-center gap-2">
                                    <span x-show="req.current" class="text-[10px] font-bold text-slate-400" x-text="req.current"></span>
                                    <svg x-show="req.passed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                    
                                    <template x-if="!req.passed">
                                        <div class="flex items-center">
                                            <!-- Orange warning triangle for zip, red cross for others -->
                                            <svg x-show="key === 'zip'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                            <svg x-show="key !== 'zip'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="space-y-3">
                    <h3 class="text-xs font-extrabold uppercase tracking-widest text-slate-400 ml-1">สิทธิ์การเขียนโฟลเดอร์ (Write Permissions)</h3>
                    <div class="space-y-2.5">
                        <template x-for="(path, key) in state.writePaths" :key="key">
                            <div class="flex items-center justify-between p-4 bg-white border border-slate-100 rounded-2xl shadow-sm">
                                <div>
                                    <span class="text-xs font-bold text-slate-700" x-text="path.name"></span>
                                    <p class="text-[10px] text-slate-400 mt-0.5 truncate max-w-sm" x-text="path.path"></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-extrabold uppercase px-2.5 py-1.5 rounded-lg" :class="path.passed ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100'" x-text="path.passed ? 'Writable' : 'Unwritable'"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Composer Install Helper Container -->
                <div x-show="state.requirements && state.requirements.autoload && !state.requirements.autoload.passed" class="mt-4 p-5 bg-orange-50 border border-orange-100 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="space-y-1 text-center md:text-left">
                        <span class="text-xs font-bold text-orange-850">ตรวจพบว่าไม่มีโฟลเดอร์ vendor</span>
                        <p class="text-[10px] text-orange-600">หากเซิร์ฟเวอร์ของคุณเชื่อมต่ออินเทอร์เน็ต สามารถลองสั่งดาวน์โหลดและติดตั้งคอมโพเนนต์อัตโนมัติได้ที่นี่</p>
                    </div>
                    <button type="button" 
                            @click="runComposerInstall()"
                            :disabled="composerRunning"
                            class="bg-slate-900 hover:bg-orange-600 text-white font-bold text-xs px-5 py-3 rounded-xl transition duration-200 shrink-0 flex items-center gap-2">
                        <svg x-show="composerRunning" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="composerRunning ? 'กำลังติดตั้ง...' : 'ดาวน์โหลดคอมโพเนนต์'"></span>
                    </button>
                </div>

                <!-- Zip extension soft warning notification -->
                <div x-show="state.requirements && state.requirements.zip && !state.requirements.zip.passed" class="mt-4 p-4 bg-amber-50 border border-amber-150 rounded-2xl flex items-start gap-3">
                    <svg class="h-5 w-5 text-amber-600 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                    <div>
                        <span class="text-xs font-bold text-amber-800">คำเตือน: ขาด Zip Extension (ไม่กระทบการทำงานหลัก)</span>
                        <p class="text-[10px] text-amber-600 leading-relaxed mt-0.5" x-text="state.requirements.zip.warning"></p>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex justify-end">
                    <button type="button" 
                            @click="nextStep()" 
                            :disabled="!state.passed"
                            class="bg-slate-900 text-white font-bold text-xs px-8 py-3.5 rounded-2xl hover:bg-orange-655 transition shadow-lg shadow-slate-200 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed">
                        ขั้นตอนถัดไป →
                    </button>
                </div>
            </div>

            <!-- STEP 2: Database Configuration -->
            <div x-show="step === 2" class="space-y-6" x-cloak>
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-slate-800 text-lg font-extrabold">ขั้นตอนที่ 2: ตั้งค่าการเชื่อมต่อฐานข้อมูล (Database)</h2>
                    <p class="text-xs text-slate-400 mt-1">ป้อนค่าการเชื่อมต่อฐานข้อมูล MariaDB หรือ MySQL ของคุณ (จะสร้างฐานข้อมูลให้อัตโนมัติหากไม่มีอยู่ในระบบ)</p>
                </div>

                <form @submit.prevent="testDatabaseConnection()" class="space-y-5">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2 space-y-1.5">
                            <label class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 ml-1">Database Host</label>
                            <input type="text" x-model="form.host" required class="w-full px-5 py-3.5 bg-white border border-slate-150 rounded-2xl focus:ring-4 focus:ring-orange-100 focus:border-orange-400 outline-none transition text-xs text-slate-800">
                        </div>
                        <div class="col-span-1 space-y-1.5">
                            <label class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 ml-1">Port</label>
                            <input type="text" x-model="form.port" required class="w-full px-5 py-3.5 bg-white border border-slate-150 rounded-2xl focus:ring-4 focus:ring-orange-100 focus:border-orange-400 outline-none transition text-xs text-slate-800">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 ml-1">ชื่อฐานข้อมูล (Database Name)</label>
                        <input type="text" x-model="form.database" required class="w-full px-5 py-3.5 bg-white border border-slate-150 rounded-2xl focus:ring-4 focus:ring-orange-100 focus:border-orange-400 outline-none transition text-xs text-slate-800" placeholder="เช่น bigdata">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 ml-1">ผู้ใช้ (DB Username)</label>
                            <input type="text" x-model="form.username" required class="w-full px-5 py-3.5 bg-white border border-slate-150 rounded-2xl focus:ring-4 focus:ring-orange-100 focus:border-orange-400 outline-none transition text-xs text-slate-800">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 ml-1">รหัสผ่าน (DB Password)</label>
                            <input type="password" x-model="form.password" class="w-full px-5 py-3.5 bg-white border border-slate-150 rounded-2xl focus:ring-4 focus:ring-orange-100 focus:border-orange-400 outline-none transition text-xs text-slate-800">
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex justify-between gap-3">
                        <button type="button" @click="step = 1" class="border border-slate-200 hover:bg-slate-100 text-slate-650 font-bold text-xs px-6 py-3.5 rounded-2xl transition duration-200">
                            ย้อนกลับ
                        </button>
                        <button type="submit" 
                                :disabled="dbTesting"
                                class="bg-slate-900 text-white font-bold text-xs px-8 py-3.5 rounded-2xl hover:bg-orange-600 transition shadow-lg shadow-slate-250 active:scale-95 disabled:opacity-40 flex items-center gap-2">
                            <svg x-show="dbTesting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-text="dbTesting ? 'กำลังทดสอบเชื่อมต่อ...' : 'ทดสอบและติดตั้งต่อ →'"></span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- STEP 3: Executing Setup -->
            <div x-show="step === 3" class="space-y-6 text-center py-8" x-cloak>
                <div class="w-20 h-20 bg-orange-150/10 border border-orange-500/20 text-orange-500 rounded-full flex items-center justify-center text-3xl mx-auto animate-pulse">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 animate-spin text-orange-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
                <div class="space-y-2">
                    <h2 class="text-slate-800 text-lg font-bold">กำลังดำเนินการติดตั้งระบบ</h2>
                    <p class="text-xs text-slate-400">ตัวช่วยกำลังเขียนไฟล์ตั้งค่าคอนฟิก <code>.env</code> สุ่มกุญแจระบบ ดำเนินการ Migrate โครงสร้างฐานข้อมูล และนำเข้าข้อมูลเริ่มต้น (Default Seeds)</p>
                </div>
                <div class="max-w-md mx-auto bg-slate-900 p-5 rounded-3xl border border-slate-900 text-left font-mono text-[10px] text-slate-400 space-y-1 h-36 overflow-y-auto shadow-inner">
                    <div class="text-slate-500">> Copying .env.example into .env</div>
                    <div class="text-slate-500">> Modifying database config environment variables</div>
                    <div class="text-slate-500">> Bootstrapping Laravel core</div>
                    <div x-show="progress >= 1">> Running key:generate (force)...</div>
                    <div x-show="progress >= 2" class="text-orange-400">> Executing migrate:fresh --seed (force)...</div>
                    <div x-show="progress >= 3">> Linking public storage...</div>
                    <div x-show="progress >= 4" class="text-emerald-400">> Creating storage/installed.lock...</div>
                </div>
            </div>

            <!-- STEP 4: Success page -->
            <div x-show="step === 4" class="space-y-6 text-center py-6" x-cloak>
                <div class="w-16 h-16 bg-emerald-50 border border-emerald-100 text-emerald-500 rounded-full flex items-center justify-center text-3xl mx-auto shadow-lg shadow-emerald-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                </div>
                <div class="space-y-2">
                    <h2 class="text-slate-800 text-xl font-bold">ติดตั้งระบบเสร็จสมบูรณ์!</h2>
                    <p class="text-xs text-slate-400 font-medium">ระบบฐานข้อมูล โครงสร้างตาราง และการเชื่อมโยงไฟล์ได้รับการตั้งค่าพร้อมใช้งานเรียบร้อย</p>
                </div>

                <div class="max-w-md mx-auto bg-white border border-slate-100 p-6 rounded-3xl text-left space-y-4 shadow-sm">
                    <div class="border-b border-slate-100 pb-3">
                        <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">บัญชีผู้ดูแลระบบเริ่มต้น (Default Administrator)</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <span class="text-slate-500 font-bold">อีเมล:</span>
                        <span class="col-span-2 text-slate-800 font-mono font-bold select-all">admin@sys.com</span>
                        
                        <span class="text-slate-500 font-bold">รหัสผ่าน:</span>
                        <span class="col-span-2 text-slate-800 font-mono font-bold select-all">admin1234</span>
                    </div>
                    <div class="bg-amber-50 border border-amber-100/70 text-amber-600 p-4 rounded-2xl text-[10px] leading-relaxed">
                        ⚠️ **ข้อควรระวังเพื่อความปลอดภัย**: กรุณาเข้าสู่ระบบด้วยบัญชีด้านบนเพื่อเข้าไปเปลี่ยนรหัสผ่านทันทีในหน้าตั้งค่าข้อมูลส่วนตัว เพื่อป้องกันการเจาะระบบ
                    </div>
                </div>

                <div class="pt-6">
                    <a href="/" class="inline-block bg-slate-900 text-white hover:bg-orange-655 font-bold text-xs px-12 py-4 rounded-2xl transition duration-200 shadow-xl shadow-slate-200 active:scale-95">
                        เข้าสู่หน้าหลักของระบบ →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Alert dialog -->
    <div x-data x-show="$store.alert.open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak x-transition>
        <div class="bg-white border border-slate-100 rounded-[2rem] p-8 max-w-sm w-full text-center shadow-2xl">
            <div class="w-12 h-12 bg-rose-50 border border-rose-100 text-rose-500 rounded-2xl flex items-center justify-center text-xl mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </div>
            <h3 class="text-slate-800 font-bold text-sm mb-2" x-text="$store.alert.title"></h3>
            <p class="text-slate-400 text-xs leading-relaxed mb-6" x-text="$store.alert.message"></p>
            <button type="button" @click="$store.alert.open = false" class="w-full bg-slate-900 hover:bg-orange-600 text-white font-bold text-xs py-3 rounded-xl transition">ตกลง</button>
        </div>
    </div>

    <footer class="max-w-2xl w-full mx-auto text-center text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-6">
        © 2026 Installer Wizard • CPN1 BigData Platform
    </footer>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('alert', {
                open: false,
                title: 'เกิดข้อผิดพลาด',
                message: '',
                show(title, message) {
                    this.title = title;
                    this.message = message;
                    this.open = true;
                }
            });
        });

        function installerWizard(state) {
            return {
                step: 1,
                state: state || {},
                dbTesting: false,
                progress: 0,
                form: {
                    host: '127.0.0.1',
                    port: '3306',
                    database: 'bigdata',
                    username: 'root',
                    password: ''
                },
                composerRunning: false,
                nextStep() {
                    if (this.step === 1 && this.state.passed) {
                        this.step = 2;
                    }
                },
                runComposerInstall() {
                    this.composerRunning = true;
                    axios.post('?action=run_composer')
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.checkRequirements();
                            } else {
                                Alpine.store('alert').show('ดาวน์โหลดล้มเหลว', response.data.message);
                            }
                        })
                        .catch(error => {
                            Alpine.store('alert').show('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์เพื่อดาวน์โหลดได้');
                        })
                        .finally(() => {
                            this.composerRunning = false;
                        });
                },
                checkRequirements() {
                    axios.post('?action=check_requirements')
                        .then(response => {
                            this.state = response.data;
                        });
                },
                testDatabaseConnection() {
                    this.dbTesting = true;
                    
                    let formData = new FormData();
                    formData.append('host', this.form.host);
                    formData.append('port', this.form.port);
                    formData.append('database', this.form.database);
                    formData.append('username', this.form.username);
                    formData.append('password', this.form.password);

                    axios.post('?action=test_db', formData)
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.runInstaller();
                            } else {
                                Alpine.store('alert').show('เชื่อมต่อฐานข้อมูลล้มเหลว', response.data.message);
                            }
                        })
                        .catch(error => {
                            Alpine.store('alert').show('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์เพื่อทดสอบฐานข้อมูลได้');
                        })
                        .finally(() => {
                            this.dbTesting = false;
                        });
                },
                runInstaller() {
                    this.step = 3;
                    this.progress = 0;

                    // Fake console output delay sequence
                    let pTimer1 = setTimeout(() => this.progress = 1, 600);
                    let pTimer2 = setTimeout(() => this.progress = 2, 1300);
                    let pTimer3 = setTimeout(() => this.progress = 3, 2000);
                    let pTimer4 = setTimeout(() => this.progress = 4, 2500);

                    let formData = new FormData();
                    formData.append('host', this.form.host);
                    formData.append('port', this.form.port);
                    formData.append('database', this.form.database);
                    formData.append('username', this.form.username);
                    formData.append('password', this.form.password);

                    axios.post('?action=execute', formData)
                        .then(response => {
                            if (response.data.status === 'success') {
                                // Keep showing loading for a brief second so progress completes visually
                                setTimeout(() => {
                                    this.step = 4;
                                }, 3000);
                            } else {
                                clearTimeout(pTimer1);
                                clearTimeout(pTimer2);
                                clearTimeout(pTimer3);
                                clearTimeout(pTimer4);
                                this.step = 2; // rollback
                                Alpine.store('alert').show('ติดตั้งล้มเหลว', response.data.message);
                            }
                        })
                        .catch(error => {
                            clearTimeout(pTimer1);
                            clearTimeout(pTimer2);
                            clearTimeout(pTimer3);
                            clearTimeout(pTimer4);
                            this.step = 2; // rollback
                            const msg = error.response && error.response.data && error.response.data.message 
                                ? error.response.data.message 
                                : 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์เพื่อประมวลผลการติดตั้ง';
                            Alpine.store('alert').show('ข้อผิดพลาดระบบ', msg);
                        });
                }
            };
        }
    </script>
</body>
</html>
