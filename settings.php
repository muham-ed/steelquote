<?php
require_once 'includes/auth.php';
require_login();

$page_title = 'إعدادات الورشة والحساب';
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name'] ?? '');
    $workshop_name = clean($_POST['workshop_name'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $address = clean($_POST['address'] ?? '');
    $commercial_reg = clean($_POST['commercial_reg'] ?? '');

    $logo_name = $user['logo'];

    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['logo']['tmp_name'];
        $filename = $_FILES['logo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $upload_dir = __DIR__ . '/uploads/logos/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $new_name = 'logo_' . $user['id'] . '_' . time() . '.' . $ext;
            if (move_uploaded_file($tmp, $upload_dir . $new_name)) {
                $logo_name = $new_name;
            }
        } else {
            set_flash('error', 'صيغة الصورة غير مدعومة. استخدم (JPG, PNG, WEBP).');
        }
    }

    if (empty($name) || empty($workshop_name)) {
        set_flash('error', 'اسم مدير الورشة واسم الورشة حقول إجبارية.');
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, workshop_name = ?, phone = ?, address = ?, commercial_reg = ?, logo = ? WHERE id = ?");
        $stmt->execute([$name, $workshop_name, $phone, $address, $commercial_reg, $logo_name, $user['id']]);

        $_SESSION['user_name'] = $name;
        $_SESSION['workshop_name'] = $workshop_name;

        set_flash('success', 'تم حفظ إعدادات الورشة وشعار الهوية بنجاح.');
        redirect('settings.php');
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-gear text-primary me-2"></i> إعدادات الورشة والهوية التجاريـة</h4>
        <p class="text-muted small mb-0">تخصيص بيانات ورشتك واللوجو لتظهر في عروض الأسعار وملفات الـ PDF المطبوعة</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card p-4 shadow-sm">
            <form method="POST" enctype="multipart/form-data">
                <h5 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-building me-1"></i> معلومات الورشة والمؤسسة</h5>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">اسم الورشة / المصنع <span class="text-danger">*</span></label>
                        <input type="text" name="workshop_name" class="form-control" value="<?= clean($user['workshop_name']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">اسم مدير الورشة <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= clean($user['name']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">رقم الهاتف / الواتساب</label>
                        <input type="text" name="phone" class="form-control" value="<?= clean($user['phone'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">السجل التجاري / البطاقة الضريبية (اختياري)</label>
                        <input type="text" name="commercial_reg" class="form-control" placeholder="123456" value="<?= clean($user['commercial_reg'] ?? '') ?>">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold">عنوان الورشة التفصيلي</label>
                        <input type="text" name="address" class="form-control" value="<?= clean($user['address'] ?? '') ?>">
                    </div>
                </div>

                <h5 class="fw-bold text-primary mb-3 border-bottom pb-2 mt-4"><i class="bi bi-image me-1"></i> شعار الورشة (Logo)</h5>

                <div class="row align-items-center mb-4">
                    <div class="col-auto">
                        <?php if (!empty($user['logo']) && file_exists(__DIR__ . '/uploads/logos/' . $user['logo'])): ?>
                            <img src="<?= BASE_URL ?>uploads/logos/<?= clean($user['logo']) ?>" alt="Logo" class="img-thumbnail rounded shadow-sm" style="max-height: 80px;">
                        <?php else: ?>
                            <div class="bg-light border rounded text-center py-3 px-4 text-muted fw-bold">لا يوجد لوجو</div>
                        <?php endif; ?>
                    </div>
                    <div class="col">
                        <label class="form-label fw-semibold">تحميل لوجو جديد للورشة</label>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                        <small class="text-muted">سيظهر اللوجو في الجزء العلوي لعروض الأسعار المطبوعة وقوائم الـ PDF.</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary fw-bold px-4"><i class="bi bi-check-lg me-1"></i> حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 shadow-sm border-start border-warning border-4">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-star me-1 text-warning"></i> حالة الاشتراك</h5>

            <div class="mb-3">
                <span class="text-muted small d-block">نوع الاشتراك الحساب:</span>
                <?php if ($user['subscription_status'] === 'active'): ?>
                    <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> اشتراك نشط كامل</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark fs-6"><i class="bi bi-hourglass-split"></i> فترة تجريبية (Trial)</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($user['trial_ends_at'])): ?>
                <div class="mb-3">
                    <span class="text-muted small d-block">تاريخ انتهاء الفترة التجريبية:</span>
                    <strong class="text-dark"><?= clean($user['trial_ends_at']) ?></strong>
                </div>
            <?php endif; ?>

            <hr>

            <div class="alert alert-info small mb-0">
                <i class="bi bi-headset me-1"></i> <strong>للتجديد والاشتراكات السنوية:</strong><br>
                تواصل مع إدارة SteelQuote عبر واتساب: <code>01012345678</code>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
