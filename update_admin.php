<?php
require_once 'includes/functions.php';

$page_title = 'تحديث بيانات مدير النظام الخصي';

$message = '';
$error = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
$stmt->execute();
$current_admin = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = clean($_POST['email'] ?? '');
    $new_pass = $_POST['password'] ?? '';
    $new_name = clean($_POST['name'] ?? '');
    $new_phone = clean($_POST['phone'] ?? '');

    if (empty($new_email) || empty($new_name) || empty($new_phone)) {
        $error = 'الرجاء إدخال الاسم، البريد الإلكتروني، ورقم الفودافون كاش / الواتساب.';
    } else {
        try {
            if (!empty($new_pass)) {
                if (strlen($new_pass) < 6) {
                    $error = 'كلمة المرور يجب أن تكون 6 أحرف أو أكثر.';
                } else {
                    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                    if ($current_admin) {
                        $update = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
                        $update->execute([$new_name, $new_email, $new_phone, $hashed, $current_admin['id']]);
                    } else {
                        $insert = $pdo->prepare("INSERT INTO users (role, name, email, phone, password, workshop_name, subscription_status, trial_ends_at) VALUES ('admin', ?, ?, ?, ?, 'إدارة منصة SteelQuote', 'active', '2030-12-31')");
                        $insert->execute([$new_name, $new_email, $new_phone, $hashed]);
                    }
                    $message = 'تم تحديث وتأمين بيانات مدير النظام ورقم الفودافون كاش بنجاح!';
                }
            } else {
                if ($current_admin) {
                    $update = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
                    $update->execute([$new_name, $new_email, $new_phone, $current_admin['id']]);
                    $message = 'تم تحديث الاسم والبريد ورقم الفودافون كاش بنجاح!';
                } else {
                    $error = 'يرجى إدخال كلمة المرور لتفعيل الحساب أول مرة.';
                }
            }
        } catch (Exception $e) {
            $error = 'حدث خطأ أثناء التحديث: ' . $e->getMessage();
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
$stmt->execute();
$current_admin = $stmt->fetch();

include 'includes/header.php';
?>

<div class="row justify-content-center py-4">
    <div class="col-md-6">
        <div class="card shadow-lg p-4 border-0 border-top border-primary border-5">
            <div class="text-center mb-4">
                <div class="fs-1 text-primary"><i class="bi bi-shield-check"></i></div>
                <h4 class="fw-bold text-dark mb-1">تأمين وتحديث بيانات المدير (Super Admin)</h4>
                <p class="text-muted small">ضع بريدك الإلكتروني الشخصي ورقم الفودافون كاش لاستلام الاشتراكات المالية</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success fw-bold shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> <?= $message ?><br>
                    <a href="login.php" class="btn btn-sm btn-success mt-2 fw-bold"><i class="bi bi-box-arrow-in-right me-1"></i> الذهاب لتسجيل الدخول الان</a>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger fw-bold shadow-sm">
                    <i class="bi bi-exclamation-triangle me-1"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">اسم مالك التطبيق (المدير)</label>
                    <input type="text" name="name" class="form-control" placeholder="مثال: المهندس محمد" value="<?= clean($current_admin['name'] ?? 'المهندس محمد (مالك المنصة)') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">بريدك الإلكتروني الشخصي الحقيقي <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="your-email@gmail.com" value="<?= clean($current_admin['email'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">رقم الفودافون كاش / الواتساب لتحويل الاشتراكات <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-phone"></i></span>
                        <input type="text" name="phone" class="form-control fw-bold text-primary" placeholder="01060828627" value="<?= clean($current_admin['phone'] ?? '01060828627') ?>" required>
                    </div>
                    <small class="text-muted">هذا الرقم سيظهر لأصحاب الورش للتحويل المالي عليه والتواصل معك فوراً.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">كلمة المرور الجديدة القوية</label>
                    <input type="password" name="password" class="form-control" placeholder="أدخل كلمة سر جديدة وصعبة التوقع">
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold"><i class="bi bi-lock me-1"></i> حفظ وتأمين البيانات فوراً</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>