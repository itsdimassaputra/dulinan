<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';

function is_admin_logged_in(): bool {
    return !empty($_SESSION['admin_logged_in']);
}

function require_admin(): void {
    if (!is_admin_logged_in()) {
        redirect(url('admin/index.php'));
    }
}

function verify_admin(string $username, string $password): bool {
    $admins = read_json('admin.json');
    foreach ($admins as $admin) {
        if ($admin['username'] === $username
            && password_verify($password, $admin['password'])) {
            return true;
        }
    }
    return false;
}