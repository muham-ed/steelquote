<?php
require_once 'includes/functions.php';
$page_title = 'طلب تسعير مشروع حدادة جديد';

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_name = clean($_POST['client_name'] ?? '');
    $client_phone = clean($_POST['client_phone'] ?? '');
    $city = clean($_POST['city'] ?? '');
    $project_type = clean($_POST['project_type'] ?? '');
    $description = clean($_POST['description'] ?? '');

    if (empty($client_name) || empty($client_phone) || empty($city) || empty($project_type)) {
        set_flash('error', 'الرجاء إدخال اسمك ورقم الهاتف والمحافظة ونوع المشروع.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO project_leads (client_name, client_phone, city, project_type, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$client_name, $client_phone, $city, $project_type, $description]);
        $success = true;
        set_flash('success', 'تم إرسال طلبك بنجاح! سيتم تحويل طلبك لأفضل الورش المعتمدة في منطقتك والتواصل معك فوراً.');
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center py-4">
  <div class="col-md-7">
    <div class="card shadow-lg p-4 border-0 border-top border-warning border-5">
      <div class="text-center mb-4">
        <div class="fs-1 text-warning"><i class="bi bi-tools"></i></div>
        <h3 class="fw-bold text-dark mb-1">طلب تسعير مشروع تصنيع حديد</h3>
        <p class="text-muted small">احصل على عروض أسعار منافسة واحترافية من أكبر الورش المعتمدة في منطقتك مجاناً</p>
      </div>

      <?php if ($success): ?>
        <div class="alert alert-success text-center py-4 shadow-sm">
          <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
          <h4 class="fw-bold">تم استلام طلب تسعيرك بنجاح!</h4>
          <p class="mb-0 text-muted">سيقوم أفضل معلمين ورش الحدادة المعتمدة والتصنيع المعدني في منطقتك بالاتصال بك وإرسال عروض الأسعار المطبوعة خلال ساعات.</p>
          <a href="index.php" class="btn btn-outline-success mt-3 fw-bold">العودة للرئيسية</a>
        </div>
      <?php else: ?>

        <form method="POST">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">الاسم الكريم <span class="text-danger">*</span></label>
              <input type="text" name="client_name" class="form-control" placeholder="أحمد سليمان" value="<?= clean($_POST['client_name'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">رقم الهاتف / الواتساب للتواصل <span class="text-danger">*</span></label>
              <input type="text" name="client_phone" class="form-control" placeholder="01012345678" value="<?= clean($_POST['client_phone'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">المحافظة / المدينة <span class="text-danger">*</span></label>
              <input type="text" name="city" class="form-control" placeholder="القاهرة - التجمع، الجيزة - 6 أكتوبر..." value="<?= clean($_POST['city'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">نوع المشروع <span class="text-danger">*</span></label>
              <select name="project_type" class="form-select" required>
                <option value="">-- اختر نوع العمل --</option>
                <option value="بوابة حديد فورفورجيه">بوابة حديد فورفورجيه / مودرن</option>
                <option value="مظلة سيارات / صاج">مظلة سيارات صاج ساندوتش بانل</option>
                <option value="جمالون معدني / مخزن">جمالون معدني وهيكل مصنع/مخزن</option>
                <option value="شبابيك وأبواب حماية">شبابيك وحماية حديد</option>
                <option value="سلم حديد طوارئ/ديكوري">سلم حديد طوارئ أو فورفورجيه</option>
                <option value="أخرى">أخرى (اذكرها في التفاصيل)</option>
              </select>
            </div>

            <div class="col-md-12">
              <label class="form-label fw-bold">تفاصيل وأبعاد المشروع (اختياري)</label>
              <textarea name="description" class="form-control" rows="4" placeholder="مثال: مطلوب تصنيع مظلة سيارات 6*4 متر مع القواعد والتثبيت..."></textarea>
            </div>
          </div>

          <div class="d-grid mt-4">
            <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark"><i class="bi bi-send me-1"></i> إرسال الطلب مجاناً لأفضل الورش</button>
          </div>
        </form>

      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
