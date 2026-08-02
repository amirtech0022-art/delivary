<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/video_embed.php';

$conn = getDbConnection();

recordVisit();

$siteLogo = getSetting('site_logo', '');
$sitePhone = getSetting('site_phone', '0770 540 1561');
$siteLocation = getSetting('site_location', 'سەیدسادق / سلێمانییە');
$siteHeroDesc = getSetting('site_hero_description', 'لە ئەمیر تەکنەلۆجی، ئێمە POS، ERP، تەرازووی زیرەک، ماڵپەڕ و ئەپی مۆبایل بۆ کۆمپانیا و بازاڕەکانی ناوخۆیی کوردستان دەدۆزینەوە.');
$sitePhoneDigits = phoneDigitsForLinks($sitePhone);
$siteLocationShort = preg_replace('#\s*/\s*#', ' و ', $siteLocation);
$metaDescription = 'ئەمیر تەکنەلۆجی کۆمپانیای گەشەپێدانی نەرمەکاڵا لە ' . $siteLocation . 'یە، POS، ERP، تەرازووی زیرەک و ئەپ و ماڵپەڕ دەکات.';

$services = [];
$result = mysqli_query($conn, 'SELECT title, description FROM services ORDER BY id');
while ($row = mysqli_fetch_assoc($result)) {
    $services[] = $row;
}
if (!$services) {
    $services = [
        ['title' => 'سیستەمی کاشێر و فرۆشتن', 'description' => 'کاشێر، ڕاپۆرتە ڕۆژانە و کۆنترۆڵی مەخزەن.'],
        ['title' => 'سیستەمی ژمێریاری و ERP', 'description' => 'بەڕێوەبردنی مەخزەن و فاکتۆرەکان لە یەک جۆرە.'],
        ['title' => 'گەیاندنی تەرازووی زیرەک', 'description' => 'پەیوەندیی تەرازو و هەڵبژاردنی داتایەکی ڕەنگی.'],
    ];
}

$projects = [];
$result = mysqli_query($conn, 'SELECT title, description, category, image_url FROM projects ORDER BY id');
while ($row = mysqli_fetch_assoc($result)) {
    $projects[] = $row;
}
if (!$projects) {
    $projects = [
        ['title' => 'POS بۆ کۆشکی خۆر', 'description' => 'سیستەمی فرۆشتن و کەشێر بۆ کەسایەتییەکانی ناوخۆ.', 'category' => 'pos', 'image_url' => 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"%3E%3Crect width="1200" height="800" fill="%23f7fbff"/%3E%3Crect x="80" y="80" width="1040" height="640" rx="32" fill="%23ffffff" stroke="%230044ff" stroke-opacity="0.16"/%3E%3Crect x="140" y="160" width="280" height="160" rx="24" fill="%23d9eeff"/%3E%3Crect x="460" y="160" width="600" height="360" rx="24" fill="%23f7fbff" stroke="%2300a2ff" stroke-opacity="0.25"/%3E%3Crect x="140" y="360" width="280" height="240" rx="24" fill="%23d9eeff"/%3E%3Ctext x="600" y="405" text-anchor="middle" fill="%230a1b33" font-size="38" font-family="Arial" font-weight="700"%3Eپڕۆژەی POS%3C/text%3E%3Ctext x="600" y="455" text-anchor="middle" fill="%235f728f" font-size="24" font-family="Arial"%3Eئەم وێنەیەیە جێگای وێنەی ڕاستەقینە%3C/text%3E%3C/svg%3E'],
        ['title' => 'ERP بۆ کۆمپانیای کەرەسەیی', 'description' => 'بەڕێوەبردنی مەخزەن و فاکتۆرەکان لە یەک سیستەم.', 'category' => 'erp', 'image_url' => 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"%3E%3Crect width="1200" height="800" fill="%23f7fbff"/%3E%3Crect x="100" y="90" width="1000" height="620" rx="32" fill="%23ffffff" stroke="%230044ff" stroke-opacity="0.16"/%3E%3Crect x="150" y="140" width="260" height="180" rx="24" fill="%23d9eeff"/%3E%3Crect x="450" y="140" width="600" height="360" rx="24" fill="%23f7fbff" stroke="%2300a2ff" stroke-opacity="0.25"/%3E%3Crect x="150" y="360" width="260" height="220" rx="24" fill="%23d9eeff"/%3E%3Ctext x="600" y="395" text-anchor="middle" fill="%230a1b33" font-size="38" font-family="Arial" font-weight="700"%3Eپڕۆژەی ERP%3C/text%3E%3Ctext x="600" y="445" text-anchor="middle" fill="%235f728f" font-size="24" font-family="Arial"%3Eئەم وێنەیەیە جێگای ڕوونکەرەوەی وێنەی ڕاستەقینە%3C/text%3E%3C/svg%3E'],
        ['title' => 'ماڵپەڕی کەشتیارەکان', 'description' => 'ماڵپەڕی سادە و ڕەنگاوڕەنگ بۆ کۆمپانیا و بازاڕ.', 'category' => 'web', 'image_url' => 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"%3E%3Crect width="1200" height="800" fill="%23f7fbff"/%3E%3Crect x="140" y="120" width="920" height="560" rx="32" fill="%23ffffff" stroke="%230044ff" stroke-opacity="0.16"/%3E%3Crect x="190" y="170" width="820" height="80" rx="18" fill="%23d9eeff"/%3E%3Crect x="190" y="280" width="250" height="240" rx="18" fill="%23d9eeff"/%3E%3Crect x="470" y="280" width="540" height="240" rx="18" fill="%23f7fbff" stroke="%2300a2ff" stroke-opacity="0.25"/%3E%3Ctext x="600" y="405" text-anchor="middle" fill="%230a1b33" font-size="38" font-family="Arial" font-weight="700"%3Eماڵپەڕ%3C/text%3E%3Ctext x="600" y="455" text-anchor="middle" fill="%235f728f" font-size="24" font-family="Arial"%3Eئەم وێنەیەیە جێگای وێنەی ڕاستەقینە%3C/text%3E%3C/svg%3E'],
    ];
}

$videos = [];
$result = mysqli_query($conn, 'SELECT title, description, embed_url FROM videos ORDER BY id');
while ($row = mysqli_fetch_assoc($result)) {
    $row['embed_url'] = normalizeVideoEmbedUrl($row['embed_url'] ?? '');
    $videos[] = $row;
}
if (!$videos) {
    $videos = [
        ['title' => 'پێشانگای POS', 'description' => 'پیشاندانی سەرەکییەکانی کەشێر و فرۆشتن.', 'embed_url' => 'https://www.youtube.com/embed/ScMzIvxBSi4?rel=0'],
        ['title' => 'پێشانگای ERP', 'description' => 'چۆنیەتییەکی گەیشتن بە زانیاری و فۆرمی کار.', 'embed_url' => 'https://www.youtube.com/embed/aqz-KE-bpKQ?rel=0'],
        ['title' => 'پێشانگای ئەپ', 'description' => 'پێشانگای ئەپ و ماڵپەڕی بیزنەس.', 'embed_url' => 'https://www.youtube.com/embed/2Vv-BfVoq4g?rel=0'],
    ];
}

ensurePackagesTable();
$packages = [];
$result = mysqli_query($conn, 'SELECT title, description, price, features FROM packages ORDER BY id');
while ($row = mysqli_fetch_assoc($result)) {
    $packages[] = $row;
}

?>
<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ئەمیر تەکنەلۆجی | نەرمەکاڵای بەڕێوەبردنی بیزنەس</title>
  <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:title" content="ئەمیر تەکنەلۆجی | نەرمەکاڵای بەڕێوەبردنی بیزنەس" />
  <meta property="og:description" content="سیستەمی بەڕێوەبردنی بیزنەس بۆ کەسایەتییە ناوخۆییەکان لە کوردستان." />
  <meta property="og:type" content="website" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Sans+Arabic:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/style.css" />
</head>
<body>
  <header class="site-header">
    <div class="container nav">
      <a class="brand" href="#hero">
        <span class="brand-mark<?= !empty($siteLogo) ? ' brand-mark--logo' : '' ?>">
          <?php if (!empty($siteLogo)): ?>
            <img src="<?= htmlspecialchars($siteLogo, ENT_QUOTES, 'UTF-8') ?>" alt="ئەمیر تەکنەلۆجی" />
          <?php else: ?>
            <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <rect x="6" y="6" width="52" height="52" rx="16" fill="url(#logoGrad)" />
              <path d="M20 46V24H26.5C29.69 24 32 26.31 32 29.5C32 32.69 29.69 35 26.5 35H20" stroke="white" stroke-width="4.6" stroke-linecap="round" />
              <path d="M20 35H31.5L39 46" stroke="white" stroke-width="4.6" stroke-linecap="round" stroke-linejoin="round" />
              <defs><linearGradient id="logoGrad" x1="8" y1="8" x2="56" y2="56" gradientUnits="userSpaceOnUse"><stop stop-color="#00CFFF" /><stop offset="1" stop-color="#0044FF" /></linearGradient></defs>
            </svg>
          <?php endif; ?>
        </span>
        <span>ئەمیر تەکنەلۆجی</span>
      </a>
      <button class="menu-toggle" aria-label="مێنیو" aria-expanded="false">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 7H20M4 12H20M4 17H20" stroke="#0044FF" stroke-width="2" stroke-linecap="round" /></svg>
      </button>
      <button id="themeToggle" type="button" class="btn" aria-pressed="false" title="تۆگل دارک / لایت" onclick="(function(){var el=document.documentElement;var isDark=el.getAttribute('data-theme')==='dark';if(isDark){el.removeAttribute('data-theme');try{localStorage.setItem('theme','light')}catch(e){};var ico=document.getElementById('themeIcon');if(ico)ico.textContent='☀️';this.setAttribute('aria-pressed','false');}else{el.setAttribute('data-theme','dark');try{localStorage.setItem('theme','dark')}catch(e){};var ico2=document.getElementById('themeIcon');if(ico2)ico2.textContent='🌙';this.setAttribute('aria-pressed','true');}})()">
        <span id="themeIcon">☀️</span>
      </button>
      <ul class="nav-links" id="navLinks">
        <li><a href="#hero" class="active">سەرەتا</a></li>
        <li><a href="#services">خزمەتگوزارییەکان</a></li>
        <li><a href="#packages">پاکێجەکان</a></li>
        <li><a href="#portfolio">کارەکانمان</a></li>
        <li><a href="#videos">ڤیدیۆ</a></li>
        <li><a href="#about">دەربارەمان</a></li>
        <li><a href="#contact">پەیوەندی</a></li>
      </ul>
      <a class="btn btn-primary nav-action" href="#contact">پەیوەندیمان پێوە بکە</a>
    </div>
  </header>

  <main>
    <section class="hero" id="hero">
      <div class="container hero-grid">
        <div class="hero-copy reveal">
          <h1>سیستەمی بەڕێوەبردنی بیزنەس بۆ کەسایەتییە ناوخۆییەکان</h1>
          <p><?= htmlspecialchars($siteHeroDesc, ENT_QUOTES, 'UTF-8') ?></p>
          <div class="hero-actions">
            <a class="btn btn-primary" href="#portfolio">کارەکانمان ببینە</a>
            <a class="btn btn-secondary" href="#contact">پەیوەندی</a>
          </div>
          <div class="hero-highlights">
            <span>POS و کەشێر</span><span>ERP و ژمێریاری</span><span>ماڵپەڕ و ئەپ</span>
          </div>
        </div>
        <div class="hero-card reveal">
          <div class="hero-stack">
            <div class="hero-stack-item"><strong>بەڕێوەبردنی ڕوون</strong><span>لەسەر سەرچاوەیەکیەکی سادە و ژمارەی کارەکانەوە.</span></div>
            <div class="hero-stack-item"><strong>پێوەندییەکی ڕاستەوخۆ</strong><span>بۆ کەسایەتییە ناوخۆییەکانی <?= htmlspecialchars($siteLocationShort, ENT_QUOTES, 'UTF-8') ?>.</span></div>
            <div class="hero-stack-item"><strong>پشتگیریی هەموو کات</strong><span>لە کاتی هەڵگرتن تا چاککردنەوە و گەشەپێدانی دواتر.</span></div>
          </div>
        </div>
      </div>
    </section>

    <section class="section" id="services">
      <div class="container">
        <div class="section-title reveal">
          <span class="eyebrow">خزمەتگوزارییەکان</span>
          <h2>پەیوەندییەکانی سەربەخۆی بیزنەسەکان</h2>
          <p>چێژ و سیستمەکانی بێکێشەی بۆ کەسایەتییەکان و کۆمپانیاکان.</p>
        </div>
        <div class="services-grid">
          <?php foreach ($services as $service): ?>
            <article class="service-card reveal">
              <div class="service-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 7H19M7 4V20M17 4V20M6 10.5H18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
              </div>
              <h3><?= htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p><?= htmlspecialchars($service['description'], ENT_QUOTES, 'UTF-8') ?></p>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="section" id="packages">
      <div class="container">
        <div class="section-title reveal">
          <span class="eyebrow">پاکێجەکان</span>
          <h2>پلانی گونجاو بۆ بیزنەسەکەت</h2>
          <p>پاکێجە جیاوازەکان بەپێی قەبارە و پێداویستی کۆمپانیاکەت.</p>
        </div>
        <?php if ($packages): ?>
        <div class="packages-grid">
          <?php foreach ($packages as $package): ?>
            <?php
              $featureLines = array_filter(array_map('trim', preg_split('/\R/u', $package['features'] ?? '')));
            ?>
            <article class="package-card reveal">
              <?php if (!empty($package['price'])): ?>
                <div class="package-price"><?= htmlspecialchars($package['price'], ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
              <h3><?= htmlspecialchars($package['title'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p><?= htmlspecialchars($package['description'], ENT_QUOTES, 'UTF-8') ?></p>
              <?php if ($featureLines): ?>
                <ul class="package-features">
                  <?php foreach ($featureLines as $feature): ?>
                    <li><?= htmlspecialchars($feature, ENT_QUOTES, 'UTF-8') ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
              <a class="btn btn-primary package-cta" href="#contact">داواکردن</a>
            </article>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="packages-empty reveal">هێشتا پاکێجێک زیاد نەکراوە. لە ئەدمینەوە زیاد بکە.</p>
        <?php endif; ?>
      </div>
    </section>

    <section class="section" id="portfolio">
      <div class="container">
        <div class="section-title reveal">
          <span class="eyebrow">کارەکانمان</span>
          <h2>نمونەیەک لە پڕۆژەکانی دڵخواز</h2>
          <p>پڕۆژەکانی ماڵپەڕ، ئەپ و ERP کە لەسەر پێویستی بازرگانی و شوێنی دۆزراون.</p>
        </div>
        <div class="portfolio-filter reveal" role="tablist" aria-label="فلتەری کارەکان">
          <button class="filter-button active" data-filter="all">هەموو</button>
          <button class="filter-button" data-filter="pos">POS</button>
          <button class="filter-button" data-filter="erp">ERP</button>
          <button class="filter-button" data-filter="web">ماڵپەڕ</button>
          <button class="filter-button" data-filter="app">ئەپ</button>
        </div>
        <div class="portfolio-grid">
          <?php foreach ($projects as $project): ?>
            <article class="portfolio-card reveal" data-category="<?= htmlspecialchars($project['category'], ENT_QUOTES, 'UTF-8') ?>">
              <img src="<?= htmlspecialchars($project['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8') ?>" />
              <div class="content">
                <h3><?= htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($project['description'], ENT_QUOTES, 'UTF-8') ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="section" id="videos">
      <div class="container">
        <div class="section-title reveal">
          <span class="eyebrow">ڤیدیۆکان</span>
          <h2>پێشانگای پانی و ڕوونکردنەوەی سیستەمەکان</h2>
          <p>ڤیدیۆکان لەسەر چۆنەتییەکی کارکردنی سیستەمەکان و پێشنیارەکانی پێویست.</p>
        </div>
        <div class="video-grid">
          <?php foreach ($videos as $video): ?>
            <article class="video-card reveal">
              <div class="video-frame">
                <iframe src="<?= htmlspecialchars($video['embed_url'], ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
              </div>
              <div class="content">
                <h3><?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($video['description'], ENT_QUOTES, 'UTF-8') ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="section" id="about">
      <div class="container">
        <div class="section-title reveal">
          <span class="eyebrow">دەربارەمان</span>
          <h2>ئێمە لە ناوەڕاستی کوردستان و بەرەو نمونەیەکی دڵخواز</h2>
          <p>ئەمیر تەکنەلۆجی بۆ بیزنەسەکانی ناوخۆیی ڕێکخستنی پێویستەکانی سیستەمەکەیانە و پێشکەش دەکات.</p>
        </div>
        <div class="about-grid">
          <article class="about-card reveal">
            <h3>ئامانجمان</h3>
            <p>ئێمە ئامانجمان وەکوو دروستکردنی سیستەمەکانی بەڕێوەبردن بۆ بازرگانی و کەسایەتییە ناوخۆییەکان، بە شێوەیەکی سادە و کەسایەتی.</p>
            <p>لە <?= htmlspecialchars($siteLocation, ENT_QUOTES, 'UTF-8') ?>، ئێمە هەموو پێویستەکانی بیزنەسەکانمان لەبەر چاوەڕوانییە هەورەیەکی گەشەکردنەوە.</p>
          </article>
          <article class="about-card reveal">
            <h3>ئاستەکانی سەرکەوتن</h3>
            <div class="stats">
              <div class="stat"><div class="number" data-target="120">0</div><div class="label">پڕۆژەی کۆتایی هات</div></div>
              <div class="stat"><div class="number" data-target="85">0</div><div class="label">کەسەی سەرکەوتوو</div></div>
              <div class="stat"><div class="number" data-target="8">0</div><div class="label">ساڵی ئەزموون</div></div>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section class="section contact-section" id="contact">
      <div class="container">
        <div class="section-title reveal contact-section__title">
          <span class="eyebrow">پەیوەندی</span>
          <h2>بۆ پێوەندی و پێشنیارەکانی بیزنەسەکەت</h2>
          <p>بۆ پەیوەندی و پێشنیارەکانی بیزنەسەکەت، لە ڕێگەی ئەم زانیاریانەوە پەیوەندیمان پێوە بکە.</p>
        </div>
        <div class="contact-grid contact-grid--single">
          <article class="contact-card reveal">
            <div class="contact-card__accent" aria-hidden="true"></div>
            <div class="contact-card__inner">
              <div class="contact-details">
                <div class="contact-item">
                  <div class="icon"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 4H10L12 8L10 10C11.2 12.2 12.8 13.8 15 15L17 13L21 15V18C21 18.55 20.55 19 20 19C13.4 19 8 13.6 8 7C8 6.45 8.45 6 9 6H7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg></div>
                  <div><strong>مۆبایل</strong><a class="phone-number" dir="ltr" href="tel:<?= htmlspecialchars($sitePhoneDigits, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($sitePhone, ENT_QUOTES, 'UTF-8') ?></a></div>
                </div>
                <div class="contact-item">
                  <div class="icon"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21C12 21 6 14.3 6 9.8C6 6.4 8.7 4 12 4C15.3 4 18 6.4 18 9.8C18 14.3 12 21 12 21Z" stroke="currentColor" stroke-width="1.8" /><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8" /></svg></div>
                  <div><strong>شوێن</strong><span><?= htmlspecialchars($siteLocation, ENT_QUOTES, 'UTF-8') ?></span></div>
                </div>
              </div>
              <div class="contact-card__divider" aria-hidden="true"></div>
              <div class="social-links">
                <a class="social-link social-link--facebook" href="https://facebook.com" target="_blank" rel="noreferrer" aria-label="فەیسبوک">
                  <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M13.5 22v-8.2h2.8l0.4-3.2h-3.2V8.9c0-0.9 0.3-1.6 1.6-1.6h1.7V4.1c-0.3 0-1.3-0.1-2.5-0.1-2.5 0-4.2 1.5-4.2 4.3v2.4H7.8v3.2h2.5V22h3.2z"/></svg>
                </a>
                <a class="social-link social-link--instagram" href="https://instagram.com" target="_blank" rel="noreferrer" aria-label="ئینستاگرام">
                  <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="5" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8"/><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor"/></svg>
                </a>
                <a class="social-link social-link--whatsapp" href="https://wa.me/<?= htmlspecialchars($sitePhoneDigits, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noreferrer" aria-label="واتساپ">
                  <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75 0.46 3.45 1.33 4.95L2 22l5.3-1.39c1.44 0.79 3.06 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91C21.95 6.45 17.5 2 12.04 2zm0 18.08c-1.48 0-2.93-0.4-4.2-1.15l-0.3-0.18-3.14 0.82 0.84-3.06-0.2-0.31c-0.82-1.28-1.25-2.76-1.25-4.29 0-4.54 3.7-8.24 8.25-8.24s8.25 3.7 8.25 8.24-3.7 8.24-8.25 8.24zm4.52-6.16c-0.25-0.12-1.47-0.72-1.7-0.81-0.23-0.08-0.4-0.12-0.57 0.12-0.17 0.25-0.66 0.81-0.81 0.97-0.15 0.17-0.3 0.19-0.55 0.06-0.25-0.12-1.05-0.39-2-1.23-0.74-0.66-1.24-1.47-1.39-1.72-0.15-0.25-0.02-0.38 0.11-0.51 0.11-0.11 0.25-0.3 0.38-0.45 0.13-0.15 0.17-0.25 0.25-0.42 0.08-0.17 0.04-0.32-0.02-0.45-0.06-0.12-0.57-1.38-0.78-1.89-0.21-0.5-0.42-0.43-0.57-0.44l-0.49-0.01c-0.17 0-0.45 0.06-0.68 0.32-0.23 0.25-0.89 0.87-0.89 2.13 0 1.25 0.91 2.46 1.04 2.63 0.13 0.17 1.79 2.73 4.34 3.83 0.61 0.26 1.08 0.42 1.45 0.54 0.61 0.19 1.16 0.16 1.6 0.1 0.49-0.07 1.47-0.6 1.68-1.18 0.21-0.58 0.21-1.08 0.15-1.18-0.06-0.1-0.23-0.16-0.48-0.28z"/></svg>
                </a>
                <a class="social-link social-link--tiktok" href="https://tiktok.com" target="_blank" rel="noreferrer" aria-label="TikTok">
                  <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M16.6 5.82c0.96-0.66 1.64-1.72 1.64-2.82h-3.28v11.76c0 1.48-1.2 2.68-2.68 2.68-1.48 0-2.68-1.2-2.68-2.68s1.2-2.68 2.68-2.68c0.28 0 0.54 0.04 0.8 0.12V10.1c-0.26-0.04-0.54-0.06-0.8-0.06-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5V9.01c1.06 0.76 2.36 1.21 3.76 1.21V6.94c-0.9 0-1.72-0.3-2.4-0.82l0.04-0.3z"/></svg>
                </a>
              </div>
              <a class="whatsapp-btn" href="https://wa.me/<?= htmlspecialchars($sitePhoneDigits, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noreferrer">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12.03 5C7.93 5 4.39 8.54 4.39 12.64C4.39 14.18 4.84 15.66 5.7 16.94L4.5 19.5L7.15 18.34C8.47 19.12 10.2 19.59 12.03 19.59C16.14 19.59 19.68 16.05 19.68 11.95C19.68 8.54 16.14 5 12.03 5Z" fill="currentColor"/></svg>
                پەیوەندی لە واتساپ
              </a>
            </div>
          </article>
        </div>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container footer-simple">
      <a class="footer-brand brand" href="#hero">
        <span class="brand-mark<?= !empty($siteLogo) ? ' brand-mark--logo' : '' ?>">
          <?php if (!empty($siteLogo)): ?>
            <img src="<?= htmlspecialchars($siteLogo, ENT_QUOTES, 'UTF-8') ?>" alt="ئەمیر تەکنەلۆجی" />
          <?php else: ?>
            <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <rect x="6" y="6" width="52" height="52" rx="16" fill="url(#logoGradFooter)" />
              <path d="M20 46V24H26.5C29.69 24 32 26.31 32 29.5C32 32.69 29.69 35 26.5 35H20" stroke="white" stroke-width="4.6" stroke-linecap="round" />
              <path d="M20 35H31.5L39 46" stroke="white" stroke-width="4.6" stroke-linecap="round" stroke-linejoin="round" />
              <defs><linearGradient id="logoGradFooter" x1="8" y1="8" x2="56" y2="56" gradientUnits="userSpaceOnUse"><stop stop-color="#00CFFF" /><stop offset="1" stop-color="#0044FF" /></linearGradient></defs>
            </svg>
          <?php endif; ?>
        </span>
        <span class="footer-name">ئەمیر تەکنەلۆجی</span>
      </a>
      <p class="footer-year">2025</p>
    </div>
  </footer>

  <a class="floating-wa" href="https://wa.me/<?= htmlspecialchars($sitePhoneDigits, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noreferrer" aria-label="واتساپ"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.03 5C7.93 5 4.39 8.54 4.39 12.64C4.39 14.18 4.84 15.66 5.7 16.94L4.5 19.5L7.15 18.34C8.47 19.12 10.2 19.59 12.03 19.59C16.14 19.59 19.68 16.05 19.68 11.95C19.68 8.54 16.14 5 12.03 5ZM16.13 14.86C15.91 15.04 14.98 15.59 14.79 15.66C14.6 15.73 14.43 15.76 14.27 15.66C14.12 15.56 13.5 15.25 12.84 14.67C12.28 14.16 11.84 13.53 11.66 13.35C11.48 13.17 11.31 13.17 11.13 13.35C10.95 13.53 10.56 13.91 10.4 14.09C10.24 14.27 10.07 14.29 9.89 14.11C9.71 13.93 9.2 13.54 8.67 12.88C8.22 12.33 7.95 11.7 7.81 11.52C7.67 11.34 7.8 11.19 7.97 11.01C8.16 10.82 8.35 10.57 8.42 10.4C8.49 10.23 8.52 10.06 8.42 9.89C8.32 9.72 7.8 8.8 7.63 8.42C7.46 8.04 7.29 8.08 7.11 8.08L6.62 8.11C6.43 8.11 6.2 8.19 6.02 8.37C5.84 8.55 5.5 9.05 5.5 10C5.5 10.95 6.03 11.88 6.37 12.35C6.71 12.82 7.72 13.93 8.7 14.87C9.68 15.81 10.74 16.59 11.21 16.95C11.68 17.31 12.2 17.47 12.78 17.42C13.12 17.38 13.59 16.87 13.79 16.41C13.99 15.95 14.38 15.83 14.56 15.83C14.74 15.83 14.92 15.86 15.11 15.95C15.3 16.04 16.35 16.68 16.53 16.76C16.71 16.84 16.89 16.87 16.95 16.76C17.01 16.66 17.01 15.86 16.76 15.01C16.51 14.16 16.36 14.12 16.13 14.86Z" fill="currentColor"/></svg></a>

  <div class="lightbox" id="lightbox" aria-hidden="true"><button class="lightbox-close" id="lightboxClose" aria-label="داخستن">✕</button><img id="lightboxImage" src="" alt="پڕۆژە" /></div>
  <script src="assets/app.js"></script>
</body>
</html>
