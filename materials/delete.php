<?php
require_once '../includes/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM materials WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, current_user_id()]);
    set_flash('success', 'تم حذف الخامة بنجاح.');
}
redirect('index.php');
?>
