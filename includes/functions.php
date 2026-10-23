<?php
require_once __DIR__ . '/../config/db.php';

function clean($v) {
    return htmlspecialchars(trim((string)$v), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function set_flash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function generate_quote_number(PDO $pdo) {
    $year = date('Y');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM quotations WHERE YEAR(created_at) = ?");
    $stmt->execute([$year]);
    $count = (int)$stmt->fetchColumn() + 1;
    return sprintf('SQ-%s-%04d', $year, $count);
}

function calculate_selling_price($total_cost, $margin_percent) {
    $total_cost = (float)$total_cost;
    $margin_percent = (float)$margin_percent;
    if ($margin_percent >= 100 || $margin_percent <= 0) {
        return $total_cost + ($total_cost * max(0, $margin_percent) / 100);
    }
    return $total_cost / (1 - ($margin_percent / 100));
}

function money($v) {
    return number_format((float)$v, 2, '.', ',');
}

function get_status_label($status) {
    switch ($status) {
        case 'draft': return 'مسودة';
        case 'sent': return 'مرسل';
        case 'accepted': return 'مقبول';
        case 'rejected': return 'مرفوض';
        default: return $status;
    }
}

function get_status_badge($status) {
    switch ($status) {
        case 'draft':
            return '<span class="badge bg-secondary"><i class="bi bi-pencil-square"></i> مسودة</span>';
        case 'sent':
            return '<span class="badge bg-info text-dark"><i class="bi bi-send"></i> مرسل</span>';
        case 'accepted':
            return '<span class="badge bg-success"><i class="bi bi-check-circle"></i> مقبول</span>';
        case 'rejected':
            return '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> مرفوض</span>';
        default:
            return '<span class="badge bg-light text-dark">' . htmlspecialchars($status) . '</span>';
    }
}
