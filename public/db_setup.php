<?php
/**
 * Standalone Database Reset Script
 * Bypasses CI4 routing — directly bootstraps the framework and runs migration + seed.
 * Access: https://uas.mondigi.biz.id/db_setup.php?token=JossBusMigrateSecureToken_2026_xYz&action=reset
 * 
 * IMPORTANT: Delete this file after use for security!
 */

// Increase execution time for seeding
set_time_limit(300);
ini_set('memory_limit', '512M');

// ── Token validation ──
$token = $_GET['token'] ?? '';
$secret = 'JossBusMigrateSecureToken_2026_xYz';

if (empty($token) || $token !== $secret) {
    http_response_code(403);
    die('<h1 style="color:red;font-family:sans-serif;">403 - Access Denied</h1>');
}

$action = $_GET['action'] ?? 'migrate';

// ── Bootstrap CodeIgniter 4 ──
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
chdir(FCPATH);

require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
CodeIgniter\Boot::bootSpark($paths);

// ── Run migration ──
$output = '<h1 style="color:#10b981;">DB Setup Running...</h1>';

try {
    $migrate = \Config\Services::migrations();

    if ($action === 'reset') {
        $output .= '<p>⏳ Rolling back all migrations...</p>';
        $migrate->regress(0);
        $output .= '<p>✅ Database rolled back to state 0.</p>';

        $output .= '<p>⏳ Running migrations to latest...</p>';
        $migrate->latest();
        $output .= '<p>✅ Migrations completed.</p>';

        $output .= '<p>⏳ Running DatabaseSeeder...</p>';
        $seeder = \Config\Database::seeder();
        $seeder->call('DatabaseSeeder');
        $output .= '<p>✅ DatabaseSeeder completed.</p>';

        $output .= '<h2 style="color:#10b981;">🎉 Full Reset + Seed Done!</h2>';
    } elseif ($action === 'seed') {
        $output .= '<p>⏳ Running DatabaseSeeder...</p>';
        $seeder = \Config\Database::seeder();
        $seeder->call('DatabaseSeeder');
        $output .= '<p>✅ DatabaseSeeder completed.</p>';
    } else {
        $output .= '<p>⏳ Running pending migrations...</p>';
        $migrate->latest();
        $output .= '<p>✅ Migrations completed.</p>';
    }
} catch (\Throwable $e) {
    $output .= '<h2 style="color:#ef4444;">❌ Error</h2>';
    $output .= '<pre style="background:#1e293b;color:#fecdd3;padding:1rem;border-radius:0.5rem;overflow-x:auto;">';
    $output .= htmlspecialchars($e->getMessage()) . "\n\n";
    $output .= htmlspecialchars($e->getTraceAsString());
    $output .= '</pre>';
}

echo "<!DOCTYPE html><html><head><title>DB Setup</title></head>
<body style='font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:2rem;'>
{$output}
<hr style='border-color:#334155;margin-top:2rem;'>
<p style='color:#64748b;font-size:0.8rem;'>⚠️ Delete this file (db_setup.php) after use for security.</p>
</body></html>";
