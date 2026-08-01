<?php
session_start();
require_once __DIR__ . '/security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

if (!verifyCsrf()) {
    $_SESSION['contact_error'] = 'داواکارییەکە دروست نییە. تکایە دووبارە هەوڵ بدەوە.';
    header('Location: ../index.php#contact');
    exit;
}

$conn = getDbConnection();

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];

if ($name === '') {
    $errors[] = 'تکایە ناو بنووسە.';
}

if (!preg_match('/^[0-9+\-\s]{7,15}$/', $phone)) {
    $errors[] = 'تکایە ژمارەی مۆبایلێکی ڕەسەن بنووسە.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'تکایە ئیمەیلێکی ڕەسەن بنووسە.';
}

if ($message === '') {
    $errors[] = 'تکایە نامەیەک بنووسە.';
}

if ($errors) {
    $_SESSION['contact_error'] = implode(' ', $errors);
    header('Location: ../index.php#contact');
    exit;
}

$stmt = $conn->prepare('INSERT INTO contacts (name, phone, email, message) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $name, $phone, $email, $message);

if ($stmt->execute()) {
    $_SESSION['contact_success'] = 'نامەکەت بۆ هەموو ئێمە نێردرا. ئێمە زووەوە دەگەڕێینەوە.';
} else {
    $_SESSION['contact_error'] = 'ناردنی نامە سەرکەوتوو نەبوو، تکایە دووبارە هەوڵ بدەوە.';
}

$stmt->close();
header('Location: ../index.php#contact');
exit;
?>
