<?php
/**
 * DULINAN — Utility Functions
 * Simple version — pakai .php di URL
 */

define('DATA_DIR', __DIR__ . '/../data');
define('ROOT_DIR', dirname(__DIR__));

/**
 * BASE_URL detection
 * Project di subfolder (misal /dulinan) atau root
 */
if (!defined('BASE_URL')) {
    $rootFs  = str_replace('\\', '/', ROOT_DIR);
    $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));

    if ($docRoot !== '' && strpos($rootFs, $docRoot) === 0) {
        $base = substr($rootFs, strlen($docRoot));
    } else {
        $base = '';
    }
    define('BASE_URL', rtrim($base, '/'));
}

/**
 * Helper URL — TANPA strip .php
 * $path = 'admin/index.php' → '/dulinan/admin/index.php'
 */
function url(string $path = ''): string {
    return BASE_URL . '/' . ltrim($path, '/');
}

function logo_url(): string {
    return url('assets/img/logo.png');
}

function favicon_url(): string {
    return url('assets/img/favicon.png');
}

function read_json(string $file): array {
    $path = DATA_DIR . '/' . $file;
    if (!file_exists($path)) return [];
    $content = file_get_contents($path);
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

function write_json(string $file, array $data): bool {
    $path = DATA_DIR . '/' . $file;
    if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0777, true);
    return file_put_contents(
        $path,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    ) !== false;
}

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function get_games(bool $onlyActive = true): array {
    $games = read_json('games.json');
    if ($onlyActive) {
        $games = array_filter($games, fn($g) => !empty($g['active']));
    }
    return array_values($games);
}

function get_categories(): array {
    return read_json('categories.json');
}

function get_game_by_slug(string $slug): ?array {
    foreach (read_json('games.json') as $g) {
        if ($g['slug'] === $slug) return $g;
    }
    return null;
}

function game_url(array $game): string {
    return url('games/' . $game['slug'] . '.php');
}

function increment_plays(string $slug): void {
    $games = read_json('games.json');
    foreach ($games as &$g) {
        if ($g['slug'] === $slug) {
            $g['plays'] = ($g['plays'] ?? 0) + 1;
            break;
        }
    }
    unset($g);
    write_json('games.json', $games);
}