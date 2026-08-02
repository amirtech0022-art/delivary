<?php
session_start();
require_once __DIR__ . '/../includes/security.php';
requireAdmin();

$logoMessage = '';
$logoError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'logo') {
    if (!verifyCsrf()) {
        $logoError = 'داواکارییەکە دروست نییە. تکایە دووبارە هەوڵ بدەوە.';
    } elseif (($_POST['remove_logo'] ?? '') === '1') {
        setSetting('site_logo', '');
        $logoMessage = 'لۆگۆکە سڕایەوە و گەڕایەوە بۆ لۆگۆی بنەڕەت.';
    } else {
        $stored = saveUploadedImage($_FILES['logo_file'] ?? [], 'logo_', $uploadErr);
        if ($stored !== null) {
            setSetting('site_logo', $stored);
            $logoMessage = 'لۆگۆکە بە سەرکەوتوویی نوێکرایەوە.';
        } else {
            $logoError = $uploadErr ?? 'هەڵە ڕوویدا لە ئەپلۆدکردن.';
        }
    }
}

$currentLogo = getSetting('site_logo', '');

$pwMessage = '';
$pwError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'password') {
    if (!verifyCsrf()) {
        $pwError = 'داواکارییەکە دروست نییە. تکایە دووبارە هەوڵ بدەوە.';
    } else {
        $current = (string)($_POST['current_password'] ?? '');
        $new     = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');
        $user    = $_SESSION['admin_user'] ?? 'admin';

        if (!verifyAdminLogin($user, $current)) {
            $pwError = 'تێپەڕەوشەی ئێستا هەڵەیە.';
        } elseif (strlen($new) < 8) {
            $pwError = 'تێپەڕەوشەی نوێ دەبێت لانیکەم ٨ پیت بێت.';
        } elseif ($new !== $confirm) {
            $pwError = 'تێپەڕەوشەی نوێ و دووبارەکردنەوەکەی وەک یەک نین.';
        } else {
            updateAdminPassword($user, $new);
            $pwMessage = 'تێپەڕەوشەکە بە سەرکەوتوویی گۆڕدرا.';
        }
    }
}

$siteMessage = '';
$siteError = '';

if (($_GET['saved'] ?? '') === 'site') {
    $siteMessage = 'ڕێکخستنەکان بە سەرکەوتوویی پاشەکەوت کران.';
}

$defaultHeroDesc = 'لە ئەمیر تەکنەلۆجی، ئێمە POS، ERP، تەرازووی زیرەک، ماڵپەڕ و ئەپی مۆبایل بۆ کۆمپانیا و بازاڕەکانی ناوخۆیی کوردستان دەدۆزینەوە.';
$sitePhone = getSetting('site_phone', '0770 540 1561');
$siteLocation = getSetting('site_location', 'سەیدسادق / سلێمانییە');
$siteHeroDesc = getSetting('site_hero_description', $defaultHeroDesc);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'site') {
    if (!verifyCsrf()) {
        $siteError = 'داواکارییەکە دروست نییە. تکایە پەڕەکە نوێ بکەرەوە و دووبارە هەوڵ بدەوە.';
    } else {
        $sitePhone = trim($_POST['site_phone'] ?? '');
        $siteLocation = trim($_POST['site_location'] ?? '');
        $siteHeroDesc = trim($_POST['site_hero_description'] ?? '');

        if ($sitePhone === '' || $siteLocation === '') {
            $siteError = 'تکایە هەموو خانە پێویستەکان پڕ بکەوە.';
        } elseif (
            !setSetting('site_phone', $sitePhone)
            || !setSetting('site_location', $siteLocation)
            || !setSetting('site_hero_description', $siteHeroDesc)
        ) {
            $siteError = 'هەڵە ڕوویدا لە پاشەکەوتکردن. تکایە دووبارە هەوڵ بدەوە.';
        } else {
            header('Location: settings.php?saved=site#site-settings');
            exit;
        }
    }
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
    #site-settings { scroll-margin-top: 1rem; }
    .settings-save-btn { width: 100%; margin-top: 0.3rem; }
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
        <a href="manage.php?section=packages">📦 پاکێجەکان</a>
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
          <?= csrfField() ?>
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

      <div class="admin-card" style="margin-bottom:1.2rem;">
        <h1>گۆڕینی تێپەڕەوشەی ئەدمین</h1>
        <p>بۆ ئاسایشی زیاتر، تێپەڕەوشەی بنەڕەت بگۆڕە.</p>

        <?php if ($pwMessage): ?><div class="alert success" style="margin-top:1rem;"><?= htmlspecialchars($pwMessage, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($pwError): ?><div class="alert error" style="margin-top:1rem;"><?= htmlspecialchars($pwError, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

        <form method="post" style="margin-top:1rem;">
          <?= csrfField() ?>
          <input type="hidden" name="form" value="password" />
          <div class="field"><label>تێپەڕەوشەی ئێستا</label><input type="password" name="current_password" autocomplete="current-password" required /></div>
          <div class="field"><label>تێپەڕەوشەی نوێ (لانیکەم ٨ پیت)</label><input type="password" name="new_password" autocomplete="new-password" required /></div>
          <div class="field"><label>دووبارەکردنەوەی تێپەڕەوشەی نوێ</label><input type="password" name="confirm_password" autocomplete="new-password" required /></div>
          <button class="btn btn-primary" type="submit">گۆڕینی تێپەڕەوشە</button>
        </form>
      </div>

      <div class="admin-card" id="site-settings">
        <h1>ڕێکخستنەکانی سایت</h1>
        <p>لەم بەشەدا دەتوانیت زانیارییە سەرەکییەکانی سایت بگۆڕیت.</p>

        <?php if ($siteMessage): ?><div class="alert success" style="margin-top:1rem;"><?= htmlspecialchars($siteMessage, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($siteError): ?><div class="alert error" style="margin-top:1rem;"><?= htmlspecialchars($siteError, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

        <form method="post" action="settings.php#site-settings" style="margin-top:1rem;">
          <?= csrfField() ?>
          <input type="hidden" name="form" value="site" />
          <div class="settings-grid">
            <div class="field"><label for="site_phone">ژمارەی مۆبایل</label><input id="site_phone" class="phone-number-input" dir="ltr" type="text" name="site_phone" value="<?= htmlspecialchars($sitePhone, ENT_QUOTES, 'UTF-8') ?>" placeholder="0770 540 1561" required /></div>
            <div class="field"><label for="site_location">شوێن</label><input id="site_location" type="text" name="site_location" value="<?= htmlspecialchars($siteLocation, ENT_QUOTES, 'UTF-8') ?>" required /></div>
            <div class="field"><label for="site_hero_description">وەسفی سەرەکی</label><textarea id="site_hero_description" name="site_hero_description" rows="4"><?= htmlspecialchars($siteHeroDesc, ENT_QUOTES, 'UTF-8') ?></textarea></div>
            <button class="btn btn-primary settings-save-btn" type="submit">پاشەکەوتکردن</button>
          </div>
        </form>
      </div>
    </main>
  </div>
  <?php if ($siteMessage || $siteError): ?>
  <script>
    document.getElementById('site-settings')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  </script>
  <?php endif; ?>
</body>
</html>
