<?php
require_once '../includes/auth.php';
require_login();

$page_title = 'إضافة عميل جديد';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $address = clean($_POST['address'] ?? '');

    if ($name === '') {
        set_flash('error', 'الرجاء إدخال اسم العميل.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO customers (user_id, name, phone, email, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([current_user_id(), $name, $phone, $email, $address]);
        set_flash('success', 'تمت إضافة العميل بنجاح.');
        redirect('index.php');
    }
}
include '../includes/header.php';
?>
<h4 class="mb-3 fw-bold"><i class="bi bi-person-plus text-primary me-2"></i>إضافة عميل جديد</h4>
<div class="card p-4 shadow-sm" style="max-width: 600px;">
  <form method="POST">
    <div class="mb-3"><label class="form-label fw-bold">اسم العميل / الشركة <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" placeholder="شركة البناء الحديثة" required></div>
    <div class="mb-3"><label class="form-label fw-bold">رقم الهاتف / الواتساب</label><input type="text" name="phone" class="form-control" placeholder="01012345678"></div>
    <div class="mb-3"><label class="form-label fw-bold">البريد الإلكتروني (اختياري)</label><input type="email" name="email" class="form-control"></div>
    <div class="mb-3"><label class="form-label fw-bold">العنوان (اختياري)</label><input type="text" name="address" class="form-control"></div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-save me-1"></i> حفظ بيانات العميل</button>
      <a href="index.php" class="btn btn-secondary">إلغاء</a>
    </div>
  </form>
</div>
<?php include '../includes/footer.php'; ?>
