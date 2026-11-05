<?php
require __DIR__ . '/data.php';
session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

function flash($m) { $_SESSION['flash'] = $m; }
function back() { header('Location: admin.php'); exit; }
function clean_name($s) { return trim(preg_replace('/\s+/u', ' ', (string)$s)); }
function find_cat($cats, $name) {
    foreach ($cats as $i => $c) if (mb_strtolower($c) === mb_strtolower($name)) return $i;
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['action'] ?? '';

    if ($a === 'login') {
        if (hash_equals(ADMIN_PASSWORD, (string)($_POST['password'] ?? ''))) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
        } else {
            flash('Password salah.');
        }
        back();
    }

    if (empty($_SESSION['admin']) || !hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Akses ditolak.');
    }

    if ($a === 'logout') { session_destroy(); header('Location: admin.php'); exit; }

    $d = load_data();
    $cats = $d['categories'];

    if ($a === 'add_cat') {
        $n = clean_name($_POST['name'] ?? '');
        if ($n === '' || mb_strlen($n) > 30) flash('Nama kategori harus 1 sampai 30 karakter.');
        elseif (find_cat($cats, $n) !== false) flash('Kategori "' . $n . '" sudah ada.');
        else { $d['categories'][] = $n; flash('Kategori "' . $n . '" ditambahkan.'); }
    }
    elseif ($a === 'rename_cat') {
        $old = (string)($_POST['old'] ?? '');
        $n = clean_name($_POST['name'] ?? '');
        $i = find_cat($cats, $old);
        $dup = find_cat($cats, $n);
        if ($i === false) flash('Kategori tidak ditemukan.');
        elseif ($n === '' || mb_strlen($n) > 30) flash('Nama kategori harus 1 sampai 30 karakter.');
        elseif ($dup !== false && $dup !== $i) flash('Kategori "' . $n . '" sudah ada.');
        else {
            $d['categories'][$i] = $n;
            foreach ($d['games'] as &$g) if ($g['cat'] === $cats[$i]) $g['cat'] = $n;
            unset($g);
            flash('Kategori diubah menjadi "' . $n . '".');
        }
    }
    elseif ($a === 'delete_cat') {
        $i = find_cat($cats, (string)($_POST['name'] ?? ''));
        if ($i === false) flash('Kategori tidak ditemukan.');
        else {
            $name = $cats[$i];
            foreach ($d['games'] as &$g) if ($g['cat'] === $name) $g['cat'] = '';
            unset($g);
            array_splice($d['categories'], $i, 1);
            flash('Kategori "' . $name . '" dihapus. Game di dalamnya menjadi tanpa kategori.');
        }
    }
    elseif ($a === 'move_cat') {
        $i = find_cat($cats, (string)($_POST['name'] ?? ''));
        $j = $i === false ? false : $i + (($_POST['dir'] ?? '') === 'up' ? -1 : 1);
        if ($i !== false && $j >= 0 && $j < count($cats)) {
            [$d['categories'][$i], $d['categories'][$j]] = [$d['categories'][$j], $d['categories'][$i]];
            flash('Urutan diperbarui.');
        }
    }
    elseif ($a === 'set_cat') {
        $file = (string)($_POST['file'] ?? '');
        $c = (string)($_POST['cat'] ?? '');
        if ($c !== '' && find_cat($cats, $c) === false) flash('Kategori tidak valid.');
        else {
            foreach ($d['games'] as &$g) if ($g['file'] === $file) { $g['cat'] = $c; flash('Kategori "' . $g['title'] . '" diperbarui.'); }
            unset($g);
        }
    }

    if (!save_data($d)) flash('Gagal menyimpan. Pastikan folder data/ bisa ditulis oleh server.');
    back();
}

$msg = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$admin = !empty($_SESSION['admin']);
$d = $admin ? load_data() : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex">
<title>Kelola Kategori - Game Hub</title>
<style>
:root{--bg:#002b50;--side:#001d38;--panel:#0a3b69;--fg:#fff;--muted:#8fb1d3;--accent:#00b8ff;--yellow:#ffd23f;--red:#ff5c6c;box-sizing:border-box;color-scheme:dark}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--fg);font-family:"Nunito","Trebuchet MS",system-ui,-apple-system,"Segoe UI",sans-serif;line-height:1.4;padding-top:env(safe-area-inset-top,0px);padding-bottom:env(safe-area-inset-bottom,0px)}
.top{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;background:var(--side)}
.logo{font-weight:900;font-size:24px;letter-spacing:-1px;color:var(--fg);text-decoration:none}.logo b{color:var(--accent)}
.top nav{display:flex;gap:10px;align-items:center}
.top a.lnk{color:var(--muted);text-decoration:none;font-weight:800}.top a.lnk:hover{color:var(--fg)}
main{max-width:760px;margin:0 auto;padding:20px 16px 48px}
h1{font-size:26px;margin:0 0 16px}
h2{font-size:18px;margin:0 0 12px}
.card{background:var(--side);border-radius:18px;padding:18px;margin-bottom:18px}
.msg{background:var(--yellow);color:#4a3200;font-weight:800;border-radius:12px;padding:10px 14px;margin-bottom:16px}
.hint{color:var(--muted);font-size:14px;margin:0 0 12px}
input[type=text],input[type=password],select{padding:10px 14px;border:0;border-radius:10px;background:var(--panel);color:var(--fg);font:inherit;font-weight:700;min-width:0;outline:2px solid transparent}
input:focus,select:focus{outline-color:var(--accent)}
.btn{padding:9px 16px;border:0;border-radius:10px;background:var(--accent);color:#00243f;font:inherit;font-weight:900;cursor:pointer}
.btn.sec{background:var(--panel);color:var(--fg)}
.btn.del{background:transparent;color:var(--red);box-shadow:inset 0 0 0 2px var(--red)}
.btn:focus-visible{outline:3px solid var(--yellow);outline-offset:2px}
.btn.sm{padding:7px 11px}
.row{display:flex;flex-wrap:wrap;align-items:center;gap:8px}
.add{display:flex;gap:8px}.add input{flex:1}
.cat{display:flex;flex-wrap:wrap;align-items:center;gap:8px;padding:10px 0;border-top:1px solid #ffffff18}
.cat:first-of-type{border-top:0}
.cat form{display:flex;gap:6px;align-items:center;margin:0}
.cat .ren{flex:1;min-width:180px}.cat .ren input{flex:1}
.count{color:var(--muted);font-size:13px;font-weight:800;min-width:60px}
.game{display:flex;align-items:center;gap:10px;padding:10px 0;border-top:1px solid #ffffff18}
.game:first-of-type{border-top:0}
.game .ic{font-size:26px;width:36px;text-align:center}
.game .nm{flex:1;min-width:0;font-weight:800}
.game .nm small{display:block;color:var(--muted);font-weight:600;font-size:12px}
.game form{margin:0}
.warn{color:var(--yellow);font-size:13px;font-weight:700}
@media (max-width:560px){.cat .ren{min-width:100%}}
</style>
</head>
<body>
<header class="top">
  <a class="logo" href="index.php">game<b>hub</b></a>
  <nav>
    <a class="lnk" href="index.php">← Beranda</a>
    <?php if ($admin): ?>
      <form method="post" style="margin:0"><input type="hidden" name="action" value="logout"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><button class="btn sec sm">Keluar</button></form>
    <?php endif; ?>
  </nav>
</header>

<main>
<h1>Kelola kategori</h1>
<?php if ($msg): ?><div class="msg" role="status"><?= e($msg) ?></div><?php endif; ?>

<?php if (!$admin): ?>
  <div class="card">
    <h2>Masuk</h2>
    <form method="post" class="add">
      <input type="hidden" name="action" value="login">
      <input type="password" name="password" placeholder="Password admin" autocomplete="current-password" required autofocus>
      <button class="btn">Masuk</button>
    </form>
    <?php if (ADMIN_PASSWORD === 'ganti-password-ini'): ?>
      <p class="warn">Password masih bawaan. Ubah konstanta ADMIN_PASSWORD di data.php.</p>
    <?php endif; ?>
  </div>

<?php else:
  $cats = $d['categories']; $games = $d['games'];
  $counts = [];
  foreach ($games as $g) $counts[$g['cat']] = ($counts[$g['cat']] ?? 0) + 1;
?>
  <div class="card">
    <h2>Tambah kategori</h2>
    <form method="post" class="add">
      <input type="hidden" name="action" value="add_cat"><input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="text" name="name" maxlength="30" placeholder="Nama kategori, misalnya Balapan" required>
      <button class="btn">Tambah</button>
    </form>
  </div>

  <div class="card">
    <h2>Daftar kategori</h2>
    <p class="hint">Urutan di sini sama dengan urutan pills di beranda.</p>
    <?php if (!$cats): ?><p class="hint">Belum ada kategori.</p><?php endif; ?>
    <?php foreach ($cats as $i => $c): ?>
      <div class="cat">
        <form method="post" class="ren">
          <input type="hidden" name="action" value="rename_cat"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="old" value="<?= e($c) ?>">
          <input type="text" name="name" value="<?= e($c) ?>" maxlength="30" required aria-label="Nama kategori">
          <button class="btn sec sm">Simpan</button>
        </form>
        <span class="count"><?= (int)($counts[$c] ?? 0) ?> game</span>
        <form method="post">
          <input type="hidden" name="action" value="move_cat"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="name" value="<?= e($c) ?>">
          <button class="btn sec sm" name="dir" value="up" aria-label="Naikkan" <?= $i === 0 ? 'disabled' : '' ?>>▲</button>
          <button class="btn sec sm" name="dir" value="down" aria-label="Turunkan" <?= $i === count($cats) - 1 ? 'disabled' : '' ?>>▼</button>
        </form>
        <form method="post" onsubmit="return confirm('Hapus kategori <?= e(addslashes($c)) ?>? Game di dalamnya menjadi tanpa kategori.')">
          <input type="hidden" name="action" value="delete_cat"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="name" value="<?= e($c) ?>">
          <button class="btn del sm">Hapus</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <h2>Kategori tiap game</h2>
    <p class="hint">Pilih kategori, perubahan langsung tersimpan.</p>
    <?php foreach ($games as $g): ?>
      <div class="game">
        <span class="ic"><?= e($g['icon']) ?></span>
        <span class="nm"><?= e($g['title']) ?><small><?= e($g['file']) ?><?= game_ready($g) ? '' : ' · belum ada filenya' ?></small></span>
        <form method="post">
          <input type="hidden" name="action" value="set_cat"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="file" value="<?= e($g['file']) ?>">
          <select name="cat" onchange="this.form.submit()" aria-label="Kategori <?= e($g['title']) ?>">
            <option value="" <?= $g['cat'] === '' ? 'selected' : '' ?>>Tanpa kategori</option>
            <?php foreach ($cats as $c): ?>
              <option value="<?= e($c) ?>" <?= $g['cat'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
          </select>
          <noscript><button class="btn sm">Simpan</button></noscript>
        </form>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
</main>
</body>
</html>
