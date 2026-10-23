<?php
require_once '../includes/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$user = current_user();

// Fetch quotation details
$stmt = $pdo->prepare("SELECT q.*, c.name AS customer_name, c.phone AS customer_phone, c.email AS customer_email, c.address AS customer_address
                       FROM quotations q
                       JOIN customers c ON c.id = q.customer_id
                       WHERE q.id = ? AND q.user_id = ?");
$stmt->execute([$id, current_user_id()]);
$quote = $stmt->fetch();

if (!$quote) {
    die('عرض السعر غير موجود أو لا تملك صلاحية الوصول إليه.');
}

// Fetch items
$item_stmt = $pdo->prepare("SELECT * FROM quotation_items WHERE quotation_id = ? ORDER BY id ASC");
$item_stmt->execute([$id]);
$items = $item_stmt->fetchAll();

$remaining_amount = max(0, $quote['selling_price'] - $quote['deposit_amount']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض سعر - <?= clean($quote['quote_number']) ?></title>
    <!-- Bootstrap RTL CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            background-color: #f4f6f9;
            color: #2b2b2b;
            padding: 20px 0;
        }
        .pdf-page {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        .header-brand {
            border-bottom: 3px double #0d6efd;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .shop-title {
            color: #0d6efd;
            font-weight: 800;
            font-size: 24px;
        }
        .info-box {
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 15px;
            border-right: 4px solid #0d6efd;
            height: 100%;
        }
        .table-items thead th {
            background-color: #0d6efd !important;
            color: #ffffff !important;
            font-weight: 600;
            text-align: center;
        }
        .table-items td {
            vertical-align: middle;
        }
        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 15px;
        }
        .signature-box {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px dashed #cbd5e1;
        }

        @media print {
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
            }
            .pdf-page {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4;
                margin: 15mm;
            }
        }
    </style>
</head>
<body>

<!-- Control Buttons (Hidden on Print) -->
<div class="container max-w-800 no-print mb-3 text-center" style="max-width: 800px;">
    <button onclick="window.print()" class="btn btn-primary btn-lg fw-bold me-2">
        <i class="bi bi-printer me-1"></i> طباعة / حفظ كـ PDF
    </button>
    <a href="view.php?id=<?= $quote['id'] ?>" class="btn btn-outline-secondary btn-lg">
        <i class="bi bi-arrow-right me-1"></i> رجوع للعرض
    </a>
</div>

<!-- Main Printable PDF Layout -->
<div class="pdf-page">
    <!-- Workshop Header & Logo -->
    <div class="header-brand">
        <div class="row align-items-center">
            <div class="col-8">
                <div class="d-flex align-items-center gap-3">
                    <?php if (!empty($user['logo']) && file_exists(__DIR__ . '/../uploads/logos/' . $user['logo'])): ?>
                        <img src="<?= BASE_URL ?>uploads/logos/<?= clean($user['logo']) ?>" style="max-height: 70px; max-width: 120px;" alt="Logo">
                    <?php endif; ?>
                    <div>
                        <div class="shop-title"><i class="bi bi-tools me-2"></i><?= clean($user['workshop_name']) ?></div>
                        <div class="text-muted small mt-1">
                            <i class="bi bi-geo-alt"></i> <?= clean($user['address'] ?? 'غير محدد') ?> |
                            <i class="bi bi-telephone"></i> <?= clean($user['phone'] ?? 'غير محدد') ?>
                            <?php if (!empty($user['commercial_reg'])): ?>
                                | س.ت: <?= clean($user['commercial_reg']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-4 text-end">
                <div class="badge bg-primary fs-6 px-3 py-2 mb-1">عرض سعر / Q-OFFER</div>
                <div class="fw-bold text-dark fs-5"><?= clean($quote['quote_number']) ?></div>
                <div class="text-muted small">التاريخ: <?= date('Y-m-d', strtotime($quote['created_at'])) ?></div>
            </div>
        </div>
    </div>

    <!-- Info Boxes: Customer & Project -->
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="info-box">
                <div class="text-primary fw-bold mb-2"><i class="bi bi-person-circle me-1"></i> العميل المحترم:</div>
                <div class="fw-bold fs-6 text-dark"><?= clean($quote['customer_name']) ?></div>
                <?php if (!empty($quote['customer_phone'])): ?>
                    <div class="small text-muted"><i class="bi bi-telephone me-1"></i> <?= clean($quote['customer_phone']) ?></div>
                <?php endif; ?>
                <?php if (!empty($quote['customer_address'])): ?>
                    <div class="small text-muted"><i class="bi bi-geo-alt me-1"></i> <?= clean($quote['customer_address']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-6">
            <div class="info-box border-info" style="border-right-color: #0dcaf0;">
                <div class="text-info fw-bold mb-2"><i class="bi bi-building me-1"></i> تفاصيل المشروع:</div>
                <div class="fw-bold fs-6 text-dark"><?= clean($quote['project_name']) ?></div>
                <div class="small text-muted mt-1"><i class="bi bi-calendar-check me-1"></i> العرض صالح حتى: <?= !empty($quote['valid_until']) ? clean($quote['valid_until']) : '15 يوماً من تاريخه' ?></div>
                <div class="small text-muted"><i class="bi bi-patch-check me-1"></i> حالة العرض: <?= get_status_label($quote['status']) ?></div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="mb-4">
        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-list-ol me-1"></i> بيان الخامات والأعمال المطلوب تنفيذها:</h6>
        <table class="table table-bordered table-items align-middle">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 45%;">الوصف والبند</th>
                    <th style="width: 15%;">الوحدة</th>
                    <th style="width: 10%;">الكمية</th>
                    <th style="width: 12%;">السعر (ج.م)</th>
                    <th style="width: 13%;">الإجمالي (ج.م)</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($items as $item): ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $i++ ?></td>
                        <td>
                            <div class="fw-bold text-dark"><?= clean($item['item_description']) ?></div>
                        </td>
                        <td class="text-center"><?= clean($item['unit']) ?></td>
                        <td class="text-center fw-bold"><?= number_format((float)$item['quantity'], 2) ?></td>
                        <td class="text-end"><?= money($item['unit_price']) ?></td>
                        <td class="text-end fw-bold"><?= money($item['total_price']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Total Price Summary & Terms -->
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="p-3 bg-light rounded border h-100">
                <div class="fw-bold text-dark mb-2"><i class="bi bi-shield-check me-1"></i> الشروط والملاحظات:</div>
                <div class="small text-muted" style="white-space: pre-line; min-height: 80px;">
                    <?= !empty($quote['notes']) ? clean($quote['notes']) : "1. الأسعار تشمل التوريد والتركيب والدهانات.\n2. دفعة مقدمة (عربون) عند التعاقد والباقي عند التسليم.\n3. يسري هذا العرض حتى التاريخ الموضح أعلاه." ?>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="summary-card text-end">
                <div class="d-flex justify-content-between text-muted small mb-1">
                    <span>السعر الإجمالي المطلوب:</span>
                    <span class="fw-bold text-dark"><?= money($quote['selling_price']) ?> ج.م</span>
                </div>
                <div class="d-flex justify-content-between text-muted small mb-1">
                    <span>الدفعة المقدمة (العربون):</span>
                    <span class="fw-bold text-success"><?= money($quote['deposit_amount']) ?> ج.م</span>
                </div>
                <div class="d-flex justify-content-between text-muted small mb-1">
                    <span>المبلغ المتبقي عند الاستلام:</span>
                    <span class="fw-bold text-danger"><?= money($remaining_amount) ?> ج.م</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between fw-bold text-dark fs-5 p-2 bg-primary bg-opacity-10 rounded">
                    <span>الإجمالي:</span>
                    <span class="text-primary"><?= money($quote['selling_price']) ?> ج.م</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signature-box">
        <div class="row text-center">
            <div class="col-6">
                <div class="fw-bold text-dark mb-4">موافقة العميل / المستلم</div>
                <div class="text-muted small">التوقيع: ........................</div>
            </div>
            <div class="col-6">
                <div class="fw-bold text-primary mb-4">إدارة <?= clean($user['workshop_name']) ?></div>
                <div class="text-muted small">التوقيع والختم: ........................</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
