<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$games = read_json('games.json');
$categories = get_categories();
$message = '';
$error = '';

$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($action === 'add_game') {
        $name = trim($_POST['name'] ?? '');
        $slug = slugify($_POST['slug'] ?? $name);

        if ($name === '' || $slug === '') {
            $error = 'Nama dan slug wajib diisi.';
        } elseif (get_game_by_slug($slug)) {
            $error = 'Slug sudah dipakai game lain.';
        } elseif (!file_exists(ROOT_DIR . '/games/' . $slug . '.php')) {
            $error = 'File /games/' . $slug . '.php tidak ditemukan. Buat file game terlebih dahulu.';
        } else {
            $games[] = [
                'slug' => $slug,
                'name' => $name,
                'description' => trim($_POST['description'] ?? ''),
                'category' => $_POST['category'] ?? 'puzzle',
                'icon' => trim($_POST['icon'] ?? '🎮') ?: '🎮',
                'color' => trim($_POST['color'] ?? '#6366f1') ?: '#6366f1',
                'active' => true,
                'plays' => 0,
                'created_at' => date('Y-m-d'),
            ];
            write_json('games.json', $games);
            $message = 'Game berhasil ditambahkan.';
        }
    }

    if ($action === 'update_game') {
        $slug = $_POST['slug'] ?? '';
        foreach ($games as &$g) {
            if ($g['slug'] === $slug) {
                $g['name'] = trim($_POST['name'] ?? $g['name']);
                $g['description'] = trim($_POST['description'] ?? $g['description']);
                $g['category'] = $_POST['category'] ?? $g['category'];
                $g['icon'] = trim($_POST['icon'] ?? $g['icon']) ?: $g['icon'];
                $g['color'] = trim($_POST['color'] ?? $g['color']) ?: $g['color'];
                break;
            }
        }
        unset($g);
        write_json('games.json', $games);
        $message = 'Game berhasil diperbarui.';
    }

    if ($action === 'toggle_game') {
        $slug = $_POST['slug'] ?? '';
        foreach ($games as &$g) {
            if ($g['slug'] === $slug) {
                $g['active'] = !($g['active'] ?? false);
                break;
            }
        }
        unset($g);
        write_json('games.json', $games);
        $message = 'Status game diubah.';
    }

    if ($action === 'delete_game') {
        $slug = $_POST['slug'] ?? '';
        $games = array_values(array_filter($games, fn($g) => $g['slug'] !== $slug));
        write_json('games.json', $games);
        $message = 'Game berhasil dihapus dari daftar (file game tetap ada).';
    }

    if ($action === 'add_category') {
        $name = trim($_POST['cat_name'] ?? '');
        $id = slugify($_POST['cat_id'] ?? $name);
        if ($name === '' || $id === '') {
            $error = 'Nama kategori wajib diisi.';
        } elseif (in_array($id, array_column($categories, 'id'), true)) {
            $error = 'ID kategori sudah ada.';
        } else {
            $categories[] = ['id' => $id, 'name' => $name];
            write_json('categories.json', $categories);
            $message = 'Kategori berhasil ditambahkan.';
        }
    }

    if ($action === 'delete_category') {
        $id = $_POST['cat_id'] ?? '';
        $used = array_filter($games, fn($g) => $g['category'] === $id);
        if ($used) {
            $error = 'Kategori masih dipakai oleh ' . count($used) . ' game.';
        } else {
            $categories = array_values(array_filter($categories, fn($c) => $c['id'] !== $id));
            write_json('categories.json', $categories);
            $message = 'Kategori dihapus.';
        }
    }

    $games = read_json('games.json');
    $categories = get_categories();
}

$editGame = null;
if (isset($_GET['edit'])) {
    foreach ($games as $g) {
        if ($g['slug'] === $_GET['edit']) { $editGame = $g; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — Dulinan</title>
    <link rel="icon" type="image/png" href="<?= favicon_url() ?>">
    <link rel="apple-touch-icon" href="<?= logo_url() ?>">
    <script src="<?= url('assets/js/theme-init.js') ?>"></script>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>

<div class="admin-layout">

    <header class="admin-header">
        <div class="admin-header-inner">
            <a href="<?= url('index.php') ?>" class="logo">
                <img src="<?= logo_url() ?>" alt="Dulinan Admin" class="logo-img">
                <span>Dulinan Admin</span>
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

                <a href="<?= url('index.php') ?>" class="btn btn-ghost btn-sm">Lihat Situs</a>
                <a href="<?= url('admin/logout.php') ?>" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </header>

    <main class="admin-main">

        <?php if ($message): ?>
            <div class="alert alert-success"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="admin-tabs" id="adminTabs">
            <button class="admin-tab active" data-tab="games">Game (<?= count($games) ?>)</button>
            <button class="admin-tab" data-tab="categories">Kategori (<?= count($categories) ?>)</button>
        </div>

        <!-- =============== TAB: GAMES =============== -->
        <section id="tab-games">

            <div class="panel" style="margin-bottom:20px;">
                <div class="panel-header">
                    <div>
                        <div class="panel-title"><?= $editGame ? 'Edit Game' : 'Tambah Game Baru' ?></div>
                        <div class="panel-subtitle">
                            File game harus sudah ada di <code>/games/{slug}.php</code>
                        </div>
                    </div>
                    <?php if ($editGame): ?>
                        <a href="<?= url('admin/dashboard.php') ?>" class="btn btn-ghost btn-sm">Batal Edit</a>
                    <?php endif; ?>
                </div>

                <form method="post">
                    <input type="hidden" name="action" value="<?= $editGame ? 'update_game' : 'add_game' ?>">
                    <?php if ($editGame): ?>
                        <input type="hidden" name="slug" value="<?= e($editGame['slug']) ?>">
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nama Game *</label>
                            <input type="text" name="name" required
                                value="<?= e($editGame['name'] ?? '') ?>" placeholder="Contoh: 2048">
                        </div>
                        <div class="form-group">
                            <label>Slug File <?= $editGame ? '' : '*' ?></label>
                            <input type="text" name="slug"
                                value="<?= e($editGame['slug'] ?? '') ?>"
                                placeholder="2048 (file: games/2048.php)"
                                <?= $editGame ? 'readonly' : '' ?>>
                        </div>
                        <div class="form-group">
                            <label>Kategori</label>
                            <select name="category">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= e($cat['id']) ?>"
                                        <?= (($editGame['category'] ?? '') === $cat['id']) ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Icon (emoji)</label>
                            <input type="text" name="icon" maxlength="4"
                                value="<?= e($editGame['icon'] ?? '🎮') ?>">
                        </div>
                        <div class="form-group">
                            <label>Warna Aksen</label>
                            <input type="color" name="color"
                                value="<?= e($editGame['color'] ?? '#6366f1') ?>">
                        </div>
                        <div class="form-group full">
                            <label>Deskripsi</label>
                            <textarea name="description" rows="2"
                                placeholder="Deskripsi singkat game..."><?= e($editGame['description'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div style="margin-top:16px;">
                        <button type="submit" class="btn btn-primary" style="width:auto;">
                            <?= $editGame ? 'Simpan Perubahan' : 'Tambah Game' ?>
                        </button>
                    </div>
                </form>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">Daftar Game</div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Icon</th>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th>Dimainkan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($games)): ?>
                            <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text-secondary);">Belum ada game.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($games as $g):
                            $catName = '—';
                            foreach ($categories as $c) if ($c['id'] === $g['category']) { $catName = $c['name']; break; }
                        ?>
                            <tr>
                                <td style="font-size:22px;"><?= e($g['icon']) ?></td>
                                <td>
                                    <div style="font-weight:600;"><?= e($g['name']) ?></div>
                                    <div style="font-size:12px;color:var(--text-secondary);">
                                        games/<?= e($g['slug']) ?>.php
                                    </div>
                                </td>
                                <td><?= e($catName) ?></td>
                                <td><?= number_format($g['plays'] ?? 0) ?></td>
                                <td>
                                    <span class="badge <?= !empty($g['active']) ? 'badge-active' : 'badge-inactive' ?>">
                                        <?= !empty($g['active']) ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <a href="<?= url('admin/dashboard.php') ?>?edit=<?= urlencode($g['slug']) ?>"
                                           class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_game">
                                            <input type="hidden" name="slug" value="<?= e($g['slug']) ?>">
                                            <button type="submit" class="btn btn-ghost btn-sm">
                                                <?= !empty($g['active']) ? 'Nonaktifkan' : 'Aktifkan' ?>
                                            </button>
                                        </form>
                                        <form method="post" style="display:inline;"
                                              onsubmit="return confirm('Hapus game ini dari daftar?');">
                                            <input type="hidden" name="action" value="delete_game">
                                            <input type="hidden" name="slug" value="<?= e($g['slug']) ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- =============== TAB: CATEGORIES =============== -->
        <section id="tab-categories" style="display:none;">

            <div class="panel" style="margin-bottom:20px;">
                <div class="panel-header">
                    <div class="panel-title">Tambah Kategori</div>
                </div>
                <form method="post" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                    <input type="hidden" name="action" value="add_category">
                    <div class="form-group" style="flex:1;min-width:180px;margin-bottom:0;">
                        <label>Nama Kategori *</label>
                        <input type="text" name="cat_name" required placeholder="Contoh: Puzzle">
                    </div>
                    <div class="form-group" style="flex:1;min-width:180px;margin-bottom:0;">
                        <label>ID (opsional)</label>
                        <input type="text" name="cat_id" placeholder="puzzle">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:auto;">Tambah</button>
                </form>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">Daftar Kategori</div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Jumlah Game</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($categories as $cat):
                            $used = count(array_filter($games, fn($g) => $g['category'] === $cat['id']));
                        ?>
                            <tr>
                                <td><code style="font-size:12px;padding:2px 6px;background:var(--bg);border-radius:4px;"><?= e($cat['id']) ?></code></td>
                                <td style="font-weight:600;"><?= e($cat['name']) ?></td>
                                <td><?= $used ?></td>
                                <td>
                                    <form method="post" style="display:inline;"
                                          onsubmit="return confirm('Hapus kategori ini?');">
                                        <input type="hidden" name="action" value="delete_category">
                                        <input type="hidden" name="cat_id" value="<?= e($cat['id']) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            <?= $used > 0 ? 'disabled title="Kategori masih dipakai"' : '' ?>>
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                            <tr><td colspan="4" style="text-align:center;padding:32px;color:var(--text-secondary);">Belum ada kategori.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>
</div>

<script>
document.querySelectorAll('.admin-tab').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.admin-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const tab = btn.dataset.tab;
        document.getElementById('tab-games').style.display = tab === 'games' ? '' : 'none';
        document.getElementById('tab-categories').style.display = tab === 'categories' ? '' : 'none';
    });
});

const nameInput = document.querySelector('input[name="name"]');
const slugInput = document.querySelector('input[name="slug"]');
if (nameInput && slugInput && !slugInput.readOnly) {
    nameInput.addEventListener('input', () => {
        slugInput.value = nameInput.value.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    });
}
</script>

<script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>