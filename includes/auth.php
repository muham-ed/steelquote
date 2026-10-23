<?php
require_once __DIR__ . '/functions.php';

function is_logged_in() {
    return !empty($_SESSION['user_id']);
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

function is_admin() {
    $u = current_user();
    return $u && ($u['role'] === 'admin');
}

function require_login() {
    if (!is_logged_in()) {
        set_flash('error', 'الرجاء تسجيل الدخول أولاً للوصول للنظام.');
        redirect(BASE_URL . 'login.php');
    }

    $u = current_user();
    $current_page = basename($_SERVER['PHP_SELF']);

    // Check subscription status for non-admin users
    if ($u['role'] !== 'admin' && in_array($u['subscription_status'], ['expired', 'suspended'])) {
        if ($current_page !== 'suspended.php' && $current_page !== 'logout.php') {
            redirect(BASE_URL . 'suspended.php');
        }
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        set_flash('error', 'عذراً، هذه الصفحة مخصصة لمالك المنصة ومدير النظام فقط.');
        redirect(BASE_URL . 'index.php');
    }
}
