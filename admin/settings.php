<?php
session_start();
if (!($_SESSION['admin_logged_in'] ?? false)) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../includes/db.php';

$logoMessage = '';
$logoError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'logo') {
    if (!empty($_FILES['logo_file']['name']) && ($_FILES['logo_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $uploadsDir = __DIR__ . '/../assets/uploads/';
        if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
        $tmp  = $_FILES['logo_file']['tmp_name'];
        $orig = basename($_FILES['logo_file']['name']);
        $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        if (!in_array($ext, $allowed, true)) {
            $logoError = 'جۆری فایل ڕێگەپێدراو نییە. تەنها وێنە (jpg, png, gif, webp, svg).';
        } else {
            try {
                $random = bin2hex(random_bytes(4));
            } catch (Exception $e) {
                $random = uniqid();
            }
            $newName = 'logo_' . time() . '_' . $random . '.' . $ext;
            if (move_uploaded_file($tmp, $uploadsDir . $newName)) {
                setSetting('site_logo', 'assets/uploads/' . $newName);
                $logoMessage = 'لۆگۆکە بە سەرکەوتوویی نوێکرایەوە.';
            } else {
                $logoError = 'هەڵە ڕوویدا لە پاشەکەوتکردنی فایلەکە.';
            }
        }
    } elseif (($_POST['remove_logo'] ?? '') === '1') {
        setSetting('site_logo', '');
        $logoMessage = 'لۆگۆکە سڕایەوە و گەڕایەوە بۆ لۆگۆی بنەڕەت.';
    } else {
        $logoError = 'هیچ فایلێک هەڵنەبژێردرا.';
    }
}

$currentLogo = getSetting('site_logo', '');
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
      <div class="admin-card" style="margin-bottom:1.2rem;">
        <h1>لۆگۆی سایت</h1>
        <p>لێرەوە دەتوانیت لۆگۆی سەرەکی سایت بگۆڕیت. لۆگۆکە لە سەرپەڕی سایتدا دەردەکەوێت.</p>

        <?php if ($logoMessage): ?><div class="alert success" style="margin-top:1rem;"><?= htmlspecialchars($logoMessage, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($logoError): ?><div class="alert error" style="margin-top:1rem;"><?= htmlspecialchars($logoError, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

        <div style="display:flex; align-items:center; gap:1rem; margin:1rem 0;">
          <span style="color:var(--muted); font-weight:700;">لۆگۆی ئێستا:</span>
          <?php if (!empty($currentLogo)): ?>
            <img src="../<?= htmlspecialchars($currentLogo, ENT_QUOTES, 'UTF-8') ?>" alt="لۆگۆ" style="height:56px; width:56px; object-fit:contain; border-radius:14px; background:#fff; border:1px solid var(--border); padding:4px;" />
          <?php else: ?>
            <span style="color:var(--muted);">لۆگۆی بنەڕەت (SVG)</span>
          <?php endif; ?>
        </div>

        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="form" value="logo" />
          <div class="field">
            <label>هەڵبژاردنی لۆگۆی نوێ</label>
            <input type="file" name="logo_file" accept="image/*" />
          </div>
          <div style="display:flex; gap:0.6rem; flex-wrap:wrap;">
            <button class="btn btn-primary" type="submit">پاشەکەوتکردنی لۆگۆ</button>
            <?php if (!empty($currentLogo)): ?>
              <button class="btn btn-secondary" type="submit" name="remove_logo" value="1" onclick="return confirm('دڵنیایت لۆگۆکە بسڕیتەوە؟')">سڕینەوەی لۆگۆ</button>
            <?php endif; ?>
          </div>
        </form>
      </div>

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
