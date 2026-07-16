<?php
/**
 * Simple Pure PHP Laravel Installer
 * Securely sets up the .env file, database, migrations, seeders, and storage link.
 */

define('INSTALLER_LOCK_FILE', __DIR__ . '/../storage/installed.lock');
define('ENV_FILE', __DIR__ . '/../.env');
define('ENV_EXAMPLE_FILE', __DIR__ . '/../.env.example');

// 1. Security Check: Block if already installed
if (file_exists(ENV_FILE) && file_exists(INSTALLER_LOCK_FILE)) {
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
        <div class="max-w-md w-full bg-white/70 backdrop-blur-2xl border border-white rounded-[3rem] p-10 text-center shadow-2xl relative z-10">
            <div class="w-16 h-16 bg-orange-500/10 border border-orange-500/20 text-orange-500 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-6">
                🔒
            </div>
            <h2 class="text-slate-800 text-xl font-bold mb-3">ระบบได้รับการติดตั้งเรียบร้อยแล้ว</h2>
            <p class="text-sm text-slate-400 leading-relaxed mb-6">หากคุณต้องการทำการติดตั้งใหม่อีกครั้ง กรุณาลบไฟล์ล็อกการติดตั้งที่ <code>storage/installed.lock</code> ออกก่อน</p>
            <a href="/" class="inline-block bg-slate-900 hover:bg-orange-600 text-white font-bold text-sm px-8 py-4 rounded-2xl transition duration-200 w-full">เข้าสู่หน้าแรกของระบบ</a>
        </div>
    </body>
    </html>';
    exit;
}

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$error = '';
$warning = '';
$success = false;
$env_content_to_copy = '';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$scriptDir = str_replace('\\', '/', $scriptDir);
$scriptDir = rtrim(str_replace('/public', '', $scriptDir), '/');
$defaultAppUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . $scriptDir;

// Check if vendor folder exists
$vendor_exists = file_exists(__DIR__ . '/../vendor/autoload.php');

// Check required PHP extensions
$required_extensions = [
    'zip'      => 'จำเป็นสำหรับการอ่านไฟล์ XLSX (ZipArchive)',
    'pdo_mysql'=> 'จำเป็นสำหรับเชื่อมต่อ MySQL/MariaDB',
    'curl'     => 'จำเป็นสำหรับ HTTP requests ภายนอก (BOPP, HRMS)',
    'mbstring' => 'จำเป็นสำหรับข้อความภาษาไทย (UTF-8)',
    'openssl'  => 'จำเป็นสำหรับ APP_KEY และ HTTPS',
    'fileinfo' => 'จำเป็นสำหรับการตรวจสอบประเภทไฟล์',
    'xml'      => 'จำเป็นสำหรับการ parse XLSX (SimpleXML)',
];
$missing_extensions = [];
foreach ($required_extensions as $ext => $desc) {
    if (!extension_loaded($ext)) {
        $missing_extensions[$ext] = $desc;
    }
}
$extensions_ok = empty($missing_extensions);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        // Step 1: Process DB credentials & try to write .env
        $host = $_POST['host'] ?? '127.0.0.1';
        $port = $_POST['port'] ?? '3306';
        $database = $_POST['database'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $appUrl = $_POST['app_url'] ?? '';

        // Test database connection
        try {
            $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 4,
            ]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Connection succeeded, now prepare .env file
            $envExample = file_exists(ENV_EXAMPLE_FILE) ? file_get_contents(ENV_EXAMPLE_FILE) : '';
            if (empty($envExample)) {
                throw new Exception('ไม่พบไฟล์ .env.example ในโปรเจกต์');
            }

            $appKey = 'base64:' . base64_encode(random_bytes(32));
            
            // Replace DB variables
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
            $newEnvContent = preg_replace('/APP_KEY=[^\r\n]*/', "APP_KEY=$appKey", $newEnvContent);
            $newEnvContent = preg_replace('/APP_URL=[^\r\n]*/', "APP_URL=$appUrl", $newEnvContent);

            // Attempt to write the file
            if (@file_put_contents(ENV_FILE, $newEnvContent)) {
                // Successfully written! Go straight to Step 3 (Migration)
                header('Location: ?step=3');
                exit;
            } else {
                // Write failed, save content and redirect to Step 2 (Manual copy)
                session_start();
                $_SESSION['env_content'] = $newEnvContent;
                header('Location: ?step=2');
                exit;
            }
        } catch (Exception $e) {
            $error = 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้: ' . $e->getMessage();
        }
    } elseif ($step === 2) {
        // Step 2: Check if user has created the .env file
        if (file_exists(ENV_FILE)) {
            header('Location: ?step=3');
            exit;
        } else {
            $error = 'ไม่พบไฟล์ .env ในระบบ กรุณาสร้างไฟล์ตามที่ระบุก่อนกดดำเนินต่อ';
            session_start();
            $env_content_to_copy = $_SESSION['env_content'] ?? '';
        }
    }
}

if ($step === 2) {
    if (empty($env_content_to_copy)) {
        session_start();
        $env_content_to_copy = $_SESSION['env_content'] ?? '';
    }
}

$migration_output = '';
if ($step === 3) {
    // Run migrations
    if (file_exists(ENV_FILE)) {
        $disabled_functions = explode(',', ini_get('disable_functions'));
        $disabled_functions = array_map('trim', $disabled_functions);
        $has_shell = function_exists('shell_exec') && !in_array('shell_exec', $disabled_functions);
        
        if ($has_shell) {
            $root_dir = realpath(__DIR__ . '/../');
            
            // Dynamically detect the PHP CLI binary path
            $php_path = PHP_BINARY;
            if (preg_match('/php-cgi|php-fpm/i', $php_path)) {
                $dir = dirname($php_path);
                $is_windows = (strpos(PHP_OS, 'WIN') !== false || DIRECTORY_SEPARATOR === '\\');
                $cli_php = $dir . ($is_windows ? '/php.exe' : '/php');
                if (file_exists($cli_php)) {
                    $php_path = $cli_php;
                } else {
                    $php_path = 'php'; // Fallback to global php command
                }
            }
            $php_bin = escapeshellarg($php_path);

            // Execute migrate:fresh --force --seed
            $migration_output = shell_exec("cd " . escapeshellarg($root_dir) . " && $php_bin artisan migrate:fresh --force --seed 2>&1");
            
            // Try storage:link
            @shell_exec("cd " . escapeshellarg($root_dir) . " && $php_bin artisan storage:link --force 2>&1");
        } else {
            $warning = 'คำเตือน: เซิร์ฟเวอร์นี้ปิดฟังก์ชัน shell_exec หรือไม่สามารถสั่งรันคำสั่งอัตโนมัติได้ หากตารางฐานข้อมูลยังไม่ถูกสร้าง กรุณาเข้าสู่ระบบผ่าน Command Line (Terminal) แล้วพิมพ์คำสั่ง php artisan migrate:fresh --seed ด้วยตนเอง';
        }

        // Create lock file
        @file_put_contents(INSTALLER_LOCK_FILE, json_encode([
            'installed_at' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR']
        ], JSON_PRETTY_PRINT));
        $success = true;
    } else {
        // No env file, redirect to step 1
        header('Location: ?step=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดตั้งระบบ | Setup Wizard</title>
    <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; background-color: #f8fafc; color: #475569; }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between py-12 px-6 relative">
    <!-- Blurred circles background -->
    <div class="absolute top-20 left-10 w-64 h-64 bg-orange-200 rounded-full blur-[100px] opacity-30 animate-pulse"></div>
    <div class="absolute bottom-10 right-10 w-80 h-80 bg-sky-200 rounded-full blur-[120px] opacity-30"></div>

    <div class="max-w-2xl w-full mx-auto bg-white/70 backdrop-blur-2xl border border-white rounded-[3.5rem] shadow-2xl overflow-hidden flex flex-col relative z-10">
        
        <!-- Header -->
        <div class="bg-white/50 border-b border-slate-100 px-8 py-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg">
                    ⚡
                </div>
                <div>
                    <h1 class="text-slate-800 text-base font-extrabold tracking-tight">ติดตั้งระบบบริหารจัดการข้อมูล</h1>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Setup Wizard (Pure PHP)</p>
                </div>
            </div>
            <div class="text-xs font-extrabold text-slate-400">
                ขั้นตอนที่ <?= $step ?> / 3
            </div>
        </div>

        <div class="p-8 md:p-12">
            
            <!-- PHP Extension Requirements -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">PHP Extensions ที่จำเป็น</span>
                    <?php if ($extensions_ok): ?>
                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-100 px-3 py-1 rounded-full">✔ ครบทุก Extension</span>
                    <?php else: ?>
                        <span class="text-[10px] font-bold text-rose-600 bg-rose-50 border border-rose-100 px-3 py-1 rounded-full">✗ ขาด <?= count($missing_extensions) ?> Extension</span>
                    <?php endif; ?>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <?php foreach ($required_extensions as $ext => $desc): ?>
                        <?php $ok = extension_loaded($ext); ?>
                        <div class="flex items-center gap-2 px-3 py-2 rounded-xl border text-[10px] font-bold
                            <?= $ok ? 'bg-emerald-50 border-emerald-100 text-emerald-700' : 'bg-rose-50 border-rose-100 text-rose-700' ?>">
                            <span><?= $ok ? '✔' : '✗' ?></span>
                            <span><?= $ext ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (!$extensions_ok): ?>
                    <div class="mt-3 p-3 bg-rose-50 border border-rose-100 rounded-xl text-[10px] text-rose-600 leading-relaxed">
                        <?php foreach ($missing_extensions as $ext => $desc): ?>
                            <div>• <strong><?= $ext ?>:</strong> <?= $desc ?></div>
                        <?php endforeach; ?>
                        <div class="mt-2 font-bold">เปิด Extension ได้ที่ php.ini โดยลบ <code>;</code> หน้า <code>extension=<?= array_key_first($missing_extensions) ?></code></div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!$vendor_exists): ?>
                <!-- Autoloader Warning -->
                <div class="p-5 bg-orange-50 border border-orange-100 rounded-2xl mb-6">
                    <span class="text-xs font-bold text-orange-850">⚠️ ตรวจพบว่าไม่มีโฟลเดอร์ vendor</span>
                    <p class="text-[10px] text-orange-600 mt-1">กรุณารันคำสั่ง <code>composer install</code> หรืออัปโหลดโฟลเดอร์ vendor ขึ้นมายังเซิร์ฟเวอร์ก่อนดำเนินงาน</p>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <!-- Error Notice -->
                <div class="p-5 bg-rose-50 border border-rose-100 text-rose-700 rounded-2xl mb-6 text-xs leading-relaxed">
                    <strong>ข้อผิดพลาด:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <!-- STEP 1: DB FORM -->
                <div class="space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-slate-800 text-lg font-extrabold">ป้อนค่าการเชื่อมต่อฐานข้อมูล (MySQL / MariaDB)</h2>
                        <p class="text-xs text-slate-400 mt-1">ระบบจะทำการทดสอบการเชื่อมต่อและพยายามสร้างฐานข้อมูลให้โดยอัตโนมัติ</p>
                    </div>

                    <form method="POST" action="?step=1" class="space-y-5">
                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-2 space-y-1.5">
                                <label class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 ml-1">Database Host</label>
                                <input type="text" name="host" value="127.0.0.1" required class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-orange-100 focus:border-orange-400 outline-none transition text-xs text-slate-800">
                            </div>
                            <div class="col-span-1 space-y-1.5">
                                <label class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 ml-1">Port</label>
                                <input type="text" name="port" value="3306" required class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-orange-100 focus:border-orange-400 outline-none transition text-xs text-slate-800">
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 ml-1">ชื่อฐานข้อมูล (Database Name)</label>
                            <input type="text" name="database" value="bigdata" required class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-orange-100 focus:border-orange-400 outline-none transition text-xs text-slate-800">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 ml-1">ที่อยู่เว็บแอปพลิเคชัน (APP_URL)</label>
                            <input type="text" name="app_url" value="<?= htmlspecialchars($defaultAppUrl) ?>" required class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-orange-100 focus:border-orange-400 outline-none transition text-xs text-slate-800" placeholder="เช่น http://localhost">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 ml-1">ผู้ใช้ (DB Username)</label>
                                <input type="text" name="username" value="root" required class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-orange-100 focus:border-orange-400 outline-none transition text-xs text-slate-800">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 ml-1">รหัสผ่าน (DB Password)</label>
                                <input type="password" name="password" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-orange-100 focus:border-orange-400 outline-none transition text-xs text-slate-800">
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 flex justify-end">
                            <button type="submit" <?= (!$vendor_exists || !$extensions_ok) ? 'disabled' : '' ?> class="bg-slate-900 text-white font-bold text-xs px-8 py-3.5 rounded-2xl hover:bg-orange-600 transition shadow-lg active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed">
                                ทดสอบและบันทึกติดตั้ง →
                            </button>
                        </div>
                    </form>
                </div>

            <?php elseif ($step === 2): ?>
                <!-- STEP 2: MANUAL COPY -->
                <div class="space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-2 text-orange-600 font-bold text-sm">
                            ⚠️ <span>เซิร์ฟเวอร์ไม่มีสิทธิ์เขียนไฟล์คอนฟิก (.env)</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">กรุณานำข้อความคอนฟิกด้านล่างนี้ ไปสร้างไฟล์ชื่อ <strong>.env</strong> ไว้ที่โฟลเดอร์หลักของโปรเจกต์ด้วยตนเอง</p>
                    </div>

                    <div class="space-y-4">
                        <div class="relative">
                            <textarea readonly class="w-full h-64 p-4 bg-slate-900 text-slate-100 font-mono text-[10px] rounded-2xl border border-slate-950 outline-none select-all" id="envCode"><?= htmlspecialchars($env_content_to_copy) ?></textarea>
                            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('envCode').value); alert('คัดลอกรหัสคอนฟิกไปยังคลิปบอร์ดแล้ว')" class="absolute top-2 right-2 bg-slate-800 hover:bg-slate-700 text-white font-bold text-[10px] px-3 py-1.5 rounded-xl border border-slate-700 active:scale-95 transition">คัดลอก</button>
                        </div>

                        <form method="POST" action="?step=2" class="pt-6 border-t border-slate-100 flex justify-between gap-4">
                            <a href="?step=1" class="border border-slate-200 hover:bg-slate-100 text-slate-650 font-bold text-xs px-6 py-3.5 rounded-2xl transition text-center flex items-center justify-center">
                                ย้อนกลับ
                            </a>
                            <button type="submit" class="bg-slate-900 text-white font-bold text-xs px-8 py-3.5 rounded-2xl hover:bg-orange-600 transition shadow-lg active:scale-95">
                                ฉันสร้างไฟล์ .env เรียบร้อยแล้ว (ดำเนินการติดตั้งต่อ) →
                            </button>
                        </form>
                    </div>
                </div>

            <?php elseif ($step === 3 && $success): ?>
                <!-- STEP 3: SUCCESS & MIGRATION OUTPUT -->
                <div class="space-y-6 text-center">
                    <div class="w-16 h-16 bg-emerald-50 border border-emerald-100 text-emerald-500 rounded-full flex items-center justify-center text-3xl mx-auto shadow-lg">
                        ✔
                    </div>
                    <div class="space-y-2">
                        <h2 class="text-slate-800 text-xl font-bold">ติดตั้งระบบเสร็จสมบูรณ์!</h2>
                        <p class="text-xs text-slate-400 font-medium">ระบบฐานข้อมูล โครงสร้างตาราง และการเชื่อมโยงไฟล์ได้รับการตั้งค่าเรียบร้อย</p>
                    </div>

                    <?php if (!empty($warning)): ?>
                        <div class="p-4 bg-amber-50 border border-amber-100 text-amber-600 rounded-2xl text-[10px] leading-relaxed text-left">
                            <?= $warning ?>
                        </div>
                    <?php endif; ?>

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
                        <div class="bg-orange-50 border border-orange-100 text-orange-600 p-4 rounded-2xl text-[10px] leading-relaxed">
                            ⚠️ กรุณาเข้าสู่ระบบและเปลี่ยนรหัสผ่านเพื่อความปลอดภัยในทันที
                        </div>
                    </div>

                    <?php if (!empty($migration_output)): ?>
                        <div class="max-w-md mx-auto text-left">
                            <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 ml-1">ผลลัพธ์การติดตั้ง (Migration Output):</span>
                            <pre class="w-full h-32 p-3 bg-slate-900 text-slate-400 font-mono text-[9px] rounded-2xl border border-slate-950 overflow-y-auto mt-2"><?= htmlspecialchars($migration_output) ?></pre>
                        </div>
                    <?php endif; ?>

                    <div class="pt-6 border-t border-slate-100">
                        <a href="/" class="inline-block bg-slate-900 text-white hover:bg-orange-600 font-bold text-xs px-12 py-4 rounded-2xl transition duration-200 shadow-xl active:scale-95 w-full md:w-auto">
                            เข้าสู่หน้าหลักของระบบ →
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <footer class="max-w-2xl w-full mx-auto text-center text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-6">
        © 2026 Installer Wizard • CPN1 BigData Platform
    </footer>
</body>
</html>
