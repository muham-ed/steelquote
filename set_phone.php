<?php
require_once 'includes/functions.php';

try {
    $phone = '01060828627';

    // Update admin user phone number
    $stmt = $pdo->prepare("UPDATE users SET phone = ? WHERE role = 'admin'");
    $stmt->execute([$phone]);

    if ($stmt->rowCount() === 0) {
        $stmt2 = $pdo->prepare("UPDATE users SET phone = ? WHERE id = 1");
        $stmt2->execute([$phone]);
    }

    echo "<div style='font-family: Segoe UI, Tahoma, sans-serif; text-align: center; padding: 50px;'>";
    echo "<h2 style='color: #198754;'>✅ تم تثبيت رقم الفودافون كاش (01060828627) رسمياً في قاعدة البيانات على السيرفر!</h2>";
    echo "<p style='color: #555;'>هذا الرقم سيظهر تلقائياً لأي ورشة عند قفل أو تجديد الاشتراكات المالية.</p>";
    echo "<br><a href='login.php' style='display:inline-block; padding: 10px 20px; background: #0d6efd; color:#fff; text-decoration:none; border-radius:5px; font-weight:bold;'>الذهاب لتسجيل الدخول</a>";
    echo "</div>";
} catch (Exception $e) {
    echo "Error updating admin phone: " . $e->getMessage();
}
