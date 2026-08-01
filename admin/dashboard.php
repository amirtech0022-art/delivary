<?php
session_start();
if (!($_SESSION['admin_logged_in'] ?? false)) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../includes/db.php';
$conn = getDbConnection();

$messages = [];
$result = mysqli_query($conn, 'SELECT * FROM contacts ORDER BY id DESC');
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ئەدمین | دۆخی پێشکەشەکان</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Sans+Arabic:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="assets.css" />
  <style>
    table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
    th, td { border-bottom: 1px solid var(--border); padding: 0.8rem; text-align: right; vertical-align: top; }
    th { background: var(--pale-blue); }
    .actions a { color: var(--primary-deep); font-weight: 700; }
  </style>
</head>
<body>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <div class="admin-brand">
        <div class="mark">A</div>
        <div>ئەمیر تەکنەلۆجی</div>
      </div>
      <nav class="admin-nav">
        <a class="active" href="dashboard.php">📊 داشبۆرد</a>
        <a href="manage.php?section=services">🛠 خزمەتگوزارییەکان</a>
        <a href="manage.php?section=projects">🧩 پڕۆژەکان</a>
        <a href="manage.php?section=videos">🎬 ڤیدیۆکان</a>
        <a href="settings.php">⚙️ ڕێکخستنەکان</a>
        <a href="logout.php">🚪 دەرچوون</a>
      </nav>
    </aside>

    <main class="admin-main">
      <div class="admin-card">
        <div class="topbar" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
          <h1 style="margin:0;">داشبۆردی ئەدمین</h1>
          <a class="btn btn-secondary" href="logout.php">دەرچوون</a>
        </div>

        <h2>نامەکانی پێشکەشکراو</h2>
        <table>
          <thead>
            <tr>
              <th>ناو</th>
              <th>مۆبایل</th>
              <th>ئیمەیل</th>
              <th>نامە</th>
              <th>کاتی ناردن</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($messages as $msg): ?>
              <tr>
                <td><?= htmlspecialchars($msg['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($msg['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($msg['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($msg['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>
