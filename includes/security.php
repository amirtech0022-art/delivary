<?php
/**
 * Central security helpers: admin auth (hashed), login rate limiting,
 * CSRF protection, and validated image uploads.
 *
 * Every page that includes this file must have already called session_start().
 */
require_once __DIR__ . '/db.php';

/* -------------------------------------------------------------------------
 * Admin users (hashed passwords)
 * ---------------------------------------------------------------------- */

function ensureAdminUsersTable()
{
    static $done = false;
    if ($done) return;
    $conn = getDbConnection();
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Seed the default admin the first time so nobody gets locked out.
    // Keeps the previous credentials (admin / admin123) but now stored hashed.
    $res = mysqli_query($conn, 'SELECT COUNT(*) AS c FROM admin_users');
    $count = (int)($res->fetch_assoc()['c'] ?? 0);
    if ($count === 0) {
        $username = 'admin';
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
        $stmt->bind_param('ss', $username, $hash);
        $stmt->execute();
    }
    $done = true;
}

/**
 * Verify credentials against the hashed store. Returns true on success.
 */
function verifyAdminLogin($username, $password)
{
    ensureAdminUsersTable();
    $conn = getDbConnection();
    $stmt = $conn->prepare('SELECT password_hash FROM admin_users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) {
        // Run a dummy verify so response timing does not reveal valid usernames.
        password_verify($password, '$2y$10$usesomesillystringforsalt.................................');
        return false;
    }
    return password_verify($password, $row['password_hash']);
}

/**
 * Change an admin password (hashed). Returns true on success.
 */
function updateAdminPassword($username, $newPassword)
{
    ensureAdminUsersTable();
    $conn = getDbConnection();
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('UPDATE admin_users SET password_hash = ? WHERE username = ?');
    $stmt->bind_param('ss', $hash, $username);
    $stmt->execute();
    return $stmt->affected_rows >= 0;
}

/**
 * Guard admin-only pages. Redirects to login when not authenticated.
 */
function requireAdmin()
{
    if (!($_SESSION['admin_logged_in'] ?? false)) {
        header('Location: login.php');
        exit;
    }
}

/* -------------------------------------------------------------------------
 * Login rate limiting (per IP, DB backed)
 * ---------------------------------------------------------------------- */

const LOGIN_MAX_ATTEMPTS = 5;      // allowed failures ...
const LOGIN_WINDOW_MIN   = 15;     // ... within this many minutes

function ensureLoginAttemptsTable()
{
    static $done = false;
    if ($done) return;
    $conn = getDbConnection();
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ip_time (ip_address, attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $done = true;
}

function currentIp()
{
    return substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45);
}

function failedLoginCount($ip)
{
    ensureLoginAttemptsTable();
    $conn = getDbConnection();
    $window = LOGIN_WINDOW_MIN;
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS c FROM login_attempts
         WHERE ip_address = ? AND attempted_at > (NOW() - INTERVAL $window MINUTE)"
    );
    $stmt->bind_param('s', $ip);
    $stmt->execute();
    return (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
}

function isLoginLocked($ip)
{
    return failedLoginCount($ip) >= LOGIN_MAX_ATTEMPTS;
}

function recordFailedLogin($ip)
{
    ensureLoginAttemptsTable();
    $conn = getDbConnection();
    $stmt = $conn->prepare('INSERT INTO login_attempts (ip_address) VALUES (?)');
    $stmt->bind_param('s', $ip);
    $stmt->execute();
}

function clearLoginAttempts($ip)
{
    ensureLoginAttemptsTable();
    $conn = getDbConnection();
    $stmt = $conn->prepare('DELETE FROM login_attempts WHERE ip_address = ?');
    $stmt->bind_param('s', $ip);
    $stmt->execute();
}

/* -------------------------------------------------------------------------
 * CSRF protection
 * ---------------------------------------------------------------------- */

function csrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            $_SESSION['csrf_token'] = bin2hex((string)mt_rand() . uniqid('', true));
        }
    }
    return $_SESSION['csrf_token'];
}

/** Hidden input to drop inside any state-changing form. */
function csrfField()
{
    $t = htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $t . '" />';
}

/** True when the submitted token matches the session token. */
function verifyCsrf()
{
    $sent = $_POST['csrf_token'] ?? '';
    $known = $_SESSION['csrf_token'] ?? '';
    return $known !== '' && is_string($sent) && hash_equals($known, $sent);
}

/* -------------------------------------------------------------------------
 * Validated image upload
 * ---------------------------------------------------------------------- */

/**
 * Validate and store an uploaded image. Returns the site-relative path
 * (e.g. "assets/uploads/xyz.png") on success, or null on failure with
 * a human message written to $error.
 */
function saveUploadedImage(array $file, string $prefix, ?string &$error): ?string
{
    $error = null;

    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $error = 'هیچ فایلێک هەڵنەبژێردرا.';
        return null;
    }
    if (($file['size'] ?? 0) > 3 * 1024 * 1024) {
        $error = 'قەبارەی فایلەکە زۆر گەورەیە (زۆرترین ٣MB).';
        return null;
    }

    $ext = strtolower(pathinfo(basename($file['name']), PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    if (!in_array($ext, $allowed, true)) {
        $error = 'جۆری فایل ڕێگەپێدراو نییە. تەنها وێنە (jpg, png, gif, webp, svg).';
        return null;
    }

    $tmp = $file['tmp_name'];

    if ($ext === 'svg') {
        // SVG is XML text: make sure it really is an svg and carries no scripts.
        $content = (string)file_get_contents($tmp);
        if (stripos($content, '<svg') === false) {
            $error = 'فایلی SVG دروست نییە.';
            return null;
        }
        if (preg_match('/<script|onload=|onerror=|javascript:/i', $content)) {
            $error = 'فایلی SVG ناوەڕۆکی مەترسیداری تێدایە و ڕەتکرایەوە.';
            return null;
        }
    } else {
        // Raster: confirm the real content type matches the extension.
        $info = @getimagesize($tmp);
        $expected = [
            'jpg'  => IMAGETYPE_JPEG,
            'jpeg' => IMAGETYPE_JPEG,
            'png'  => IMAGETYPE_PNG,
            'gif'  => IMAGETYPE_GIF,
            'webp' => defined('IMAGETYPE_WEBP') ? IMAGETYPE_WEBP : -1,
        ];
        if ($info === false || ($info[2] ?? 0) !== ($expected[$ext] ?? -2)) {
            $error = 'ناوەڕۆکی فایلەکە لەگەڵ جۆرەکەی ناگونجێت.';
            return null;
        }
    }

    $uploadsDir = __DIR__ . '/../assets/uploads/';
    if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
    try {
        $random = bin2hex(random_bytes(4));
    } catch (Exception $e) {
        $random = uniqid();
    }
    $newName = $prefix . time() . '_' . $random . '.' . $ext;
    if (!move_uploaded_file($tmp, $uploadsDir . $newName)) {
        $error = 'هەڵە ڕوویدا لە پاشەکەوتکردنی فایلەکە.';
        return null;
    }
    return 'assets/uploads/' . $newName;
}
