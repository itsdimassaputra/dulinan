<?php
require_once __DIR__ . '/includes/functions.php';

$games = get_games(true);
$categories = get_categories();

$counts = ['all' => count($games)];
foreach ($categories as $cat) {
    $counts[$cat['id']] = count(array_filter($games, fn($g) => $g['category'] === $cat['id']));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dulinan — Kumpulan Game Gratis</title>
    <meta name="description" content="Mainkan game gratis langsung di browser. Tanpa download, tanpa registrasi.">
    <link rel="icon" type="image/png" href="<?= favicon_url() ?>">
    <link rel="apple-touch-icon" href="<?= logo_url() ?>">
    <script src="<?= url('assets/js/theme-init.js') ?>"></script>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a href="<?= url('index.php') ?>" class="logo">
            <img src="<?= logo_url() ?>" alt="Dulinan" class="logo-img">
            <span>Dulinan</span>
        </a>

        <div class="header-actions">
            <button id="themeToggle" class="theme-btn" title="Ganti tema" aria-label="Ganti tema">
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
                </svg>
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>

            <a href="<?= url('admin/index.php') ?>" class="gear-btn" title="Admin" aria-label="Admin">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
            </a>
        </div>
    </div>
</header>

<main>
    <section class="hero">
        <div class="hero-animated-bg" aria-hidden="true">
            <span class="blob blob-1"></span>
            <span class="blob blob-2"></span>
            <span class="blob blob-3"></span>
            <span class="blob blob-4"></span>
        </div>

        <div class="hero-content container">
            <span class="hero-badge">
                <span class="dot"></span>
                <?= count($games) ?> game siap dimainkan
            </span>
            <h1>Ayo Dulinan</h1>
            <p>Main Game Instan, Tanpa Hambatan.</p>
        </div>

        <div class="hero-fade" aria-hidden="true"></div>
    </section>

    <section class="container content-section">
        <div class="toolbar">
            <div class="search-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <input type="text" id="searchInput" placeholder="Cari game..." autocomplete="off">
            </div>
            <div class="filter-chips">
                <button class="chip active" data-filter="all">Semua (<?= $counts['all'] ?>)</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="chip" data-filter="<?= e($cat['id']) ?>">
                        <?= e($cat['name']) ?> (<?= $counts[$cat['id']] ?? 0 ?>)
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="game-grid" id="gameGrid">
            <?php if (empty($games)): ?>
                <div class="empty-state">
                    <h3>Belum ada game</h3>
                    <p>Game baru akan segera hadir.</p>
                </div>
            <?php else: ?>
                <?php foreach ($games as $game):
                    $cat = null;
                    foreach ($categories as $c) if ($c['id'] === $game['category']) { $cat = $c; break; }
                ?>
                    <a href="<?= e(game_url($game)) ?>"
                       class="game-card"
                       data-name="<?= e($game['name']) ?>"
                       data-category="<?= e($game['category']) ?>">
                        <span class="play-badge">▶ Mainkan</span>
                        <div class="game-thumb" style="background: <?= e($game['color']) ?>1a;">
                            <span class="icon"><?= e($game['icon']) ?></span>
                        </div>
                        <div class="game-info">
                            <div class="game-name"><?= e($game['name']) ?></div>
                            <div class="game-meta">
                                <?= e($cat['name'] ?? 'Umum') ?> · <?= number_format($game['plays'] ?? 0) ?> dimainkan
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
            <div class="empty-state" id="emptyState" style="display:none;">
                <h3>Tidak ditemukan</h3>
                <p>Coba kata kunci atau kategori lain.</p>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container">
        © <?= date('Y') ?> Dulinan · Dibuat dengan PHP Native &amp; JSON
    </div>
</footer>

<script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>