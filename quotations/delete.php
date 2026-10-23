<?php
require_once '../includes/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM quotations WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, current_user_id()]);
        set_flash('success', 'تم حذف عرض السعر بنجاح.');
    } catch (PDOException $e) {
        set_flash('error', 'تعذر حذف عرض السعر: ' . $e->getMessage());
    }
}

redirect('index.php');
?>
