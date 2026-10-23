<?php
require_once '../includes/auth.php';
require_login();

$page_title = 'إضافة خامة جديدة';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name'] ?? '');
    $unit = clean($_POST['unit'] ?? '');
    $price = (float)($_POST['unit_price'] ?? 0);
    $notes = clean($_POST['notes'] ?? '');

    if ($name === '' || $unit === '' || $price <= 0) {
        set_flash('error', 'الرجاء إدخال بيانات صحيحة للخامة.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO materials (user_id, name, unit, unit_price, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([current_user_id(), $name, $unit, $price, $notes]);
        set_flash('success', 'تمت إضافة الخامة بنجاح.');
        redirect('index.php');
    }
}
include '../includes/header.php';
?>
<h4 class="mb-3 fw-bold"><i class="bi bi-box-seam text-primary me-2"></i>إضافة خامة جديدة</h4>
<div class="card p-4 shadow-sm" style="max-width: 600px;">
  <form method="POST">
    <div class="mb-3"><label class="form-label fw-bold">اسم الخامة <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" placeholder="مثال: علب حديد 4×4 سم سُمك 2 مم" required></div>
    <div class="mb-3"><label class="form-label fw-bold">وحدة القياس <span class="text-danger">*</span></label><input type="text" name="unit" class="form-control" placeholder="كجم، متر طولي، قطعة، لوح..." required></div>
    <div class="mb-3"><label class="form-label fw-bold">سعر الوحدة (ج.م) <span class="text-danger">*</span></label><input type="number" step="0.01" min="0.01" name="unit_price" class="form-control" placeholder="0.00" required></div>
    <div class="mb-3"><label class="form-label fw-bold">ملاحظات (اختياري)</label><input type="text" name="notes" class="form-control"></div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-save me-1"></i> حفظ الخامة</button>
      <a href="index.php" class="btn btn-secondary">إلغاء</a>
    </div>
  </form>
</div>
<?php include '../includes/footer.php'; ?>
