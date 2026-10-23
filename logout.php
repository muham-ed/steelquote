<?php
require_once 'includes/functions.php';

session_unset();
session_destroy();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

set_flash('success', 'تم تسجيل الخروج بنجاح.');
redirect('login.php');
?>
