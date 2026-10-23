<?php
require_once 'includes/functions.php';
$page_title = 'تسجيل ورشة جديدة';

if (is_logged_in()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name'] ?? '');
    $workshop_name = clean($_POST['workshop_name'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $address = clean($_POST['address'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($workshop_name) || empty($email) || empty($password)) {
        set_flash('error', 'الرجاء إدخال جميع الحقول المطلوبة.');
    } elseif ($password !== $confirm_password) {
        set_flash('error', 'كلمتا المرور غير متطابقتين.');
    } elseif (strlen($password) < 6) {
        set_flash('error', 'كلمة المرور يجب أن لا تقل عن 6 أحرف.');
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            set_flash('error', 'البريد الإلكتروني مسجل بالفعل. حاول تسجيل الدخول.');
        } else {
            try {
                $pdo->beginTransaction();

                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $trial_ends = date('Y-m-d', strtotime('+14 days'));

                $stmt = $pdo->prepare("INSERT INTO users (name, workshop_name, phone, address, email, password, subscription_status, trial_ends_at) VALUES (?, ?, ?, ?, ?, ?, 'trial', ?)");
                $stmt->execute([$name, $workshop_name, $phone, $address, $email, $hashed, $trial_ends]);
                $user_id = $pdo->lastInsertId();

                // Populate starter materials for the new workshop
                $starter_materials = [
                    ['علب حديد 4×4 سم (سُمك 2 مم)', 'متر طولي', 85.00, 'قطاعات مربعة للمظلات والهياكل'],
                    ['علب حديد 8×4 سم (سُمك 2.5 مم)', 'متر طولي', 140.00, 'للأعمدة والإطارات الرئيسية'],
                    ['صاج معالج معزول (ساندوتش بانل 5سم)', 'متر مربع', 450.00, 'لتغطية الأسقف والجمالونات'],
                    ['زوايا حديد 5×5 سم (سُمك 4 مم)', 'متر طولي', 95.00, 'للتدعيم والتثبيت'],
                    ['حديد فورفورجيه مشكل (مزخرف)', 'كجم', 65.00, 'للبوابات والشبابيك الديكورية'],
                    ['دهان بريمر مانع للصدأ', 'جالون', 350.00, 'دهان تأسيس لحماية الحديد'],
                    ['سلك لحام 6013 (عبوة 5 كجم)', 'علبة', 280.00, 'مستهلكات لحام']
                ];

                $mat_stmt = $pdo->prepare("INSERT INTO materials (user_id, name, unit, unit_price, notes) VALUES (?, ?, ?, ?, ?)");
                foreach ($starter_materials as $m) {
                    $mat_stmt->execute([$user_id, $m[0], $m[1], $m[2], $m[3]]);
                }

                $pdo->commit();

                // Log in new user
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['workshop_name'] = $workshop_name;

                set_flash('success', "مرحباً بك يا معلم $name! تم تسجيل ورشتك بنجاح وبدء الفترة التجريبية المجانية (14 يوم).");
                redirect('index.php');
            } catch (Exception $e) {
                $pdo->rollBack();
                set_flash('error', 'حدث خطأ أثناء التسجيل: ' . $e->getMessage());
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center py-4">
  <div class="col-md-7">
    <div class="card shadow-lg p-4 border-0">
      <div class="text-center mb-4">
        <div class="fs-1 text-primary"><i class="bi bi-tools"></i></div>
        <h4 class="fw-bold text-dark mb-1">تسجيل ورشة جديدة في SteelQuote</h4>
        <p class="text-muted small">احصل على تجربة مجانية لمدة 14 يوماً وابدأ بتسعير أعمـالك باحترافية</p>
      </div>

      <form method="POST">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-bold">اسم المعلم / مدير الورشة <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="أحمد سليمان" value="<?= clean($_POST['name'] ?? '') ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">اسم الورشة / المصنع <span class="text-danger">*</span></label>
            <input type="text" name="workshop_name" class="form-control" placeholder="ورشة النجم الذهبي للحدادة" value="<?= clean($_POST['workshop_name'] ?? '') ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">رقم الهاتف / الواتساب</label>
            <input type="text" name="phone" class="form-control" placeholder="01012345678" value="<?= clean($_POST['phone'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">عنوان الورشة</label>
            <input type="text" name="address" class="form-control" placeholder="القاهرة - المنطقة الصناعية" value="<?= clean($_POST['address'] ?? '') ?>">
          </div>

          <div class="col-md-12">
            <label class="form-label fw-bold">البريد الإلكتروني (لتسجيل الدخول) <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" placeholder="workshop@example.com" value="<?= clean($_POST['email'] ?? '') ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">كلمة المرور <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" placeholder="6 أحرف على الأقل" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">تأكيد كلمة المرور <span class="text-danger">*</span></label>
            <input type="password" name="confirm_password" class="form-control" placeholder="أعد كتابة كلمة المرور" required>
          </div>
        </div>

        <div class="d-grid mt-4">
          <button type="submit" class="btn btn-primary btn-lg fw-bold"><i class="bi bi-person-check me-1"></i> إنشـاء الحساب وبدء التجربة المجانية</button>
        </div>
      </form>

      <div class="text-center border-top pt-3 mt-3">
        <small class="text-muted">لديك حساب بالفعل؟ </small>
        <a href="login.php" class="fw-bold text-primary">تسجيل الدخول</a>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
