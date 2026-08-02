<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'amir_technology';

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die('داتابەیسەکە نەکراوە: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');

function getDbConnection()
{
    global $conn;
    return $conn;
}

/**
 * Make sure the key/value settings table exists.
 * Runs a lightweight CREATE TABLE IF NOT EXISTS so no manual migration is needed.
 */
function ensureSettingsTable()
{
    static $done = false;
    if ($done) return;
    $conn = getDbConnection();
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $done = true;
}

/**
 * Read a single setting value, or $default when it has never been set.
 */
function getSetting($key, $default = '')
{
    ensureSettingsTable();
    $conn = getDbConnection();
    $stmt = $conn->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return ($row && $row['setting_value'] !== null) ? $row['setting_value'] : $default;
}

/**
 * Insert or update a single setting value.
 */
function setSetting($key, $value)
{
    ensureSettingsTable();
    $conn = getDbConnection();
    $stmt = $conn->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ss', $key, $value);
    return $stmt->execute();
}

/**
 * Make sure the visits table exists (visitor analytics).
 */
function ensureVisitsTable()
{
    static $done = false;
    if ($done) return;
    $conn = getDbConnection();
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS visits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        visit_date DATE NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_visit_date (visit_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $done = true;
}

/**
 * Record one visit for the current browser session, at most once per day.
 * Logged-in admins are not counted so the numbers reflect real visitors.
 */
function recordVisit()
{
    if ($_SESSION['admin_logged_in'] ?? false) return;

    $today = date('Y-m-d');
    if (($_SESSION['last_visit_day'] ?? '') === $today) return;
    $_SESSION['last_visit_day'] = $today;

    ensureVisitsTable();
    $conn = getDbConnection();
    $ip = substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45);
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $stmt = $conn->prepare('INSERT INTO visits (visit_date, ip_address, user_agent) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $today, $ip, $ua);
    $stmt->execute();
}

/**
 * Strip non-digits and return an international number for tel:/wa.me links.
 * Accepts local formats like 0770 540 1561 or +964 770 540 1561.
 */
function phoneDigitsForLinks(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone);
    if ($digits === '') {
        return '';
    }
    if ($digits[0] === '0') {
        return '964' . substr($digits, 1);
    }
    return $digits;
}

?>
