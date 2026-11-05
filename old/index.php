<?php
require __DIR__ . '/data.php';
$data = load_data();
$cats = $data['categories'];
$games = $data['games'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#002b50">
<title>Game Hub - Main Game Gratis</title>
<style>
:root{--bg:#002b50;--side:#001d38;--panel:#0a3b69;--fg:#fff;--muted:#8fb1d3;--accent:#00b8ff;--yellow:#ffd23f;box-sizing:border-box;color-scheme:dark}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--fg);font-family:"Nunito","Trebuchet MS",system-ui,-apple-system,"Segoe UI",sans-serif;line-height:1.35;
padding-top:env(safe-area-inset-top,0px);padding-bottom:env(safe-area-inset-bottom,0px)}
body::before{content:"";position:fixed;inset:0 0 auto;height:env(safe-area-inset-top,0px);background:var(--side);z-index:30}
.top{position:sticky;top:env(safe-area-inset-top,0px);z-index:20;display:flex;align-items:center;gap:14px;padding:10px 16px;background:var(--side);box-shadow:0 2px 12px #0006}
.logo{font-weight:900;font-size:26px;letter-spacing:-1px;color:var(--fg);text-decoration:none;white-space:nowrap}
.logo b{color:var(--accent)}
.search{flex:1;max-width:520px;margin:0 auto;position:relative}
.search input{width:100%;padding:11px 16px 11px 42px;border:0;border-radius:99px;background:var(--panel);color:var(--fg);font:inherit;font-weight:700;outline:2px solid transparent}
.search input::placeholder{color:var(--muted)}
.search input:focus{outline-color:var(--accent)}
.search span{position:absolute;left:15px;top:50%;transform:translateY(-50%);font-size:16px}
.gear{color:var(--muted);text-decoration:none;font-size:20px;padding:6px}
.gear:hover{color:var(--fg)}
main{max-width:1200px;margin:0 auto;padding:18px 20px 40px}
/* Pills tab */
.pills{display:flex;gap:8px;overflow-x:auto;padding:2px 0 16px;scrollbar-width:none}
.pills::-webkit-scrollbar{display:none}
.pill{flex:none;display:flex;align-items:center;gap:8px;padding:9px 18px;border:0;border-radius:99px;background:var(--panel);color:var(--fg);font:inherit;font-weight:800;cursor:pointer;transition:background .15s,transform .15s}
.pill:hover{background:#12508c}
.pill.on{background:var(--accent);color:#00243f}
.pill i{font-style:normal;font-size:12px;opacity:.7}
.pill:focus-visible{outline:3px solid var(--yellow);outline-offset:2px}
h2{margin:0 0 14px;font-size:22px;font-weight:900}
/* Grid */
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));grid-auto-rows:140px;grid-auto-flow:dense;gap:14px}
.tile{position:relative;display:block;border-radius:20px;overflow:hidden;color:#fff;text-decoration:none;isolation:isolate;transition:transform .18s cubic-bezier(.3,1.5,.5,1),box-shadow .18s}
.tile.big{grid-column:span 2;grid-row:span 2}
a.tile:hover,a.tile:focus-visible{transform:scale(1.06);box-shadow:0 0 0 4px var(--accent),0 12px 30px #0008;z-index:5;outline:none}
.tile .ico{position:absolute;inset:0;display:grid;place-items:center;font-size:52px;filter:drop-shadow(0 4px 0 #0003);transition:transform .25s}
.tile.big .ico{font-size:110px}
a.tile:hover .ico{transform:scale(1.15) rotate(-4deg)}
.tile .cap{position:absolute;left:0;right:0;bottom:0;padding:26px 12px 10px;background:linear-gradient(transparent,#000b);font-weight:900;font-size:15px;transform:translateY(60%);opacity:0;transition:.18s}
.tile.big .cap{font-size:20px;padding:40px 16px 14px}
a.tile:hover .cap,a.tile:focus-visible .cap{transform:none;opacity:1}
.cap small{display:block;font-weight:700;color:var(--yellow);font-size:11px;letter-spacing:.5px}
.soon{filter:saturate(.3) brightness(.7)}
.soon::after{content:"SEGERA HADIR";position:absolute;top:10px;left:10px;font-size:10px;font-weight:900;letter-spacing:1px;background:#000a;padding:4px 8px;border-radius:99px}
.empty{display:none;color:var(--muted);font-weight:700;padding:30px 0}
@media (hover:none){.tile .cap{transform:none;opacity:1}}
@media (max-width:760px){
  main{padding:12px 12px 32px}
  .grid{grid-template-columns:repeat(auto-fill,minmax(110px,1fr));grid-auto-rows:110px;gap:10px}
  .tile .ico{font-size:40px}.tile.big .ico{font-size:80px}
  .logo{font-size:22px}
}
@media (prefers-reduced-motion:reduce){.tile,.tile .ico,.tile .cap,.pill{transition:none}}
</style>
</head>
<body>
<header class="top">
  <a class="logo" href="index.php">game<b>hub</b></a>
  <label class="search"><span>🔍</span><input id="q" type="search" placeholder="Cari game" autocomplete="off"></label>
  <a class="gear" href="admin.php" title="Kelola kategori" aria-label="Kelola kategori">⚙️</a>
</header>

<main>
  <nav class="pills" id="pills" aria-label="Kategori game">
    <button class="pill on" data-cat="">Semua <i><?= count($games) ?></i></button>
    <?php foreach ($cats as $c):
        $n = count(array_filter($games, fn($g) => $g['cat'] === $c)); ?>
      <button class="pill" data-cat="<?= e($c) ?>"><?= e($c) ?> <i><?= $n ?></i></button>
    <?php endforeach; ?>
  </nav>

  <h2 id="heading">Semua game</h2>
  <div class="grid" id="grid">
  <?php foreach ($games as $g):
      $ok = game_ready($g);
      $cls = 'tile' . (!empty($g['big']) ? ' big' : '') . ($ok ? '' : ' soon');
      $attr = 'data-title="' . e(strtolower($g['title'])) . '" data-cat="' . e($g['cat']) . '" style="background:' . e($g['bg']) . '"';
  ?>
    <?php if ($ok): ?><a class="<?= $cls ?>" href="<?= e($g['file']) ?>" <?= $attr ?>>
    <?php else: ?><div class="<?= $cls ?>" <?= $attr ?> aria-disabled="true"><?php endif; ?>
      <span class="ico"><?= e($g['icon']) ?></span>
      <span class="cap"><small><?= e(strtoupper($g['cat'] ?: 'Game')) ?></small><?= e($g['title']) ?></span>
    <?= $ok ? '</a>' : '</div>' ?>
  <?php endforeach; ?>
  </div>
  <p class="empty" id="empty">Tidak ada game yang cocok.</p>
</main>

<script>
const q = document.getElementById("q"), tiles = [...document.querySelectorAll(".tile")];
let cat = "";
function filter() {
  const t = q.value.trim().toLowerCase();
  let n = 0;
  tiles.forEach(el => {
    const ok = (!cat || el.dataset.cat === cat) && el.dataset.title.includes(t);
    el.style.display = ok ? "" : "none";
    if (ok) n++;
  });
  document.getElementById("empty").style.display = n ? "none" : "block";
  document.getElementById("heading").textContent = cat ? "Game " + cat : "Semua game";
}
document.querySelectorAll(".pill").forEach(b => b.onclick = () => {
  document.querySelectorAll(".pill").forEach(x => x.classList.remove("on"));
  b.classList.add("on"); cat = b.dataset.cat; filter();
});
q.addEventListener("input", filter);
</script>
</body>
</html>
