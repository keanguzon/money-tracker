<?php
/**
 * Application Configuration
 * Money Tracker Application
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application settings
define('APP_NAME', 'MoneyTrack');
define('APP_VERSION', '1.0.0');

// Base URL (auto-detect when running under a web server like XAMPP)
if (!defined('APP_URL')) {
    $fallbackUrl = 'http://localhost/money-tracker';

    if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['SCRIPT_NAME'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);

        // Try to infer the project root from common subfolders
        $rootPath = '';
        if (preg_match('#^(.*?)/(pages|api|assets)/#', $scriptName, $m)) {
            $rootPath = $m[1];
        } else {
            // Example: /money-tracker/index.php -> /money-tracker
            $rootPath = rtrim(dirname($scriptName), '/');
        }

        // Avoid "//" when at domain root
        if ($rootPath === '/' || $rootPath === '.') {
            $rootPath = '';
        }

        define('APP_URL', $scheme . '://' . $host . $rootPath);
    } else {
        define('APP_URL', $fallbackUrl);
    }
}

// Default settings
define('DEFAULT_CURRENCY', 'PHP');
define('DEFAULT_TIMEZONE', 'Asia/Manila');

// Set timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load environment variables from .env if present (local development)
// Format: KEY=value (lines starting with # are ignored)
$envFile = BASE_PATH . '/.env';
if (is_file($envFile) && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }
            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            if ($key === '' || getenv($key) !== false) {
                continue;
            }
            // Strip optional surrounding quotes
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            putenv($key . '=' . $value);
        }
    }
}

// Include database configuration
require_once BASE_PATH . '/config/database.php';

// Helper functions
function redirect($url) {
    header("Location: " . APP_URL . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function formatCurrency($amount, $currency = 'PHP') {
    $symbols = [
        'PHP' => '₱',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥'
    ];
    $symbol = $symbols[$currency] ?? $currency;
    return $symbol . number_format($amount, 2);
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
