<?php
require_once '../includes/auth.php';
require_admin();

$page_title = 'لوحة تحكم مالك المنصة (Super Admin)';

// Handle Subscription Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $target_id = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'];

    if ($target_id > 0) {
        if ($action === 'activate_month') {
            $ends = date('Y-m-d', strtotime('+1 month'));
            $stmt = $pdo->prepare("UPDATE users SET subscription_status = 'active', trial_ends_at = ? WHERE id = ?");
            $stmt->execute([$ends, $target_id]);
            set_flash('success', 'تم تفعيل اشتراك الورشة لمدة شهر بنجاح.');
        } elseif ($action === 'activate_year') {
            $ends = date('Y-m-d', strtotime('+1 year'));
            $stmt = $pdo->prepare("UPDATE users SET subscription_status = 'active', trial_ends_at = ? WHERE id = ?");
            $stmt->execute([$ends, $target_id]);
            set_flash('success', 'تم تفعيل اشتراك الورشة لمدة سنة كاملة بنجاح!');
        } elseif ($action === 'suspend') {
            $stmt = $pdo->prepare("UPDATE users SET subscription_status = 'suspended' WHERE id = ?");
            $stmt->execute([$target_id]);
            set_flash('error', 'تم تعليق/إيقاف الورشة وقفول حسابها بنجاح.');
        } elseif ($action === 'extend_trial') {
            $ends = date('Y-m-d', strtotime('+14 days'));
            $stmt = $pdo->prepare("UPDATE users SET subscription_status = 'trial', trial_ends_at = ? WHERE id = ?");
            $stmt->execute([$ends, $target_id]);
            set_flash('success', 'تم تمديد الفترة التجريبية للورشة لمدة 14 يوماً.');
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
            $stmt->execute([$target_id]);
            set_flash('success', 'تم حذف الورشة وجميع بياناتها بنجاح.');
        }
    }
    redirect('index.php');
}

// Key Admin Stats
$totalWorkshops = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$activeWorkshops = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND subscription_status = 'active'")->fetchColumn();
$trialWorkshops = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND subscription_status = 'trial'")->fetchColumn();
$suspendedWorkshops = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND subscription_status IN ('expired', 'suspended')")->fetchColumn();

// Fetch all workshops
$workshops = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC")->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1"><i class="bi bi-shield-lock-fill text-danger me-2"></i>لوحة الإدارة والتحكم الكامل للـ Super Admin</h4>
        <p class="text-muted small mb-0">إدارة الاشتراكات، تفعيل الورش، وتحصيل الأرباح المالية</p>
    </div>
</div>

<!-- Admin Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 border-start border-primary border-4 shadow-sm">
            <div class="text-muted small fw-semibold">إجمالي الورش المسجلة</div>
            <div class="h3 fw-bold mb-0 text-dark"><?= $totalWorkshops ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-start border-success border-4 shadow-sm">
            <div class="text-muted small fw-semibold">اشتراكات نشطة (مدفوعة)</div>
            <div class="h3 fw-bold mb-0 text-success"><?= $activeWorkshops ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-start border-warning border-4 shadow-sm">
            <div class="text-muted small fw-semibold">فترات تجريبية (Trial)</div>
            <div class="h3 fw-bold mb-0 text-warning"><?= $trialWorkshops ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-start border-danger border-4 shadow-sm">
            <div class="text-muted small fw-semibold">ورش معلقة / غير مدفوعة</div>
            <div class="h3 fw-bold mb-0 text-danger"><?= $suspendedWorkshops ?></div>
        </div>
    </div>
</div>

<!-- Workshops Management Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 fw-bold text-primary">
        <i class="bi bi-building me-1"></i> قائمة أصحاب الورش والتحكم المالي بالاشتراكات
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>اسم الورشة</th>
                    <th>اسم صاحب الورشة</th>
                    <th>الواتساب / الإيميل</th>
                    <th>حالة الاشتراك</th>
                    <th>ينتهي في</th>
                    <th>تاريخ التسجيل</th>
                    <th class="text-center" style="width: 25%;">التحكم بالاشتراك</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($workshops)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">لا توجد ورش مسجلة بعد.</td></tr>
                <?php else: foreach ($workshops as $w): ?>
                    <tr>
                        <td><?= $w['id'] ?></td>
                        <td class="fw-bold text-dark"><?= clean($w['workshop_name']) ?></td>
                        <td><?= clean($w['name']) ?></td>
                        <td>
                            <?php if (!empty($w['phone'])): ?>
                                <a href="https://api.whatsapp.com/send?phone=<?= preg_replace('/[^0-9]/', '', $w['phone']) ?>" target="_blank" class="text-success fw-bold text-decoration-none d-block">
                                    <i class="bi bi-whatsapp"></i> <?= clean($w['phone']) ?>
                                </a>
                            <?php endif; ?>
                            <small class="text-muted"><?= clean($w['email']) ?></small>
                        </td>
                        <td>
                            <?php if ($w['subscription_status'] === 'active'): ?>
                                <span class="badge bg-success">اشتراك نشط</span>
                            <?php elseif ($w['subscription_status'] === 'trial'): ?>
                                <span class="badge bg-warning text-dark">تجريبي (Trial)</span>
                            <?php else: ?>
                                <span class="badge bg-danger">معلق / متوقف</span>
                            <?php endif; ?>
                        </td>
                        <td class="small fw-semibold"><?= !empty($w['trial_ends_at']) ? clean($w['trial_ends_at']) : '-' ?></td>
                        <td class="small text-muted"><?= date('Y-m-d', strtotime($w['created_at'])) ?></td>
                        <td class="text-center">
                            <form method="POST" class="d-inline-flex gap-1 flex-wrap justify-content-center">
                                <input type="hidden" name="user_id" value="<?= $w['id'] ?>">

                                <button type="submit" name="action" value="activate_month" class="btn btn-xs btn-success text-white" title="تفعيل شهر واحد">+ شهر</button>
                                <button type="submit" name="action" value="activate_year" class="btn btn-xs btn-primary" title="تفعيل سنة كاملة">+ سنة</button>
                                <button type="submit" name="action" value="suspend" class="btn btn-xs btn-warning text-dark" title="إيقاف وقفل الحساب"><i class="bi bi-lock"></i> قفل</button>
                                <button type="submit" name="action" value="delete" class="btn btn-xs btn-danger" onclick="return confirm('هل أنت متأكد من حذف الورشة وجميع بياناتها؟');" title="حذف"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
