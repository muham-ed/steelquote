<?php
require_once 'includes/auth.php';
$page_title = 'تسجيل الدخول';

if (is_logged_in()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        set_flash('error', 'الرجاء إدخال البريد الإلكتروني وكلمة المرور.');
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['workshop_name'] = $user['workshop_name'];

            set_flash('success', 'أهلاً بك يا معلم ' . clean($user['name']) . ' في نظام SteelQuote!');
            redirect('index.php');
        } else {
            set_flash('error', 'البريد الإلكتروني أو كلمة المرور غير صحيحة.');
        }
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center py-5">
  <div class="col-md-5">
    <div class="card shadow-lg p-4 border-0">
      <div class="text-center mb-4">
        <div class="fs-1 text-primary"><i class="bi bi-tools"></i></div>
        <h4 class="fw-bold text-dark mb-1">SteelQuote</h4>
        <p class="text-muted small">تسجيل الدخول لنظام إدارة وتسعير ورش الحدادة</p>
      </div>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label fw-bold">البريد الإلكتروني</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="admin@steelquote.com" value="<?= clean($_POST['email'] ?? 'admin@steelquote.com') ?>" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">كلمة المرور</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control" placeholder="******" value="123456" required>
          </div>
        </div>

        <div class="d-grid mb-3">
          <button type="submit" class="btn btn-primary btn-lg fw-bold"><i class="bi bi-box-arrow-in-right me-1"></i> دخول للنظام</button>
        </div>
      </form>

      <div class="text-center border-top pt-3 mt-2">
        <small class="text-muted">ليس لديك حساب؟ </small>
        <a href="register.php" class="fw-bold text-primary">سجل ورشتك الآن (تجربة مجانية)</a>
      </div>

      <div class="alert alert-info mt-3 small mb-0">
        <i class="bi bi-info-circle me-1"></i> <strong>حساب تجريبي للاختبار السريع:</strong><br>
        الإيميل: <code>admin@steelquote.com</code> | كلمة السر: <code>123456</code>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
