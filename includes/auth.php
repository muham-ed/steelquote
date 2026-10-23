<?php
require_once __DIR__ . '/functions.php';

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        set_flash('error', 'الرجاء تسجيل الدخول أولاً للوصول للنظام.');
        redirect(BASE_URL . 'login.php');
    }
}

function current_user() {
    global $pdo;
    if (!is_logged_in()) return null;

    static $user = null;
    if ($user === null) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if (!$user) {
            unset($_SESSION['user_id']);
            redirect(BASE_URL . 'login.php');
        }
    }
    return $user;
}

function current_user_id() {
    return $_SESSION['user_id'] ?? 1;
}
