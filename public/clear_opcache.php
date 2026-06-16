<?php
// Secure OPcache reset script
$token = $_GET['token'] ?? '';
if ($token !== 'JossBusMigrateSecureToken_2026_xYz') {
    die('Access Denied');
}

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache has been reset successfully! All updated files are now reloaded fresh.";
} else {
    echo "OPcache function is not available on this server.";
}
