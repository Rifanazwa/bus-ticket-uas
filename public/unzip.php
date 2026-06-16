<?php
/**
 * Standalone Unzip Utility for vendor folder.
 * Avoids booting CodeIgniter to prevent dependency errors during upload.
 */

$token = $_GET['token'] ?? '';
$secretToken = 'JossBusMigrateSecureToken_2026_xYz';

if (empty($token) || $token !== $secretToken) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Access Denied: Invalid Token';
    exit;
}

$zipPath = __DIR__ . '/../vendor.zip';
$targetDir = __DIR__ . '/../';

if (!file_exists($zipPath)) {
    echo 'Skip: vendor.zip not found (already unzipped or not uploaded).';
    exit;
}

if (!class_exists('ZipArchive')) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Error: ZipArchive extension is not enabled on this server PHP config.';
    exit;
}

$zip = new ZipArchive();
if ($zip->open($zipPath) === TRUE) {
    // Extract everything
    $zip->extractTo($targetDir);
    $zip->close();
    
    // Delete zip after successful extraction to save space
    unlink($zipPath);
    echo 'Success: vendor.zip unzipped successfully.';
} else {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Error: Failed to open vendor.zip.';
}
