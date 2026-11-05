<?php
require_once __DIR__ . '/../includes/auth.php';
$_SESSION = [];
session_destroy();
redirect(url('admin/index.php'));