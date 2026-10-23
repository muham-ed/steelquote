<?php
require_once '../includes/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$user = current_user();

// Fetch quotation details scoped by user_id
$stmt = $pdo->prepare("SELECT q.*, c.name AS customer_name, c.phone AS customer_phone, c.email AS customer_email, c.address AS customer_address
                       FROM quotations q
                       JOIN customers c ON c.id = q.customer_id
                       WHERE q.id = ? AND q.user_id = ?");
$stmt->execute([$id, current_user_id()]);
$quote = $stmt->fetch();

if (!$quote) {
    set_flash('error', 'عرض السعر غير موجود أو لا تملك صلاحية الوصول إليه.');
    redirect('index.php');
}

// Fetch items
$item_stmt = $pdo->prepare("SELECT * FROM quotation_items WHERE quotation_id = ? ORDER BY id ASC");
$item_stmt->execute([$id]);
$items = $item_stmt->fetchAll();

$page_title = 'عرض سعر ' . $quote['quote_number'];

// Quick status change handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $new_status = clean($_POST['status'] ?? '');
    if (in_array($new_status, ['draft', 'sent', 'accepted', 'rejected'])) {
        $update = $pdo->prepare("UPDATE quotations SET status = ? WHERE id = ? AND user_id = ?");
        $update->execute([$new_status, $id, current_user_id()]);
        set_flash('success', 'تم تحديث حالة عرض السعر إلى: ' . get_status_label($new_status));
        redirect('view.php?id=' . $id);
    }
}

// WhatsApp Link Generator
$clean_phone = preg_replace('/[^0-9]/', '', $quote['customer_phone'] ?? '');
if (substr($clean_phone, 0, 1) === '0') {
    $clean_phone = '20' . substr($clean_phone, 1); // Egypt country code default
}
$wa_msg = "مرحباً بك د/ " . $quote['customer_name'] . "،\nيسعدنا تقديم عرض السعر رقم " . $quote['quote_number'] . " الخاص بمشروع [" . $quote['project_name'] . "] بقيمة " . money($quote['selling_price']) . " ج.م من " . $user['workshop_name'] . ".\nيمكنكم معاينة العرض المطبوع عبر الرابط:\n" . 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . "quotations/pdf.php?id=" . $quote['id'];
$wa_url = "https://api.whatsapp.com/send?phone=" . $clean_phone . "&text=" . urlencode($wa_msg);

$remaining_amount = max(0, $quote['selling_price'] - $quote['deposit_amount']);

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-file-earmark-text text-primary me-2"></i>عرض سعر: <?= clean($quote['quote_number']) ?>
            <?= get_status_badge($quote['status']) ?>
        </h4>
        <p class="text-muted small mb-0">تاريخ الإنشاء: <?= date('Y-m-d h:i A', strtotime($quote['created_at'])) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $wa_url ?>" target="_blank" class="btn btn-success shadow-sm fw-bold">
            <i class="bi bi-whatsapp me-1"></i> إرسال عبر واتساب
        </a>
        <a href="pdf.php?id=<?= $quote['id'] ?>" target="_blank" class="btn btn-primary shadow-sm fw-bold">
            <i class="bi bi-file-earmark-pdf me-1"></i> طباعة / تحميل PDF
        </a>
        <a href="edit.php?id=<?= $quote['id'] ?>" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-pencil me-1"></i> تعديل
        </a>
        <a href="index.php" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-right me-1"></i> رجوع
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Customer Details Card -->
    <div class="col-md-6">
        <div class="card p-3 h-100 shadow-sm border-start border-primary border-4">
            <h6 class="fw-bold text-primary mb-2"><i class="bi bi-person me-1"></i> بيانات العميل</h6>
            <h5 class="fw-bold mb-2"><?= clean($quote['customer_name']) ?></h5>
            <p class="mb-1 text-muted small"><i class="bi bi-telephone me-1"></i> الهاتف: <?= !empty($quote['customer_phone']) ? clean($quote['customer_phone']) : '-' ?></p>
            <p class="mb-1 text-muted small"><i class="bi bi-envelope me-1"></i> الإيميل: <?= !empty($quote['customer_email']) ? clean($quote['customer_email']) : '-' ?></p>
            <p class="mb-0 text-muted small"><i class="bi bi-geo-alt me-1"></i> العنوان: <?= !empty($quote['customer_address']) ? clean($quote['customer_address']) : '-' ?></p>
        </div>
    </div>

    <!-- Project Details & Status Card -->
    <div class="col-md-6">
        <div class="card p-3 h-100 shadow-sm border-start border-info border-4">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="fw-bold text-info mb-0"><i class="bi bi-building me-1"></i> تفاصيل المشروع والحالة</h6>
                <!-- Quick status changer -->
                <form method="POST" class="d-flex align-items-center gap-1">
                    <input type="hidden" name="action" value="update_status">
                    <span class="small fw-bold me-1">تغيير الحالة:</span>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="draft" <?= $quote['status'] === 'draft' ? 'selected' : '' ?>>مسودة</option>
                        <option value="sent" <?= $quote['status'] === 'sent' ? 'selected' : '' ?>>مرسل للعميل</option>
                        <option value="accepted" <?= $quote['status'] === 'accepted' ? 'selected' : '' ?>>مقبول</option>
                        <option value="rejected" <?= $quote['status'] === 'rejected' ? 'selected' : '' ?>>مرفوض</option>
                    </select>
                </form>
            </div>
            <h5 class="fw-bold mb-2 text-dark"><?= clean($quote['project_name']) ?></h5>
            <p class="mb-1 text-muted small"><i class="bi bi-calendar-event me-1"></i> صالحة حتى: <?= !empty($quote['valid_until']) ? clean($quote['valid_until']) : 'غير محدد' ?></p>
        </div>
    </div>
</div>

<!-- Items Table -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-bold py-3 text-primary">
        <i class="bi bi-list-check me-1"></i> جدول بنود ومستلزمات العمل
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light text-center">
                <tr>
                    <th style="width: 5%;">#</th>
                    <th>وصف البند / الخامة</th>
                    <th style="width: 15%;">الوحدة</th>
                    <th style="width: 12%;">الكمية</th>
                    <th style="width: 15%;">سعر الوحدة</th>
                    <th style="width: 18%;">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 1; foreach ($items as $item): ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $count++ ?></td>
                        <td class="fw-semibold"><?= clean($item['item_description']) ?></td>
                        <td class="text-center"><?= clean($item['unit']) ?></td>
                        <td class="text-center fw-bold"><?= number_format((float)$item['quantity'], 2) ?></td>
                        <td class="text-end"><?= money($item['unit_price']) ?> ج.م</td>
                        <td class="text-end fw-bold text-dark"><?= money($item['total_price']) ?> ج.م</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Breakdown and Pricing Summary -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card p-3 h-100 shadow-sm">
            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-card-text me-1"></i> الملاحظات وشروط العرض</h6>
            <div class="p-3 bg-light rounded text-muted mb-3" style="white-space: pre-line; min-height: 100px;">
                <?= !empty($quote['notes']) ? clean($quote['notes']) : 'لا توجد ملاحظات إضافية.' ?>
            </div>

            <!-- Deposit & Remaining -->
            <div class="border rounded p-3 bg-white">
                <h6 class="fw-bold text-success mb-2"><i class="bi bi-cash-stack me-1"></i> موقف العربون والمستحقات:</h6>
                <div class="d-flex justify-content-between mb-1 small text-muted">
                    <span>العربون / الدفعة المقدمة:</span>
                    <span class="fw-bold text-success"><?= money($quote['deposit_amount']) ?> ج.م</span>
                </div>
                <div class="d-flex justify-content-between small text-muted">
                    <span>المبلغ المتبقي عند التسليم:</span>
                    <span class="fw-bold text-danger"><?= money($remaining_amount) ?> ج.م</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3 shadow-sm border-primary">
            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-calculator me-1"></i> تفاصيل الحسابات المالية</h6>
            <table class="table table-sm table-borderless align-middle mb-0">
                <tr>
                    <td class="text-muted">إجمالي الخامات:</td>
                    <td class="text-end fw-bold"><?= money($quote['materials_cost']) ?> ج.م</td>
                </tr>
                <tr>
                    <td class="text-muted">تكلفة المصنعية والعمالة:</td>
                    <td class="text-end fw-bold"><?= money($quote['labor_cost']) ?> ج.م</td>
                </tr>
                <tr>
                    <td class="text-muted">المصاريف الإدارية / النقل:</td>
                    <td class="text-end fw-bold"><?= money($quote['overhead_cost']) ?> ج.م</td>
                </tr>
                <tr class="border-top">
                    <td class="fw-bold">إجمالي التكلفة:</td>
                    <td class="text-end fw-bold text-dark fs-6"><?= money($quote['total_cost']) ?> ج.م</td>
                </tr>
                <tr>
                    <td class="text-muted">نسبة هامش الربح:</td>
                    <td class="text-end fw-bold text-info"><?= number_format((float)$quote['profit_margin_percent'], 1) ?> %</td>
                </tr>
                <tr>
                    <td class="text-muted">مبلغ الربح المقدر:</td>
                    <td class="text-end fw-bold text-success"><?= money($quote['profit_amount']) ?> ج.م</td>
                </tr>
                <tr class="border-top bg-primary text-white">
                    <td class="fw-bold fs-5 p-2">سعر البيع النهائي:</td>
                    <td class="text-end fw-bold fs-4 p-2"><?= money($quote['selling_price']) ?> ج.م</td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
