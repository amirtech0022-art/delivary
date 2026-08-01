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
    $stmt->bind_param('ss', $key, $value);
    $stmt->execute();
}
?>
