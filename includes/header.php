<?php
require_once __DIR__ . '/auth.php';
$current_user = current_user();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($page_title) ? clean($page_title) . ' | SteelQuote' : 'SteelQuote — نظام تسعير ورش الحدادة' ?></title>
    <!-- App Favicon / Browser Icon -->
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/favicon.svg">
    <link rel="shortcut icon" href="<?= BASE_URL ?>assets/favicon.svg">
    <!-- Bootstrap RTL CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold text-warning fs-4" href="<?= BASE_URL ?>index.php">
      <i class="bi bi-tools me-2"></i>SteelQuote
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="تبديل القائمة">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <?php if (is_logged_in()): ?>
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'quotations') === false && strpos($_SERVER['PHP_SELF'], 'materials') === false && strpos($_SERVER['PHP_SELF'], 'customers') === false) ? 'active fw-bold' : '' ?>" href="<?= BASE_URL ?>index.php">
              <i class="bi bi-speedometer2 me-1"></i> لوحة التحكم
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'leads') !== false ? 'active fw-bold' : '' ?>" href="<?= BASE_URL ?>leads.php">
              <i class="bi bi-briefcase me-1"></i> طلبات العملاء (B2B2C)
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'quotations') !== false ? 'active fw-bold' : '' ?>" href="<?= BASE_URL ?>quotations/index.php">
              <i class="bi bi-file-earmark-text me-1"></i> عروض الأسعار
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'calculator') !== false ? 'active fw-bold' : '' ?>" href="<?= BASE_URL ?>calculator.php">
              <i class="bi bi-calculator me-1"></i> حاسبة أوزان الحديد
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'materials') !== false ? 'active fw-bold' : '' ?>" href="<?= BASE_URL ?>materials/index.php">
              <i class="bi bi-box-seam me-1"></i> الخامات
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'customers') !== false ? 'active fw-bold' : '' ?>" href="<?= BASE_URL ?>customers/index.php">
              <i class="bi bi-people me-1"></i> العملاء
            </a>
          </li>
          <?php if (is_admin()): ?>
            <li class="nav-item">
              <a class="nav-link text-danger fw-bold <?= strpos($_SERVER['PHP_SELF'], 'admin') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/index.php">
                <i class="bi bi-shield-lock me-1"></i> لوحة المدير (Super Admin)
              </a>
            </li>
          <?php endif; ?>
        </ul>

        <div class="d-flex align-items-center ms-lg-3 gap-2">
          <a href="<?= BASE_URL ?>quotations/create.php" class="btn btn-warning btn-sm fw-bold">
            <i class="bi bi-plus-lg me-1"></i> عرض سعر جديد
          </a>
          <div class="dropdown">
            <button class="btn btn-outline-light btn-sm dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-building me-1"></i> <?= clean($current_user['workshop_name']) ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>settings.php"><i class="bi bi-gear me-2"></i>إعدادات الورشة واللوجو</a></li>
              <?php if (is_admin()): ?>
                <li><a class="dropdown-item text-danger fw-bold" href="<?= BASE_URL ?>admin/index.php"><i class="bi bi-shield-lock me-2"></i>لوحة مالك النظام</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>تسجيل الخروج</a></li>
            </ul>
          </div>
        </div>
      <?php else: ?>
        <div class="ms-auto">
          <a href="<?= BASE_URL ?>request_quote.php" class="btn btn-outline-warning text-warning fw-bold me-2"><i class="bi bi-tools me-1"></i> طلب تسعير مشروعك</a>
          <a href="<?= BASE_URL ?>login.php" class="btn btn-outline-light me-2">تسجيل الدخول</a>
          <a href="<?= BASE_URL ?>register.php" class="btn btn-warning fw-bold">سجل ورشتك مجاناً</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container mb-5 flex-grow-1">
<?php if ($f = get_flash()): ?>
  <div class="alert alert-<?= $f['type'] === 'error' ? 'danger' : clean($f['type']) ?> alert-dismissible fade show shadow-sm" role="alert">
    <?= clean($f['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
  </div>
<?php endif; ?>
