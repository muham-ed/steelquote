<?php
require_once 'includes/auth.php';
require_login();

$page_title = 'فرص المشاريع وطلبات العملاء (B2B2C Marketplace)';
$user = current_user();

// Convert lead into a customer if requested
if (isset($_GET['action']) && $_GET['action'] === 'convert' && isset($_GET['lead_id'])) {
    $lead_id = (int)$_GET['lead_id'];
    $stmt = $pdo->prepare("SELECT * FROM project_leads WHERE id = ?");
    $stmt->execute([$lead_id]);
    $lead = $stmt->fetch();

    if ($lead) {
        // Add to workshop customers
        $ins = $pdo->prepare("INSERT INTO customers (user_id, name, phone, address) VALUES (?, ?, ?, ?)");
        $ins->execute([current_user_id(), $lead['client_name'], $lead['client_phone'], $lead['city']]);

        set_flash('success', 'تم إضافة العميل ' . clean($lead['client_name']) . ' لقائمة عملائك بنجاح! يمكنك الآن إرسال عرض سعر له.');
        redirect('quotations/create.php');
    }
}

// Fetch all project leads
$stmt = $pdo->query("SELECT * FROM project_leads ORDER BY created_at DESC");
$leads = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-briefcase text-primary me-2"></i> طلبات تسعير العملاء وفرص العمل المتاحة</h4>
        <p class="text-muted small mb-0">طلبات المشاريع الواردة مباشرة من العملاء النهائيين في مختلف المحافظات</p>
    </div>
    <a href="request_quote.php" target="_blank" class="btn btn-outline-warning btn-sm fw-bold text-dark"><i class="bi bi-plus-circle me-1"></i> نموذج طلب عميل جديد</a>
</div>

<div class="row g-3">
    <?php if (empty($leads)): ?>
        <div class="col-12 text-center py-5 card shadow-sm text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
            لا توجد طلبات مشاريع جديدة حالياً. كُن أول من يستقبل الطلبات عند نشر نموذج العميل!
        </div>
    <?php else: foreach ($leads as $l): ?>
        <?php
        $clean_phone = preg_replace('/[^0-9]/', '', $l['client_phone']);
        if (substr($clean_phone, 0, 1) === '0') {
            $clean_phone = '20' . substr($clean_phone, 1);
        }
        $wa_text = urlencode("مرحباً بك د/ " . $l['client_name'] . "، قرأنا طلبك لتصنيع [" . $l['project_type'] . "] عبر منصة SteelQuote، ويسعدنا تقديم أفضل عرض سعر وتحديد موعد للمعاينة من ورشة " . $user['workshop_name']);
        $wa_link = "https://api.whatsapp.com/send?phone=$clean_phone&text=$wa_text";
        ?>
        <div class="col-md-6">
            <div class="card p-3 h-100 shadow-sm border-start border-warning border-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-geo-alt me-1"></i> <?= clean($l['city']) ?></span>
                    <small class="text-muted"><i class="bi bi-clock me-1"></i> <?= date('Y-m-d', strtotime($l['created_at'])) ?></small>
                </div>
                <h5 class="fw-bold text-dark mb-1"><?= clean($l['project_type']) ?></h5>
                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-person me-1"></i> العميل: <?= clean($l['client_name']) ?></h6>
                <p class="small text-muted mb-3 bg-light p-2 rounded" style="min-height: 50px;">
                    <?= !empty($l['description']) ? clean($l['description']) : 'لا توجد تفاصيل إضافية مدمجة.' ?>
                </p>
                <div class="d-flex gap-2">
                    <a href="<?= $wa_link ?>" target="_blank" class="btn btn-sm btn-success fw-bold flex-grow-1">
                        <i class="bi bi-whatsapp me-1"></i> تواصل عبر الواتساب
                    </a>
                    <a href="leads.php?action=convert&lead_id=<?= $l['id'] ?>" class="btn btn-sm btn-outline-primary fw-bold">
                        <i class="bi bi-person-plus me-1"></i> تحويل لعميل وإنشاء عرض
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
