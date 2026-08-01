<?php
session_start();
if (!($_SESSION['admin_logged_in'] ?? false)) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ئەدمین | ڕێکخستنەکان</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Sans+Arabic:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="assets.css" />
  <style>
    .settings-grid { display: grid; gap: 1rem; }
    .field { display: grid; gap: 0.4rem; margin-bottom: 0.8rem; }
    input, textarea { width: 100%; padding: 0.8rem 1rem; border-radius: 12px; border: 1px solid var(--border); font: inherit; }
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
        <a href="dashboard.php">📊 داشبۆرد</a>
        <a href="manage.php?section=services">🛠 خزمەتگوزارییەکان</a>
        <a href="manage.php?section=projects">🧩 پڕۆژەکان</a>
        <a href="manage.php?section=videos">🎬 ڤیدیۆکان</a>
        <a class="active" href="settings.php">⚙️ ڕێکخستنەکان</a>
        <a href="logout.php">🚪 دەرچوون</a>
      </nav>
    </aside>

    <main class="admin-main">
      <div class="admin-card">
        <h1>ڕێکخستنەکانی سایت</h1>
        <p>لەم بەشەدا دەتوانیت زانیارییە سەرەکییەکانی سایت بگۆڕیت.</p>
        <div class="settings-grid" style="margin-top:1rem;">
          <div class="field"><label>ژمارەی مۆبایل</label><input type="text" value="+964 770 000 0000" /></div>
          <div class="field"><label>ئیمەیل</label><input type="text" value="info@amirtech.dev" /></div>
          <div class="field"><label>شوێن</label><input type="text" value="سەیدسادق / سلێمانییە" /></div>
          <div class="field"><label>وەسفی سەرەکی</label><textarea>ئەمیر تەکنەلۆجی بۆ دروستکردنی سیستەمی بەڕێوەبردنی بیزنەس بۆ کەسایەتییە ناوخۆییەکان.</textarea></div>
          <button class="btn btn-primary" type="button">پاشەکەوتکردن</button>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
