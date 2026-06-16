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

if ($action === 'setup_env') {
    header('Content-Type: text/plain');
    $envContent = <<<EOT
CI_ENVIRONMENT = production

app.baseURL = 'https://uas.mondigi.biz.id/'
app.forceGlobalSecureRequests = true
app.CSPEnabled = false

database.default.hostname = localhost
database.default.database = zabhkkbq_uas
database.default.username = zabhkkbq_uas
database.default.password = UasWeb1234
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_general_ci

encryption.key = hex2bin:31e0517bad8306bff122298fb1c01d9b739310e0e88f7192593949aea082b80b

session.driver = 'CodeIgniter\Session\Handlers\FileHandler'
session.savePath = writable/session

logger.threshold = 4

gemini.apiKey = 'AIzaSyDsL9yzlw-vXrp631BtCZGeiS77Mp8Sg8g'

midtrans.serverKey = 'SB-Mid-server-YOUR_SERVER_KEY'
midtrans.clientKey = 'SB-Mid-client-YOUR_CLIENT_KEY'
midtrans.isProduction = false
midtrans.isSanitized = true
midtrans.is3ds = true
EOT;
    if (file_put_contents(__DIR__ . '/../.env', $envContent)) {
        echo "SUCCESS: Server .env configured successfully.\n";
    } else {
        echo "ERROR: Failed to write .env file.\n";
    }
    exit;
}

require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
CodeIgniter\Boot::bootWeb($paths);

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
