<?php
require_once '../includes/auth.php';
require_admin();

$page_title = 'إدارة طلبات تسعير العملاء (B2B2C Leads)';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $lead_id = (int)($_POST['lead_id'] ?? 0);
    if ($lead_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM project_leads WHERE id = ?");
        $stmt->execute([$lead_id]);
        set_flash('success', 'تم حذف طلب تسعير العميل بنجاح.');
    }
    redirect('leads.php');
}

$leads = $pdo->query("SELECT * FROM project_leads ORDER BY created_at DESC")->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1"><i class="bi bi-briefcase text-primary me-2"></i> إدارة طلبات تسعير العملاء B2B2C</h4>
        <p class="text-muted small mb-0">جميع طلبات المشاريع التي أرسلها العملاء عبر نموذج `request_quote.php` لمتابعتها وتوجيهها للورش</p>
    </div>
    <a href="../request_quote.php" target="_blank" class="btn btn-outline-primary btn-sm fw-bold"><i class="bi bi-box-arrow-up-right me-1"></i> فتح نموذج العميل</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>اسم العميل</th>
                    <th>الهاتف / الواتساب</th>
                    <th>المحافظة</th>
                    <th>نوع المشروع</th>
                    <th>التفاصيل</th>
                    <th>التاريخ</th>
                    <th class="text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leads)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">لا توجد طلبات عملاء حتى الآن.</td></tr>
                <?php else: foreach ($leads as $l): ?>
                    <tr>
                        <td><?= $l['id'] ?></td>
                        <td class="fw-bold text-dark"><?= clean($l['client_name']) ?></td>
                        <td class="fw-bold text-success"><i class="bi bi-telephone me-1"></i> <?= clean($l['client_phone']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= clean($l['city']) ?></span></td>
                        <td class="fw-bold text-primary"><?= clean($l['project_type']) ?></td>
                        <td class="small text-muted" style="max-width: 250px;"><?= clean($l['description'] ?? '-') ?></td>
                        <td class="small text-muted"><?= date('Y-m-d', strtotime($l['created_at'])) ?></td>
                        <td class="text-center">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="lead_id" value="<?= $l['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الطلب؟');"><i class="bi bi-trash"></i> حذف</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
