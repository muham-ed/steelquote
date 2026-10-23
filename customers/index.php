<?php
require_once '../includes/auth.php';
require_login();

$page_title = 'إدارة العملاء';
$user_id = current_user_id();

$stmt = $pdo->prepare("SELECT * FROM customers WHERE user_id = ? ORDER BY name ASC");
$stmt->execute([$user_id]);
$customers = $stmt->fetchAll();

include '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="fw-bold mb-1"><i class="bi bi-people text-primary me-2"></i>قائمة العملاء</h4>
      <p class="text-muted small mb-0">إدارة بيانات وأرقام هواتف عملاء ورشَتك</p>
    </div>
    <a href="create.php" class="btn btn-primary btn-sm"><i class="bi bi-person-plus me-1"></i> إضافة عميل جديد</a>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light"><tr><th>#</th><th>اسم العميل / الشركة</th><th>الهاتف / الواتساب</th><th>الإيميل</th><th>العنوان</th><th class="text-center">إجراءات</th></tr></thead>
      <tbody>
      <?php if (empty($customers)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">لا يوجد عملاء مسجلون بعد — قم بإضافة عملائك للتسيعر السريع.</td></tr>
      <?php else: foreach ($customers as $c): ?>
        <tr>
          <td><?= $c['id'] ?></td>
          <td class="fw-bold text-dark"><?= clean($c['name']) ?></td>
          <td>
            <?php if (!empty($c['phone'])): ?>
              <a href="https://api.whatsapp.com/send?phone=<?= preg_replace('/[^0-9]/', '', $c['phone']) ?>" target="_blank" class="text-decoration-none text-success fw-bold">
                <i class="bi bi-whatsapp"></i> <?= clean($c['phone']) ?>
              </a>
            <?php else: ?>
              <span class="text-muted">-</span>
            <?php endif; ?>
          </td>
          <td><?= clean($c['email'] ?? '-') ?></td>
          <td class="small text-muted"><?= clean($c['address'] ?? '-') ?></td>
          <td class="text-center">
            <a href="edit.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i> تعديل</a>
            <a href="delete.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من حذف هذا العميل؟');"><i class="bi bi-trash"></i> حذف</a>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
