<?php
require_once '../includes/auth.php';
require_login();

$page_title = 'إدارة الخامات';
$user_id = current_user_id();

$stmt = $pdo->prepare("SELECT * FROM materials WHERE user_id = ? ORDER BY name ASC");
$stmt->execute([$user_id]);
$materials = $stmt->fetchAll();

include '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="fw-bold mb-1"><i class="bi bi-box-seam text-primary me-2"></i>خامات الورشة</h4>
      <p class="text-muted small mb-0">قائمة الخامات المسجلة وأسعار الوحدات الخاصة بورشَتك</p>
    </div>
    <div class="d-flex gap-2">
      <a href="../calculator.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-calculator me-1"></i> حاسبة أوزان الحديد</a>
      <a href="create.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> إضافة خامة جديد</a>
    </div>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light"><tr><th>#</th><th>اسم الخامة</th><th>الوحدة</th><th>سعر الوحدة</th><th>ملاحظات</th><th class="text-center">إجراءات</th></tr></thead>
      <tbody>
      <?php if (empty($materials)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">لا توجد خامات مسجلة بعد — أضف خاماتك للبدء بالتسعير المباشر.</td></tr>
      <?php else: foreach ($materials as $m): ?>
        <tr>
          <td><?= $m['id'] ?></td>
          <td class="fw-bold text-dark"><?= clean($m['name']) ?></td>
          <td><span class="badge bg-light text-dark border"><?= clean($m['unit']) ?></span></td>
          <td class="fw-bold text-success"><?= money($m['unit_price']) ?> ج.م</td>
          <td class="small text-muted"><?= clean($m['notes'] ?? '-') ?></td>
          <td class="text-center">
            <a href="edit.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i> تعديل</a>
            <a href="delete.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من حذف هذه الخامة؟');"><i class="bi bi-trash"></i> حذف</a>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
