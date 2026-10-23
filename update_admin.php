<?php
require_once 'includes/functions.php';

$page_title = 'تحديث بيانات مدير النظام الخصي';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = clean($_POST['email'] ?? '');
    $new_pass = $_POST['password'] ?? '';
    $new_name = clean($_POST['name'] ?? '');

    if (empty($new_email) || empty($new_pass) || empty($new_name)) {
        $error = 'الرجاء إدخال الاسم، البريد الإلكتروني، وكلمة المرور القوية.';
    } elseif (strlen($new_pass) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف أو أكثر.';
    } else {
        try {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);

            // Check if admin user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
            $stmt->execute();
            $admin = $stmt->fetch();

            if ($admin) {
                $update = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                $update->execute([$new_name, $new_email, $hashed, $admin['id']]);
            } else {
                $insert = $pdo->prepare("INSERT INTO users (role, name, email, password, workshop_name, subscription_status, trial_ends_at) VALUES ('admin', ?, ?, ?, 'إدارة منصة SteelQuote', 'active', '2030-12-31')");
                $insert->execute([$new_name, $new_email, $hashed]);
            }

            $message = 'تم تحديث وتأمين بيانات مدير النظام بنجاح! يمكنك الآن تسجيل الدخول ببريدك الإلكتروني الشخصي وكلمة المرور الجديدة.';
        } catch (Exception $e) {
            $error = 'حدث خطأ أثناء التحديث: ' . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center py-4">
    <div class="col-md-6">
        <div class="card shadow-lg p-4 border-0 border-top border-primary border-5">
            <div class="text-center mb-4">
                <div class="fs-1 text-primary"><i class="bi bi-shield-check"></i></div>
                <h4 class="fw-bold text-dark mb-1">تأمين حـساب مدير النظام (Super Admin)</h4>
                <p class="text-muted small">ضع بريدك الإلكتروني الشخصي الحقيقي وكلمة مرور سرية قوية لحماية حسابك من التوقع</p>
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
                    <input type="text" name="name" class="form-control" placeholder="مثال: المهندس محمد" value="المهندس محمد (مالك المنصة)" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">بريدك الإلكتروني الشخصي الحقيقي <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="your-email@gmail.com" required>
                    <small class="text-muted">هذا الإيميل هو الذي ستستخدمه لتسجيل الدخول كمالك للمنصة.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">كلمة المرور الجديدة القوية <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="أدخل كلمة سر معقدة وصعبة التوقع" required>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold"><i class="bi bi-lock me-1"></i> حفظ وتأمين حساب المدير فوراً</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
