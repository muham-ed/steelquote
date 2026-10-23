<?php
require_once '../includes/auth.php';
require_login();

$page_title = 'تعديل خامة';
$id = (int)($_GET['id'] ?? 0);
$user_id = current_user_id();

$stmt = $pdo->prepare("SELECT * FROM materials WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$mat = $stmt->fetch();

if (!$mat) { set_flash('error', 'الخامة غير موجودة.'); redirect('index.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name'] ?? '');
    $unit = clean($_POST['unit'] ?? '');
    $price = (float)($_POST['unit_price'] ?? 0);
    $notes = clean($_POST['notes'] ?? '');

    if ($name === '' || $unit === '' || $price <= 0) {
        set_flash('error', 'الرجاء إدخال بيانات صحيحة.');
    } else {
        $update = $pdo->prepare("UPDATE materials SET name=?, unit=?, unit_price=?, notes=? WHERE id=? AND user_id=?");
        $update->execute([$name, $unit, $price, $notes, $id, $user_id]);
        set_flash('success', 'تم تعديل الخامة بنجاح.');
        redirect('index.php');
    }
}
include '../includes/header.php';
?>
<h4 class="mb-3 fw-bold"><i class="bi bi-pencil text-primary me-2"></i>تعديل الخامة: <?= clean($mat['name']) ?></h4>
<div class="card p-4 shadow-sm" style="max-width: 600px;">
  <form method="POST">
    <div class="mb-3"><label class="form-label fw-bold">اسم الخامة</label><input type="text" name="name" class="form-control" value="<?= clean($mat['name']) ?>" required></div>
    <div class="mb-3"><label class="form-label fw-bold">وحدة القياس</label><input type="text" name="unit" class="form-control" value="<?= clean($mat['unit']) ?>" required></div>
    <div class="mb-3"><label class="form-label fw-bold">سعر الوحدة (ج.م)</label><input type="number" step="0.01" name="unit_price" class="form-control" value="<?= $mat['unit_price'] ?>" required></div>
    <div class="mb-3"><label class="form-label fw-bold">ملاحظات (اختياري)</label><input type="text" name="notes" class="form-control" value="<?= clean($mat['notes'] ?? '') ?>"></div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-save me-1"></i> تحديث</button>
      <a href="index.php" class="btn btn-secondary">إلغاء</a>
    </div>
  </form>
</div>
<?php include '../includes/footer.php'; ?>
