<?php
/**
 * Standalone Database Reset Script
 * Access: https://uas.mondigi.biz.id/db_setup.php?token=...&action=reset
 * Force redeploy v6
 */
set_time_limit(300);
ini_set('memory_limit', '512M');

$token = $_GET['token'] ?? '';
if (empty($token) || $token !== 'JossBusMigrateSecureToken_2026_xYz') {
    http_response_code(403);
    die('403 Forbidden');
}

$action = $_GET['action'] ?? 'migrate';

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
chdir(FCPATH);

if (isset($_GET['debug'])) {
    header('Content-Type: text/plain');
    echo "PHP Version: " . PHP_VERSION . "\n";
    echo "FCPATH: " . FCPATH . "\n";
    echo "systemDirectory exists: " . (file_exists(__DIR__ . '/../system') ? 'YES' : 'NO') . "\n";
    echo "vendor/autoload exists: " . (file_exists(__DIR__ . '/../vendor/autoload.php') ? 'YES' : 'NO') . "\n";
    echo "app/Config/Database exists: " . (file_exists(__DIR__ . '/../app/Config/Database.php') ? 'YES' : 'NO') . "\n";
    echo ".env exists: " . (file_exists(__DIR__ . '/../.env') ? 'YES' : 'NO') . "\n";
    
    echo "\n=== .htaccess Content ===\n";
    if (file_exists(__DIR__ . '/.htaccess')) {
        echo file_get_contents(__DIR__ . '/.htaccess') . "\n";
    } else {
        echo ".htaccess not found\n";
    }
    echo "=========================\n\n";
    
    if (file_exists(__DIR__ . '/../.env')) {
        $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $env = [];
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            list($key, $value) = explode('=', $line, 2) + [NULL, NULL];
            if ($key !== NULL) {
                $env[trim($key)] = trim($value, " '\"");
            }
        }
        $host = $env['database.default.hostname'] ?? 'localhost';
        $db = $env['database.default.database'] ?? '';
        $user = $env['database.default.username'] ?? '';
        $pass = $env['database.default.password'] ?? '';
        echo "Env DB config found: host=$host, db=$db, user=$user\n";
        $conn = @new mysqli($host, $user, $pass, $db);
        if ($conn->connect_error) {
            echo "Connection failed: " . $conn->connect_error . "\n";
        } else {
            echo "Connection SUCCESS!\n";
            $conn->close();
        }
    }
    exit;
}
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
CodeIgniter\Boot::bootSpark($paths);

$output = '';
try {
    $migrate = \Config\Services::migrations();
    if ($action === 'reset') {
        $migrate->regress(0);
        $output .= "Rolled back.\n";
        $migrate->latest();
        $output .= "Migrated.\n";
        $seeder = \Config\Database::seeder();
        $seeder->call('DatabaseSeeder');
        $output .= "Seeded.\n";
    } elseif ($action === 'seed') {
        $seeder = \Config\Database::seeder();
        $seeder->call('DatabaseSeeder');
        $output .= "Seeded.\n";
    } else {
        $migrate->latest();
        $output .= "Migrated.\n";
    }
    echo "SUCCESS\n" . $output;
} catch (\Throwable $e) {
    http_response_code(500);
    echo "ERROR\n" . $e->getMessage();
}
