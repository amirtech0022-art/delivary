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
?>
