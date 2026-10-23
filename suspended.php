<?php
require_once 'includes/auth.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user = current_user();

if ($user['role'] === 'admin' || !in_array($user['subscription_status'], ['expired', 'suspended'])) {
    redirect('index.php');
}

// Fetch Super Admin contact phone
$stmt = $pdo->query("SELECT phone FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1");
$admin = $stmt->fetch();
$raw_phone = !empty($admin['phone']) ? $admin['phone'] : '01012345678';

$clean_phone = preg_replace('/[^0-9]/', '', $raw_phone);
if (substr($clean_phone, 0, 1) === '0') {
    $clean_phone = '20' . substr($clean_phone, 1);
}

$page_title = 'تنبيه الاشتراك';
include 'includes/header.php';

$wa_msg = urlencode("مرحباً، أود تجديد اشتراك ورشتي [" . $user['workshop_name'] . "] وتحويل قيمة الاشتراك عبر فودافون كاش / انستا باي.");
$wa_url = "https://api.whatsapp.com/send?phone=$clean_phone&text=$wa_msg";
?>

<div class="row justify-content-center py-5">
    <div class="col-md-6">
        <div class="card shadow-lg p-4 text-center border-0 border-top border-danger border-5">
            <div class="fs-1 text-danger mb-3">
                <i class="bi bi-lock-fill"></i>
            </div>

            <h4 class="fw-bold text-dark mb-2">عفواً، حساب ورشتك متوقف حالياً</h4>
            <p class="text-muted mb-3">
                انتهت الفترة التجريبية أو تم تعليق الاشتراك لورشتك (<strong><?= clean($user['workshop_name']) ?></strong>).<br>
                يرجى تحويل مبلغ الاشتراك وتواصل معنا فوراً لتفعيل حسابتك.
            </p>

            <div class="alert alert-warning text-dark fw-bold mb-4">
                <i class="bi bi-phone-vibrate me-1"></i> رقم تحويل فودافون كاش / انستا باي للـتأكيد: <br>
                <span class="fs-4 text-primary font-monospace"><?= clean($raw_phone) ?></span>
            </div>

            <div class="p-3 bg-light rounded mb-4 text-start small">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">صاحب الورشة:</span>
                    <span class="fw-bold text-dark"><?= clean($user['name']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">البريد الإلكتروني:</span>
                    <span class="fw-bold text-dark"><?= clean($user['email']) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">حالة الاشتراك:</span>
                    <span class="badge bg-danger"><?= $user['subscription_status'] === 'expired' ? 'انتهى الاشتراك' : 'حساب معلق' ?></span>
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="<?= $wa_url ?>" target="_blank" class="btn btn-success btn-lg fw-bold shadow-sm">
                    <i class="bi bi-whatsapp me-2"></i> تواصل معنا وتأكيد التحويل عبر الواتساب
                </a>
                <a href="logout.php" class="btn btn-outline-secondary">تسجيل الخروج</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
