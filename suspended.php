<?php
require_once 'includes/auth.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user = current_user();

if ($user['role'] === 'admin' || !in_array($user['subscription_status'], ['expired', 'suspended'])) {
    redirect('index.php');
}

$page_title = 'تنبيه الاشتراك';
include 'includes/header.php';

// Super admin WhatsApp contact
$admin_phone = '201012345678';
$wa_msg = urlencode("مرحباً، أود تجديد اشتراك ورشتي [" . $user['workshop_name'] . "] على منصة SteelQuote.");
$wa_url = "https://api.whatsapp.com/send?phone=$admin_phone&text=$wa_msg";
?>

<div class="row justify-content-center py-5">
    <div class="col-md-6">
        <div class="card shadow-lg p-4 text-center border-0 border-top border-danger border-5">
            <div class="fs-1 text-danger mb-3">
                <i class="bi bi-lock-fill"></i>
            </div>

            <h4 class="fw-bold text-dark mb-2">عفواً، حساب ورشتك متوقف حالياً</h4>
            <p class="text-muted mb-4">
                انتهت الفترة التجريبية أو تم تعليق الاشتراك لورشتك (<strong><?= clean($user['workshop_name']) ?></strong>).<br>
                يرجى التواصل مع إدارة المنصة لتسديد الاشتراكات وتفعيل الحساب فوراً.
            </p>

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
                    <i class="bi bi-whatsapp me-2"></i> تواصل مع الإدارة للتفعيل عبر الواتساب
                </a>
                <a href="logout.php" class="btn btn-outline-secondary">تسجيل الخروج</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
