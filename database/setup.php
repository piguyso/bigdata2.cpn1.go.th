<?php
// Check if .env exists, if not copy from example
$env_file = __DIR__ . '/../.env';
$env_example = __DIR__ . '/../.env.example';
if (!file_exists($env_file)) {
    if (file_exists($env_example)) {
        copy($env_example, $env_file);
        echo "Created .env from .env.example\n";
    }
}

// Check database connection
// We can load the .env values using a simple parser since Laravel isn't fully initialized
$env_content = file_exists($env_file) ? file_get_contents($env_file) : '';
$db_connection = 'mysql';
$db_host = '127.0.0.1';
$db_port = '3306';
$db_database = '';
$db_username = '';
$db_password = '';

if (preg_match('/^DB_CONNECTION=(.*)$/m', $env_content, $matches)) {
    $db_connection = trim($matches[1]);
}
if (preg_match('/^DB_HOST=(.*)$/m', $env_content, $matches)) {
    $db_host = trim($matches[1]);
}
if (preg_match('/^DB_PORT=(.*)$/m', $env_content, $matches)) {
    $db_port = trim($matches[1]);
}
if (preg_match('/^DB_DATABASE=(.*)$/m', $env_content, $matches)) {
    $db_database = trim($matches[1]);
}
if (preg_match('/^DB_USERNAME=(.*)$/m', $env_content, $matches)) {
    $db_username = trim($matches[1]);
}
if (preg_match('/^DB_PASSWORD=(.*)$/m', $env_content, $matches)) {
    $db_password = trim($matches[1]);
}

// Clean quotes
$db_database = trim($db_database, "\"'");
$db_username = trim($db_username, "\"'");
$db_password = trim($db_password, "\"'");

if (empty($db_database) || empty($db_username)) {
    echo "===================================================\n";
    echo "⚠️  Database is not configured yet in .env.\n";
    echo "Please set DB_DATABASE, DB_USERNAME, and DB_PASSWORD.\n";
    echo "Then run: php artisan migrate:fresh --seed\n";
    echo "===================================================\n";
    exit(0); // Exit with success so composer install doesn't fail
}

echo "Testing database connection to $db_database...\n";
try {
    $dsn = "mysql:host=$db_host;port=$db_port;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 3,
    ]);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database $db_database is ready.\n";
    
    // Run key:generate if key is empty
    $app_key = '';
    if (preg_match('/^APP_KEY=(.*)$/m', $env_content, $matches)) {
        $app_key = trim($matches[1]);
    }
    if (empty($app_key) || $app_key === 'base64:') {
        echo "Generating app key...\n";
        passthru('php artisan key:generate --ansi');
    }
    
    // Run migrations and seeders
    echo "Running database migrations and seeding...\n";
    passthru('php artisan migrate:fresh --force --seed');
    
    // Run storage link
    echo "Creating storage link...\n";
    passthru('php artisan storage:link --force');
    
    // Run npm build if npm is available
    $has_npm = false;
    $is_win = (strpos(PHP_OS, 'WIN') !== false || DIRECTORY_SEPARATOR === '\\');
    $check_npm_cmd = $is_win ? 'where npm >nul 2>nul' : 'which npm >/dev/null 2>&1';
    system($check_npm_cmd, $npm_status);
    if ($npm_status === 0) {
        echo "Building frontend assets...\n";
        if ($is_win) {
            passthru('npm install && npm run build');
        } else {
            passthru('npm install && npm run build');
        }
    }
    
} catch (Exception $e) {
    echo "===================================================\n";
    echo "⚠️  Could not connect to database: " . $e->getMessage() . "\n";
    echo "Please make sure database credentials in .env are correct.\n";
    echo "Once configured, you can run: php artisan migrate:fresh --seed\n";
    echo "===================================================\n";
    exit(0); // Exit with success so composer install doesn't fail
}
