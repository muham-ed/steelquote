<?php
require_once 'includes/auth.php';
require_login();

$page_title = 'لوحة التحكم الإحصائية';
$user_id = current_user_id();
$user = current_user();

// Key Metrics scoped by user_id
$totalQuotes = (int)$pdo->query("SELECT COUNT(*) FROM quotations WHERE user_id = $user_id")->fetchColumn();
$totalValue = (float)$pdo->query("SELECT COALESCE(SUM(selling_price),0) FROM quotations WHERE user_id = $user_id AND status='accepted'")->fetchColumn();
$pendingQuotes = (int)$pdo->query("SELECT COUNT(*) FROM quotations WHERE user_id = $user_id AND status IN ('draft','sent')")->fetchColumn();
$totalCustomers = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE user_id = $user_id")->fetchColumn();
$totalMaterials = (int)$pdo->query("SELECT COUNT(*) FROM materials WHERE user_id = $user_id")->fetchColumn();

// Status Counts
$draftCount = (int)$pdo->query("SELECT COUNT(*) FROM quotations WHERE user_id = $user_id AND status='draft'")->fetchColumn();
$sentCount = (int)$pdo->query("SELECT COUNT(*) FROM quotations WHERE user_id = $user_id AND status='sent'")->fetchColumn();
$acceptedCount = (int)$pdo->query("SELECT COUNT(*) FROM quotations WHERE user_id = $user_id AND status='accepted'")->fetchColumn();
$rejectedCount = (int)$pdo->query("SELECT COUNT(*) FROM quotations WHERE user_id = $user_id AND status='rejected'")->fetchColumn();

// Recent Quotations
$recent = $pdo->query("SELECT q.*, c.name AS customer_name FROM quotations q JOIN customers c ON c.id = q.customer_id WHERE q.user_id = $user_id ORDER BY q.created_at DESC LIMIT 5")->fetchAll();

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3 class="fw-bold text-dark mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i>لوحة تحكم <?= clean($user['workshop_name']) ?></h3>
    <p class="text-muted mb-0">نظرة عامة على عروض الأسعار والمؤشرات المالـية لورشتك</p>
  </div>
  <div class="d-flex gap-2">
    <a href="calculator.php" class="btn btn-outline-primary shadow-sm"><i class="bi bi-calculator me-1"></i> حاسبة أوزان الحديد</a>
    <a href="quotations/create.php" class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg me-1"></i> عرض سعر جديد</a>
  </div>
</div>

<!-- Key Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card stat-card border-primary-accent p-3">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small fw-semibold">إجمالي عروض الأسعار</div>
          <div class="h3 fw-bold mb-0 text-dark"><?= $totalQuotes ?></div>
        </div>
        <div class="fs-1 text-primary opacity-50"><i class="bi bi-file-earmark-text"></i></div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card border-success-accent p-3">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small fw-semibold">قيمة العروض المقبولة</div>
          <div class="h3 fw-bold mb-0 text-success"><?= money($totalValue) ?> <span class="fs-6 text-muted">ج.م</span></div>
        </div>
        <div class="fs-1 text-success opacity-50"><i class="bi bi-currency-dollar"></i></div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card border-warning-accent p-3">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small fw-semibold">عروض قيد الانتظار</div>
          <div class="h3 fw-bold mb-0 text-warning"><?= $pendingQuotes ?></div>
        </div>
        <div class="fs-1 text-warning opacity-50"><i class="bi bi-hourglass-split"></i></div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card border-info-accent p-3">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small fw-semibold">العملاء / الخامات</div>
          <div class="h3 fw-bold mb-0 text-info"><?= $totalCustomers ?> <span class="fs-6 text-muted">عميل</span> / <?= $totalMaterials ?> <span class="fs-6 text-muted">خامة</span></div>
        </div>
        <div class="fs-1 text-info opacity-50"><i class="bi bi-people"></i></div>
      </div>
    </div>
  </div>
</div>

<!-- Quotation Status Distribution -->
<div class="row g-3 mb-4">
  <div class="col-md-12">
    <div class="card p-3">
      <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart me-1 text-secondary"></i> توزيع حالة عروض الأسعار</h6>
      <div class="row text-center g-2">
        <div class="col-6 col-sm-3">
          <div class="border rounded p-2 bg-light">
            <span class="badge bg-secondary mb-1">مسودة</span>
            <div class="fw-bold fs-5"><?= $draftCount ?></div>
          </div>
        </div>
        <div class="col-6 col-sm-3">
          <div class="border rounded p-2 bg-light">
            <span class="badge bg-info text-dark mb-1">مرسل للعميل</span>
            <div class="fw-bold fs-5"><?= $sentCount ?></div>
          </div>
        </div>
        <div class="col-6 col-sm-3">
          <div class="border rounded p-2 bg-light">
            <span class="badge bg-success mb-1">تم القبول</span>
            <div class="fw-bold fs-5"><?= $acceptedCount ?></div>
          </div>
        </div>
        <div class="col-6 col-sm-3">
          <div class="border rounded p-2 bg-light">
            <span class="badge bg-danger mb-1">مرفوض</span>
            <div class="fw-bold fs-5"><?= $rejectedCount ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Quotations Table -->
<div class="card mb-4">
  <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
    <span><i class="bi bi-clock-history me-1 text-primary"></i> أحدث عروض الأسعار</span>
    <a href="quotations/index.php" class="btn btn-sm btn-outline-primary">عرض جميع العروض</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>رقم العرض</th>
          <th>العميل</th>
          <th>المشروع</th>
          <th>إجمالي التكلفة</th>
          <th>سعر البيع</th>
          <th>الحالة</th>
          <th>التاريخ</th>
          <th class="text-center">إجراءات</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$recent): ?>
        <tr>
          <td colspan="8" class="text-center text-muted py-4">
            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
            لا توجد عروض أسعار حتى الآن — ابدأ بإنشاء أول عرض سعر!
          </td>
        </tr>
      <?php else: foreach ($recent as $q): ?>
        <tr>
          <td class="fw-bold text-primary"><?= clean($q['quote_number']) ?></td>
          <td><?= clean($q['customer_name']) ?></td>
          <td><?= clean($q['project_name']) ?></td>
          <td><?= money($q['total_cost']) ?> ج.م</td>
          <td class="fw-bold text-success"><?= money($q['selling_price']) ?> ج.م</td>
          <td><?= get_status_badge($q['status']) ?></td>
          <td class="small text-muted"><?= date('Y-m-d', strtotime($q['created_at'])) ?></td>
          <td class="text-center">
            <div class="btn-group btn-group-sm">
              <a class="btn btn-outline-primary" href="quotations/view.php?id=<?= $q['id'] ?>" title="عرض التفاصيل"><i class="bi bi-eye"></i></a>
              <a class="btn btn-outline-secondary" href="quotations/edit.php?id=<?= $q['id'] ?>" title="تعديل"><i class="bi bi-pencil"></i></a>
              <a class="btn btn-outline-danger" href="quotations/pdf.php?id=<?= $q['id'] ?>" target="_blank" title="تحميل / طباعة PDF"><i class="bi bi-file-earmark-pdf"></i></a>
            </div>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Quick Action Buttons -->
<div class="d-flex flex-wrap gap-2">
  <a href="quotations/create.php" class="btn btn-primary"><i class="bi bi-file-earmark-plus me-1"></i> + عرض سعر جديد</a>
  <a href="calculator.php" class="btn btn-outline-primary"><i class="bi bi-calculator me-1"></i> حاسبة أوزان الحديد</a>
  <a href="materials/create.php" class="btn btn-outline-secondary"><i class="bi bi-box-seam me-1"></i> + إضافة خامة</a>
  <a href="customers/create.php" class="btn btn-outline-secondary"><i class="bi bi-person-plus me-1"></i> + إضافة عميل</a>
</div>

<?php include 'includes/footer.php'; ?>
