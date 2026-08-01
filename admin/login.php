<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    }

    $error = 'ناوی بەکارهێنەر یان تێپەڕەوشە هەڵەیە.';
}
?>
<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>چوونەژوورەوەی ئەدمین</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Sans+Arabic:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/style.css" />
  <style>
    body { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    .login-box { width: min(420px, 100% - 2rem); background: white; border-radius: 24px; padding: 2rem; box-shadow: var(--shadow); border: 1px solid var(--border); }
    .login-box h1 { margin-bottom: 1rem; font-size: 1.5rem; }
    .login-box input { width: 100%; margin-bottom: 0.9rem; }
    .login-box button { width: 100%; }
    .error { color: #c62828; margin-bottom: 1rem; font-weight: 700; }
  </style>
</head>
<body>
  <div class="login-box">
    <h1>چوونەژوورەوەی ئەدمین</h1>
    <?php if (!empty($error)) echo '<div class="error">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>'; ?>
    <form method="post">
      <input type="text" name="username" placeholder="ناوی بەکارهێنەر" required />
      <input type="password" name="password" placeholder="تێپەڕەوشە" required />
      <button class="btn btn-primary" type="submit">چوونەژوورەوە</button>
    </form>
  </div>
</body>
</html>
