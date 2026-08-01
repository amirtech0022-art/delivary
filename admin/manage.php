<?php
session_start();
require_once __DIR__ . '/../includes/security.php';
requireAdmin();
$conn = getDbConnection();

$section = $_GET['section'] ?? 'services';
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

// ---- Delete (POST + CSRF protected) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
    if (!verifyCsrf()) { http_response_code(400); exit('داواکاری نادروست.'); }
    $deleteSection = $_POST['section'] ?? 'services';
    $deleteId = (int)($_POST['id'] ?? 0);
    $allowedTables = ['services' => 'services', 'projects' => 'projects', 'videos' => 'videos'];
    if (isset($allowedTables[$deleteSection]) && $deleteId > 0) {
        $table = $allowedTables[$deleteSection];
        $stmt = $conn->prepare("DELETE FROM `$table` WHERE id = ?");
        $stmt->bind_param('i', $deleteId);
        $stmt->execute();
    }
    header('Location: manage.php?section=' . urlencode($deleteSection));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) { http_response_code(400); exit('داواکاری نادروست. تکایە پەڕەکە نوێ بکەرەوە.'); }
    $section = $_POST['section'] ?? $section;
    $submittedAction = $_POST['action'] ?? 'add';
    $submittedId = (int)($_POST['id'] ?? 0);

    if ($section === 'services') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($submittedAction === 'edit' && $submittedId) {
            $stmt = $conn->prepare('UPDATE services SET title=?, description=? WHERE id=?');
            $stmt->bind_param('ssi', $title, $description, $submittedId);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare('INSERT INTO services (title, description) VALUES (?, ?)');
            $stmt->bind_param('ss', $title, $description);
            $stmt->execute();
        }
    } elseif ($section === 'projects') {
      $title = trim($_POST['title'] ?? '');
      $description = trim($_POST['description'] ?? '');
      $category = trim($_POST['category'] ?? '');
      $image_url = trim($_POST['image_url'] ?? '');

      // Handle uploaded image file (optional). If provided and valid, prefer it over text URL.
      if (!empty($_FILES['image_file']['name'])) {
        $stored = saveUploadedImage($_FILES['image_file'], 'proj_', $uploadErr);
        if ($stored !== null) {
          $image_url = $stored;
        }
      }

      if ($submittedAction === 'edit' && $submittedId) {
        $stmt = $conn->prepare('UPDATE projects SET title=?, description=?, category=?, image_url=? WHERE id=?');
        $stmt->bind_param('ssssi', $title, $description, $category, $image_url, $submittedId);
        $stmt->execute();
      } else {
        $stmt = $conn->prepare('INSERT INTO projects (title, description, category, image_url) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $title, $description, $category, $image_url);
        $stmt->execute();
      }
    } elseif ($section === 'videos') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $embed_url = trim($_POST['embed_url'] ?? '');
        if ($submittedAction === 'edit' && $submittedId) {
            $stmt = $conn->prepare('UPDATE videos SET title=?, description=?, embed_url=? WHERE id=?');
            $stmt->bind_param('sssi', $title, $description, $embed_url, $submittedId);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare('INSERT INTO videos (title, description, embed_url) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $title, $description, $embed_url);
            $stmt->execute();
        }
    }
    header('Location: manage.php?section=' . $section);
    exit;
}

$record = null;
if ($action === 'edit' && $id) {
    if ($section === 'services') {
        $result = mysqli_query($conn, "SELECT * FROM services WHERE id=$id");
        $record = mysqli_fetch_assoc($result);
    } elseif ($section === 'projects') {
        $result = mysqli_query($conn, "SELECT * FROM projects WHERE id=$id");
        $record = mysqli_fetch_assoc($result);
    } elseif ($section === 'videos') {
        $result = mysqli_query($conn, "SELECT * FROM videos WHERE id=$id");
        $record = mysqli_fetch_assoc($result);
    }
}

$items = [];
if ($section === 'services') {
    $result = mysqli_query($conn, 'SELECT * FROM services ORDER BY id DESC');
    while ($row = mysqli_fetch_assoc($result)) $items[] = $row;
} elseif ($section === 'projects') {
    $result = mysqli_query($conn, 'SELECT * FROM projects ORDER BY id DESC');
    while ($row = mysqli_fetch_assoc($result)) $items[] = $row;
} elseif ($section === 'videos') {
    $result = mysqli_query($conn, 'SELECT * FROM videos ORDER BY id DESC');
    while ($row = mysqli_fetch_assoc($result)) $items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ئەدمین | بەڕێوەبردنی ناوەڕۆک</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Sans+Arabic:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/style.css" />
  <style>
    body { background: var(--bg); }
    .admin-shell { width: min(1200px, calc(100% - 2rem)); margin: 2rem auto; background: white; border-radius: 24px; padding: 1.5rem; box-shadow: var(--shadow); }
    .topbar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem; }
    .tabs { display: flex; flex-wrap: wrap; gap: 0.6rem; margin-bottom: 1rem; }
    .tabs a { padding: 0.65rem 0.9rem; border-radius: 999px; background: var(--pale-blue); color: var(--primary-deep); font-weight: 700; }
    .tabs a.active { background: linear-gradient(135deg, var(--accent-cyan), var(--primary-deep)); color: white; }
    form { display: grid; gap: 0.8rem; margin-bottom: 1.5rem; }
    input, textarea, select { width: 100%; padding: 0.8rem 1rem; border-radius: 12px; border: 1px solid var(--border); font: inherit; }
    textarea { min-height: 100px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border-bottom: 1px solid var(--border); padding: 0.75rem; text-align: right; vertical-align: top; }
    th { background: var(--pale-blue); }
    .actions a { color: var(--primary-deep); font-weight: 700; margin-left: 0.6rem; }
  </style>
</head>
<body>
  <div class="admin-shell">
    <div class="topbar">
      <h1>بەڕێوەبردنی ناوەڕۆکەکان</h1>
      <a class="btn btn-secondary" href="dashboard.php">گەڕانەوە بۆ داشبۆرد</a>
    </div>

    <div class="tabs">
      <a href="manage.php?section=services" class="<?= $section === 'services' ? 'active' : '' ?>">خزمەتگوزارییەکان</a>
      <a href="manage.php?section=projects" class="<?= $section === 'projects' ? 'active' : '' ?>">پڕۆژەکان</a>
      <a href="manage.php?section=videos" class="<?= $section === 'videos' ? 'active' : '' ?>">ڤیدیۆکان</a>
    </div>

    <form method="post" enctype="multipart/form-data">
      <?= csrfField() ?>
      <input type="hidden" name="section" value="<?= htmlspecialchars($section, ENT_QUOTES, 'UTF-8') ?>" />
      <input type="hidden" name="action" value="<?= $action === 'edit' ? 'edit' : 'add' ?>" />
      <input type="hidden" name="id" value="<?= (int)($id ?? 0) ?>" />
      <?php if ($section === 'projects'): ?>
        <select name="category">
          <option value="pos" <?= ($record['category'] ?? '') === 'pos' ? 'selected' : '' ?>>POS</option>
          <option value="erp" <?= ($record['category'] ?? '') === 'erp' ? 'selected' : '' ?>>ERP</option>
          <option value="web" <?= ($record['category'] ?? '') === 'web' ? 'selected' : '' ?>>ماڵپەڕ</option>
          <option value="app" <?= ($record['category'] ?? '') === 'app' ? 'selected' : '' ?>>ئەپ</option>
        </select>
      <?php endif; ?>
      <input type="text" name="title" placeholder="ناونیشان" value="<?= htmlspecialchars($record['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required />
      <textarea name="description" placeholder="وەسف" required><?= htmlspecialchars($record['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
      <?php if ($section === 'projects'): ?>
        <input type="file" name="image_file" accept="image/*" />
        <input type="text" name="image_url" placeholder="URLی وێنە یاخود پەیوەندی (ئامادە بۆ بیرکاری)" value="<?= htmlspecialchars($record['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
      <?php endif; ?>
      <?php if ($section === 'videos'): ?><input type="text" name="embed_url" placeholder="URLی ڤیدیۆ" value="<?= htmlspecialchars($record['embed_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required /><?php endif; ?>
      <button class="btn btn-primary" type="submit"><?= $action === 'edit' ? 'نوێکردنەوە' : 'زیادکردن' ?></button>
    </form>

    <table>
      <thead>
        <tr>
          <th>ناونیشان</th>
          <th>وەسف</th>
          <?php if ($section === 'projects'): ?><th>URL / وێنە</th><?php endif; ?>
          <?php if ($section === 'videos'): ?><th>URL</th><?php endif; ?>
          <th>کردار</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?></td>
            <?php if ($section === 'projects'): ?>
              <td>
                <?php $projectUrl = trim((string)($item['image_url'] ?? '')); ?>
                <?php if ($projectUrl !== ''): ?>
                  <?php $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $projectUrl) || strpos($projectUrl, 'data:image/') === 0 || strpos($projectUrl, 'assets/uploads/') === 0; ?>
                  <?php if ($isImage): ?>
                    <img src="<?= htmlspecialchars($projectUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" style="height:60px;max-width:160px;object-fit:cover;border-radius:8px;display:block;margin-bottom:6px;" />
                    <a href="<?= htmlspecialchars($projectUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">کردنەوە / بەردەوام</a>
                  <?php else: ?>
                    <a href="<?= htmlspecialchars($projectUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($projectUrl, ENT_QUOTES, 'UTF-8') ?></a>
                  <?php endif; ?>
                <?php else: ?>
                  -
                <?php endif; ?>
              </td>
            <?php endif; ?>
            <?php if ($section === 'videos'): ?><td><?= htmlspecialchars($item['embed_url'], ENT_QUOTES, 'UTF-8') ?></td><?php endif; ?>
            <td class="actions">
              <a href="manage.php?section=<?= htmlspecialchars($section, ENT_QUOTES, 'UTF-8') ?>&action=edit&id=<?= (int)$item['id'] ?>">دەستکاریکردن</a>
              <form method="post" style="display:inline; margin:0;" onsubmit="return confirm('دڵنیایت؟')">
                <?= csrfField() ?>
                <input type="hidden" name="do" value="delete" />
                <input type="hidden" name="section" value="<?= htmlspecialchars($section, ENT_QUOTES, 'UTF-8') ?>" />
                <input type="hidden" name="id" value="<?= (int)$item['id'] ?>" />
                <button type="submit" style="background:none;border:0;color:#c62828;font:inherit;font-weight:700;cursor:pointer;padding:0;">سڕینەوە</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
