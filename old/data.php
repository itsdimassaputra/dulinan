<?php
// Konfigurasi dan fungsi bantu bersama untuk index.php dan admin.php.

// GANTI password ini sebelum diunggah ke hosting.
const ADMIN_PASSWORD = 'ganti-password-ini';
const DATA_FILE = __DIR__ . '/data/games.json';

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function default_data() {
    return [
        'categories' => ['Arcade', 'Klasik', 'Puzzle'],
        'games' => [
            ['title'=>'Sky Hopper',  'file'=>'skyhopper.php',     'icon'=>'🐦','cat'=>'Arcade','bg'=>'linear-gradient(160deg,#79d0d6,#9bd66b)','big'=>true],
            ['title'=>'Game Klasik', 'file'=>'classic-games.php', 'icon'=>'🕹️','cat'=>'Klasik','bg'=>'linear-gradient(160deg,#ffd27a,#ff7a6b)','big'=>true],
            ['title'=>'Tetris',      'file'=>'tetris.php',        'icon'=>'🧱','cat'=>'Puzzle','bg'=>'linear-gradient(160deg,#a58bff,#4f5bff)'],
            ['title'=>'2048',        'file'=>'2048.php',          'icon'=>'🔢','cat'=>'Puzzle','bg'=>'linear-gradient(160deg,#ffb347,#ff6a3d)'],
            ['title'=>'Minesweeper', 'file'=>'minesweeper.php',   'icon'=>'💣','cat'=>'Puzzle','bg'=>'linear-gradient(160deg,#6ee7b7,#2b8a6e)'],
            ['title'=>'Flappy Race', 'file'=>'race.php',          'icon'=>'🏎️','cat'=>'Arcade','bg'=>'linear-gradient(160deg,#ff8ab3,#c13b7a)'],
        ],
    ];
}

function save_data($d) {
    $dir = dirname(DATA_FILE);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $ht = $dir . '/.htaccess';
    if (!is_file($ht)) {
        file_put_contents($ht, "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
    }
    return file_put_contents(DATA_FILE, json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX) !== false;
}

function load_data() {
    if (!is_file(DATA_FILE)) save_data(default_data());
    $d = json_decode((string)@file_get_contents(DATA_FILE), true);
    if (!is_array($d) || !isset($d['categories'], $d['games'])) $d = default_data();
    return $d;
}

function game_ready($g) { return is_file(__DIR__ . '/' . basename($g['file'])); }
