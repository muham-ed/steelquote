<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'steelquote');

// Auto detect BASE_URL for XAMPP (/steelquote/) or PHP Dev Server (/)
if (!defined('BASE_URL')) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/steelquote') !== false) {
        define('BASE_URL', '/steelquote/');
    } else {
        define('BASE_URL', '/');
    }
}

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('خطأ في الاتصال بقاعدة البيانات: ' . htmlspecialchars($e->getMessage()));
}

define('SHOP_NAME', 'ورشة الحدادة والتصنيع الحديثة');
define('SHOP_PHONE', '01000000000');
define('SHOP_ADDRESS', 'القاهرة - المنطقة الصناعية');
define('SHOP_EMAIL', 'info@steelquote-workshop.com');
