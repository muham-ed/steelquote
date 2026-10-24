<?php
require_once 'includes/functions.php';

try {
    $email = 'admin@steelquote.com';
    $password = '123456';
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' OR email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin) {
        $update = $pdo->prepare("UPDATE users SET email = ?, password = ?, role = 'admin', subscription_status = 'active', phone = '01060828627' WHERE id = ?");
        $update->execute([$email, $hashed, $admin['id']]);
    } else {
        $insert = $pdo->prepare("INSERT INTO users (role, name, email, password, workshop_name, phone, subscription_status, trial_ends_at) VALUES ('admin', 'المهندس محمد (مالك المنصة)', ?, ?, 'إدارة منصة SteelQuote', '01060828627', 'active', '2030-12-31')");
        $insert->execute([$email, $hashed]);
    }

    echo "<div style='font-family: Segoe UI, Tahoma, sans-serif; text-align: center; padding: 50px;'>";
    echo "<h2 style='color: #198754;'>✅ تم إصلاح وتحديث حساب مدير النظام بنجاح!</h2>";
    echo "<p style='color: #333; font-size: 18px;'>يمكنك الآن تسجيل الدخول بالبيانات التالية:</p>";
    echo "<div style='background: #f8f9fa; border: 1px solid #ddd; display: inline-block; padding: 15px 30px; border-radius: 8px; text-align: right; margin: 15px 0;'>";
    echo "<b>البريد الإلكتروني:</b> <code style='font-size:16px;'>admin@steelquote.com</code><br>";
    echo "<b>كلمة المرور:</b> <code style='font-size:16px;'>123456</code>";
    echo "</div><br><br>";
    echo "<a href='login.php' style='display:inline-block; padding: 12px 25px; background: #0d6efd; color:#fff; text-decoration:none; border-radius:5px; font-weight:bold;'>الذهاب لتسجيل الدخول الآن</a>";
    echo "</div>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
