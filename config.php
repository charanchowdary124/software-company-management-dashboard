<?php
// Send defensive HTTP security headers
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// Start session with secure cookie parameters
if (session_status() === PHP_SESSION_NONE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

// CSRF Protection Helpers
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Simple .env parser to read credentials
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            // Remove quotes if present
            $value = trim($value, '"\'');
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Load .env if present
loadEnv(__DIR__ . '/.env');

// Database credentials (PostgreSQL)
// User can add these to .env if they wish to connect to active Supabase
$db_host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? null);
$db_port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '5432');
$db_name = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? null);
$db_user = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? null);
$db_password = getenv('DB_PASSWORD') ?: ($_ENV['DB_PASSWORD'] ?? null);

$pdo = null;
$db_connected = false;
$db_error = null;

if ($db_host && $db_name && $db_user) {
    try {
        $dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name";
        $pdo = new PDO($dsn, $db_user, $db_password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 3 // short timeout to avoid freezing if connection fails
        ]);
        $db_connected = true;
    } catch (PDOException $e) {
        $db_error = 'Database connection failed';
        error_log('Database connection error: ' . $e->getMessage());
    }
}
