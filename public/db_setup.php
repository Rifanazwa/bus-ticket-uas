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
