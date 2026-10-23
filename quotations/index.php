<?php
require_once '../includes/auth.php';
require_login();

$page_title = 'عروض الأسعار';
$user_id = current_user_id();

// Handle quick status update via POST if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $quote_id = (int)($_POST['quote_id'] ?? 0);
    $new_status = clean($_POST['status'] ?? '');
    if ($quote_id > 0 && in_array($new_status, ['draft', 'sent', 'accepted', 'rejected'])) {
        $stmt = $pdo->prepare("UPDATE quotations SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$new_status, $quote_id, $user_id]);
        set_flash('success', 'تم تحديث حالة عرض السعر بنجاح.');
    } else {
        set_flash('error', 'بيانات غير صالحة لتحديث الحالة.');
    }
    redirect('index.php');
}

// Search and filter parameters
$status_filter = clean($_GET['status'] ?? '');
$customer_filter = (int)($_GET['customer_id'] ?? 0);
$search = clean($_GET['search'] ?? '');

$query = "SELECT q.*, c.name AS customer_name, c.phone AS customer_phone
          FROM quotations q
          JOIN customers c ON c.id = q.customer_id
          WHERE q.user_id = ?";
$params = [$user_id];

if ($status_filter !== '') {
    $query .= " AND q.status = ?";
    $params[] = $status_filter;
}

if ($customer_filter > 0) {
    $query .= " AND q.customer_id = ?";
    $params[] = $customer_filter;
}

if ($search !== '') {
    $query .= " AND (q.quote_number LIKE ? OR q.project_name LIKE ? OR c.name LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$query .= " ORDER BY q.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$quotations = $stmt->fetchAll();

// Fetch customers for filter dropdown
$customers = $pdo->prepare("SELECT id, name FROM customers WHERE user_id = ? ORDER BY name");
$customers->execute([$user_id]);
$customers = $customers->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-text text-primary me-2"></i>عروض الأسعار</h4>
        <p class="text-muted small mb-0">إدارة ومتابعة جميع عروض الأسعار المقدمة لعملائك</p>
    </div>
    <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> عرض سعر جديد</a>
</div>

<!-- Filters Card -->
<div class="card p-3 mb-4 bg-white shadow-sm">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-bold">بحث</label>

            <input type="text" name="search" class="form-control form-control-sm" placeholder="رقم العرض، اسم المشروع، اسم العميل..." value="<?= clean($search) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold">الحالة</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">جميع الحالات</option>
                <option value="draft" <?= $status_filter === 'draft' ? 'selected' : '' ?>>مسودة</option>
                <option value="sent" <?= $status_filter === 'sent' ? 'selected' : '' ?>>مرسل للعميل</option>
                <option value="accepted" <?= $status_filter === 'accepted' ? 'selected' : '' ?>>مقبول</option>
                <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>مرفوض</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold">العميل</label>
            <select name="customer_id" class="form-select form-select-sm">
                <option value="0">جميع العملاء</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $customer_filter === (int)$c['id'] ? 'selected' : '' ?>><?= clean($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-secondary w-100"><i class="bi bi-funnel"></i> تصفية</button>
            <a href="index.php" class="btn btn-sm btn-outline-secondary" title="إعادة ضبط"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<!-- Quotations Table -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>رقم العرض</th>
                    <th>العميل</th>
                    <th>المشروع</th>
                    <th>إجمالي التكلفة</th>
                    <th>هامش الربح</th>
                    <th>سعر البيع النهائي</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th class="text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($quotations)): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="bi bi-info-circle fs-3 d-block mb-2 text-secondary"></i>
                        لا توجد عروض أسعار تطابق خيارات البحث الحالية.
                    </td>
                </tr>
            <?php else: foreach ($quotations as $q): ?>
                <tr>
                    <td class="fw-bold text-primary"><?= clean($q['quote_number']) ?></td>
                    <td>
                        <div class="fw-semibold"><?= clean($q['customer_name']) ?></div>
                        <?php if (!empty($q['customer_phone'])): ?>
                            <small class="text-muted"><i class="bi bi-telephone"></i> <?= clean($q['customer_phone']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= clean($q['project_name']) ?></td>
                    <td><?= money($q['total_cost']) ?> ج.م</td>
                    <td><span class="badge bg-light text-dark border"><?= number_format((float)$q['profit_margin_percent'], 1) ?>%</span></td>
                    <td class="fw-bold text-success fs-6"><?= money($q['selling_price']) ?> ج.م</td>
                    <td>
                        <!-- Quick Status Change Form -->
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="quote_id" value="<?= $q['id'] ?>">
                            <select name="status" class="form-select form-select-sm border-0 bg-transparent fw-bold p-0" style="width: auto; cursor: pointer;" onchange="this.form.submit()">
                                <option value="draft" <?= $q['status'] === 'draft' ? 'selected' : '' ?>>📝 مسودة</option>
                                <option value="sent" <?= $q['status'] === 'sent' ? 'selected' : '' ?>>📤 مرسل</option>
                                <option value="accepted" <?= $q['status'] === 'accepted' ? 'selected' : '' ?>>✅ مقبول</option>
                                <option value="rejected" <?= $q['status'] === 'rejected' ? 'selected' : '' ?>>❌ مرفوض</option>
                            </select>
                        </form>
                    </td>
                    <td class="small text-muted"><?= date('Y-m-d', strtotime($q['created_at'])) ?></td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="view.php?id=<?= $q['id'] ?>" class="btn btn-outline-primary" title="عرض المفصل"><i class="bi bi-eye"></i></a>
                            <a href="edit.php?id=<?= $q['id'] ?>" class="btn btn-outline-secondary" title="تعديل العرض"><i class="bi bi-pencil"></i></a>
                            <a href="pdf.php?id=<?= $q['id'] ?>" target="_blank" class="btn btn-outline-success" title="طباعة / PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                            <a href="delete.php?id=<?= $q['id'] ?>" class="btn btn-outline-danger" title="حذف" onclick="return confirm('هل أنت متأكد من حذف هذا العرض نهائياً؟');"><i class="bi bi-trash"></i></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
