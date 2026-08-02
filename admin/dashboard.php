<?php
session_start();
require_once __DIR__ . '/../includes/security.php';
requireAdmin();
$conn = getDbConnection();
ensureVisitsTable();

// Helper: run a COUNT(*) query and return the integer result.
function countVisits($sql, $types = '', ...$params)
{
    $conn = getDbConnection();
    $stmt = $conn->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)($row['c'] ?? 0);
}

$todayCount = countVisits('SELECT COUNT(*) AS c FROM visits WHERE visit_date = CURDATE()');
$monthCount = countVisits('SELECT COUNT(*) AS c FROM visits WHERE YEAR(visit_date) = YEAR(CURDATE()) AND MONTH(visit_date) = MONTH(CURDATE())');
$yearCount  = countVisits('SELECT COUNT(*) AS c FROM visits WHERE YEAR(visit_date) = YEAR(CURDATE())');
$totalCount = countVisits('SELECT COUNT(*) AS c FROM visits');

// Date range filter (from / to). Defaults: first day of this month .. today.
$validDate = static function ($value) {
    $d = DateTime::createFromFormat('Y-m-d', (string)$value);
    return ($d && $d->format('Y-m-d') === $value) ? $value : null;
};
$from = $validDate($_GET['from'] ?? '') ?? date('Y-m-01');
$to   = $validDate($_GET['to'] ?? '')   ?? date('Y-m-d');
if ($from > $to) { [$from, $to] = [$to, $from]; }

$rangeCount = countVisits('SELECT COUNT(*) AS c FROM visits WHERE visit_date BETWEEN ? AND ?', 'ss', $from, $to);

// Daily breakdown within the selected range.
$daily = [];
$stmt = $conn->prepare('SELECT visit_date, COUNT(*) AS c FROM visits WHERE visit_date BETWEEN ? AND ? GROUP BY visit_date ORDER BY visit_date DESC');
$stmt->bind_param('ss', $from, $to);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $daily[] = $row;
$maxDaily = 0;
foreach ($daily as $d) { if ((int)$d['c'] > $maxDaily) $maxDaily = (int)$d['c']; }
?>
<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ئەدمین | داشبۆردی ئامار</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Sans+Arabic:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="assets.css" />
  <style>
    .stat-cards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; margin: 1rem 0 1.6rem; }
    .stat-box { border-radius: 18px; padding: 1.1rem 1.2rem; background: linear-gradient(135deg, rgba(0,207,255,0.14), rgba(0,68,255,0.08)); border: 1px solid var(--border); }
    .stat-box .num { font-size: 1.9rem; font-weight: 800; color: var(--primary-deep); line-height: 1.1; }
    .stat-box .lbl { color: var(--muted); font-weight: 700; margin-top: 0.25rem; }
    .filter-row { display: flex; flex-wrap: wrap; gap: 0.8rem; align-items: end; margin: 0.5rem 0 1rem; }
    .filter-row .field { display: grid; gap: 0.3rem; }
    .filter-row label { font-weight: 700; font-size: 0.9rem; }
    .filter-row input { padding: 0.7rem 0.9rem; border-radius: 12px; border: 1px solid var(--border); font: inherit; }
    .range-result { background: var(--pale-blue); border-radius: 14px; padding: 0.9rem 1.1rem; font-weight: 700; color: var(--primary-deep); margin-bottom: 1.2rem; }
    table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
    th, td { border-bottom: 1px solid var(--border); padding: 0.8rem; text-align: right; vertical-align: middle; }
    th { background: var(--pale-blue); }
    .bar { height: 10px; border-radius: 999px; background: linear-gradient(135deg, var(--accent-cyan), var(--primary-deep)); min-width: 6px; }
    .empty { color: var(--muted); padding: 1rem 0; }
    @media (max-width: 900px) { .stat-cards { grid-template-columns: repeat(2, minmax(0,1fr)); } }
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
        <div class="topbar" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
          <h1 style="margin:0;">داشبۆردی ئاماری سەردان</h1>
          <a class="btn btn-secondary" href="logout.php">دەرچوون</a>
        </div>
        <p style="color:var(--muted); margin-bottom:0.5rem;">ژمارەی سەردانیکەرانی سایت بەپێی ماوە.</p>

        <div class="stat-cards">
          <div class="stat-box"><div class="num"><?= number_format($todayCount) ?></div><div class="lbl">ئەمڕۆ (ڕۆژانە)</div></div>
          <div class="stat-box"><div class="num"><?= number_format($monthCount) ?></div><div class="lbl">ئەم مانگە</div></div>
          <div class="stat-box"><div class="num"><?= number_format($yearCount) ?></div><div class="lbl">ئەمساڵ</div></div>
          <div class="stat-box"><div class="num"><?= number_format($totalCount) ?></div><div class="lbl">کۆی گشتی</div></div>
        </div>

        <h2 style="margin-bottom:0.4rem;">سەردانی لە بەروارێکەوە بۆ بەروارێک</h2>
        <form method="get" class="filter-row">
          <div class="field">
            <label for="from">لە بەرواری</label>
            <input type="date" id="from" name="from" value="<?= htmlspecialchars($from, ENT_QUOTES, 'UTF-8') ?>" />
          </div>
          <div class="field">
            <label for="to">بۆ بەرواری</label>
            <input type="date" id="to" name="to" value="<?= htmlspecialchars($to, ENT_QUOTES, 'UTF-8') ?>" />
          </div>
          <button class="btn btn-primary" type="submit">پیشاندان</button>
        </form>

        <div class="range-result">
          لە <?= htmlspecialchars($from, ENT_QUOTES, 'UTF-8') ?> بۆ <?= htmlspecialchars($to, ENT_QUOTES, 'UTF-8') ?>:
          <?= number_format($rangeCount) ?> سەردان
        </div>

        <h2 style="margin-bottom:0.4rem;">وردەکاری ڕۆژانە</h2>
        <?php if (!$daily): ?>
          <div class="empty">هیچ سەردانێک لەم ماوەیەدا تۆمار نەکراوە.</div>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th style="width:180px;">بەروار</th>
                <th style="width:120px;">ژمارە</th>
                <th>ڕێژە</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($daily as $d): ?>
                <?php $pct = $maxDaily > 0 ? max(4, (int)round((int)$d['c'] / $maxDaily * 100)) : 0; ?>
                <tr>
                  <td><?= htmlspecialchars($d['visit_date'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= number_format((int)$d['c']) ?></td>
                  <td><div class="bar" style="width:<?= $pct ?>%;"></div></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>
