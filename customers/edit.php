<?php
require_once '../includes/auth.php';
require_login();

$page_title = 'تعديل بيانات عميل';
$id = (int)($_GET['id'] ?? 0);
$user_id = current_user_id();

$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$cust = $stmt->fetch();

if (!$cust) { set_flash('error', 'العميل غير موجود.'); redirect('index.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $address = clean($_POST['address'] ?? '');

    if ($name === '') {
        set_flash('error', 'الرجاء إدخال اسم العميل.');
    } else {
        $update = $pdo->prepare("UPDATE customers SET name=?, phone=?, email=?, address=? WHERE id=? AND user_id=?");
        $update->execute([$name, $phone, $email, $address, $id, $user_id]);
        set_flash('success', 'تم تعديل بيانات العميل بنجاح.');
        redirect('index.php');
    }
}
include '../includes/header.php';
?>
<h4 class="mb-3 fw-bold"><i class="bi bi-pencil text-primary me-2"></i>تعديل العميل: <?= clean($cust['name']) ?></h4>
<div class="card p-4 shadow-sm" style="max-width: 600px;">
  <form method="POST">
    <div class="mb-3"><label class="form-label fw-bold">اسم العميل / الشركة</label><input type="text" name="name" class="form-control" value="<?= clean($cust['name']) ?>" required></div>
    <div class="mb-3"><label class="form-label fw-bold">رقم الهاتف / الواتساب</label><input type="text" name="phone" class="form-control" value="<?= clean($cust['phone'] ?? '') ?>"></div>
    <div class="mb-3"><label class="form-label fw-bold">البريد الإلكتروني</label><input type="email" name="email" class="form-control" value="<?= clean($cust['email'] ?? '') ?>"></div>
    <div class="mb-3"><label class="form-label fw-bold">العنوان</label><input type="text" name="address" class="form-control" value="<?= clean($cust['address'] ?? '') ?>"></div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-save me-1"></i> تحديث</button>
      <a href="index.php" class="btn btn-secondary">إلغاء</a>
    </div>
  </form>
</div>
<?php include '../includes/footer.php'; ?>
