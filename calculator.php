<?php
require_once 'includes/auth.php';
require_login();

$page_title = 'حاسبة أوزان القطاعات الفولاذية والحديد';

// Save to materials if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_to_materials') {
    $mat_name = clean($_POST['mat_name'] ?? '');
    $mat_unit = clean($_POST['mat_unit'] ?? 'كجم');
    $unit_price = (float)($_POST['unit_price'] ?? 0);
    $notes = clean($_POST['notes'] ?? '');

    if (!empty($mat_name) && $unit_price > 0) {
        $stmt = $pdo->prepare("INSERT INTO materials (user_id, name, unit, unit_price, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([current_user_id(), $mat_name, $mat_unit, $unit_price, $notes]);
        set_flash('success', 'تم حفظ الخامة المحسوبة في قائمة خاماتك بنجاح!');
        redirect('materials/index.php');
    } else {
        set_flash('error', 'يرجى التأكد من اسم الخامة وسعر الوحدة.');
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-calculator text-primary me-2"></i> حاسبة أوزان قطاعات الفولاذ والحديد</h4>
        <p class="text-muted small mb-0">حساب الأوزان الدقيقة للعلب والمواسير والصاج والفورفورجيه وتحويلها لتكلفة إجمالية</p>
    </div>
</div>

<div class="row g-4">
    <!-- Calculator Tabs/Forms -->
    <div class="col-md-7">
        <div class="card p-4 shadow-sm">
            <ul class="nav nav-pills nav-fill mb-4" id="calcTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold" id="box-tab" data-bs-toggle="pill" data-bs-target="#box" type="button">علب ومربعات</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" id="pipe-tab" data-bs-toggle="pill" data-bs-target="#pipe" type="button">مواسير دائرية</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" id="sheet-tab" data-bs-toggle="pill" data-bs-target="#sheet" type="button">ألواح صاج</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" id="solid-tab" data-bs-toggle="pill" data-bs-target="#solid" type="button">حديد صم (فورفورجيه)</button>
                </li>
            </ul>

            <div class="tab-content" id="calcTabContent">
                <!-- Tab 1: Box Profiles -->
                <div class="tab-pane fade show active" id="box">
                    <h6 class="fw-bold text-primary mb-3">حساب أوزان العلب والقطاعات المربعة والمستطيلة:</h6>
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label small fw-semibold">العرض A (مم)</label><input type="number" id="box_w" class="form-control" value="40"></div>
                        <div class="col-6"><label class="form-label small fw-semibold">الارتفاع B (مم)</label><input type="number" id="box_h" class="form-control" value="40"></div>
                        <div class="col-6"><label class="form-label small fw-semibold">السُّمك T (مم)</label><input type="number" step="0.1" id="box_t" class="form-control" value="2"></div>
                        <div class="col-6"><label class="form-label small fw-semibold">طول العود (متر)</label><input type="number" step="0.1" id="box_len" class="form-control" value="6"></div>
                        <div class="col-6"><label class="form-label small fw-semibold">عدد الأعواد</label><input type="number" id="box_qty" class="form-control" value="10"></div>
                        <div class="col-6"><label class="form-label small fw-semibold">سعر كجم الحديد (ج.م)</label><input type="number" id="box_price_kg" class="form-control" value="65"></div>
                    </div>
                </div>

                <!-- Tab 2: Round Pipes -->
                <div class="tab-pane fade" id="pipe">
                    <h6 class="fw-bold text-primary mb-3">حساب أوزان المواسير والأنابيب الدائرية:</h6>
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label small fw-semibold">القطر الخارجي D (مم)</label><input type="number" id="pipe_d" class="form-control" value="50"></div>
                        <div class="col-6"><label class="form-label small fw-semibold">السُّمك T (مم)</label><input type="number" step="0.1" id="pipe_t" class="form-control" value="2.5"></div>
                        <div class="col-6"><label class="form-label small fw-semibold">طول الماسورة (متر)</label><input type="number" step="0.1" id="pipe_len" class="form-control" value="6"></div>
                        <div class="col-6"><label class="form-label small fw-semibold">عدد المواسير</label><input type="number" id="pipe_qty" class="form-control" value="5"></div>
                        <div class="col-12"><label class="form-label small fw-semibold">سعر كجم الحديد (ج.م)</label><input type="number" id="pipe_price_kg" class="form-control" value="65"></div>
                    </div>
                </div>

                <!-- Tab 3: Steel Sheets -->
                <div class="tab-pane fade" id="sheet">
                    <h6 class="fw-bold text-primary mb-3">حساب أوزان ألواح الصاج المسحوب والمجلفن:</h6>
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label small fw-semibold">العرض (متر)</label><input type="number" step="0.01" id="sheet_w" class="form-control" value="1.22"></div>
                        <div class="col-6"><label class="form-label small fw-semibold">الطول (متر)</label><input type="number" step="0.01" id="sheet_l" class="form-control" value="2.44"></div>
                        <div class="col-6"><label class="form-label small fw-semibold">السُّمك T (مم)</label><input type="number" step="0.1" id="sheet_t" class="form-control" value="1.5"></div>
                        <div class="col-6"><label class="form-label small fw-semibold">عدد الألواح</label><input type="number" id="sheet_qty" class="form-control" value="4"></div>
                        <div class="col-12"><label class="form-label small fw-semibold">سعر كجم الصاج (ج.م)</label><input type="number" id="sheet_price_kg" class="form-control" value="70"></div>
                    </div>
                </div>

                <!-- Tab 4: Solid Steel -->
                <div class="tab-pane fade" id="solid">
                    <h6 class="fw-bold text-primary mb-3">حساب أوزان الحديد المصمت والفورفورجيه (مربعات وخوص):</h6>
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label small fw-semibold">ضلع المربع / القطر (مم)</label><input type="number" id="solid_size" class="form-control" value="12"></div>
                        <div class="col-6"><label class="form-label small fw-semibold">طول العود (متر)</label><input type="number" step="0.1" id="solid_len" class="form-control" value="6"></div>
                        <div class="col-6"><label class="form-label small fw-semibold">عدد الأسياخ/الأعواد</label><input type="number" id="solid_qty" class="form-control" value="20"></div>
                        <div class="col-6"><label class="form-label small fw-semibold">سعر كجم الحديد (ج.م)</label><input type="number" id="solid_price_kg" class="form-control" value="65"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Output & Save to Materials -->
    <div class="col-md-5">
        <div class="card p-4 shadow-sm border-primary">
            <h5 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-calculator-fill me-1"></i> النتيجة المحسوبة</h5>

            <div class="bg-light p-3 rounded mb-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted fw-semibold">الوزن الإجمالي للكمية:</span>
                    <span class="fw-bold fs-5 text-dark"><span id="res_weight">0.00</span> كجم</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted fw-semibold">الوزن بالطن:</span>
                    <span class="fw-bold text-info"><span id="res_tons">0.000</span> طن</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between text-success">
                    <span class="fw-bold fs-6">التكلفة الإجمالية المقدرة:</span>
                    <span class="fw-bold fs-4"><span id="res_cost">0.00</span> ج.م</span>
                </div>
            </div>

            <!-- Form to save directly as a Material -->
            <form method="POST" class="border-top pt-3">
                <input type="hidden" name="action" value="save_to_materials">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-plus-square me-1"></i> حفظ هذه الخامة في قائمة خاماتك:</h6>

                <div class="mb-2">
                    <label class="form-label small fw-semibold">اسم الخامة</label>
                    <input type="text" name="mat_name" id="mat_name_input" class="form-control form-control-sm" value="علب حديد 40×40 مم (2 مم)" required>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">وحدة التسعير</label>
                        <select name="mat_unit" class="form-select form-select-sm">
                            <option value="كجم">كجم</option>
                            <option value="عود 6 متر">عود 6 متر</option>
                            <option value="لوح">لوح</option>
                            <option value="متر طولي">متر طولي</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">سعر الوحدة (ج.م)</label>
                        <input type="number" step="0.01" name="unit_price" id="mat_price_input" class="form-control form-control-sm" value="65.00" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold"><i class="bi bi-save me-1"></i> + إضافة لقائمة الخامات</button>
            </form>
        </div>
    </div>
</div>

<script>
function calculateSteel() {
    const activeTab = document.querySelector('#calcTabs .active').id;
    let weightKg = 0;
    let pricePerKg = 0;
    let defaultName = '';
    let unitPriceVal = 0;

    if (activeTab === 'box-tab') {
        const w = parseFloat(document.getElementById('box_w').value) || 0;
        const h = parseFloat(document.getElementById('box_h').value) || 0;
        const t = parseFloat(document.getElementById('box_t').value) || 0;
        const len = parseFloat(document.getElementById('box_len').value) || 0;
        const qty = parseFloat(document.getElementById('box_qty').value) || 0;
        pricePerKg = parseFloat(document.getElementById('box_price_kg').value) || 0;

        // Formula for hollow rectangular tube weight = 0.02466 * t * (w + h - 2*t) * len / 1000 per meter * len
        const weightPerMeter = 0.02466 * t * (w + h - (2 * t));
        weightKg = weightPerMeter * len * qty;

        defaultName = `علب حديد ${w}×${h} مم (سُمك ${t} مم)`;
        unitPriceVal = pricePerKg;
    } else if (activeTab === 'pipe-tab') {
        const d = parseFloat(document.getElementById('pipe_d').value) || 0;
        const t = parseFloat(document.getElementById('pipe_t').value) || 0;
        const len = parseFloat(document.getElementById('pipe_len').value) || 0;
        const qty = parseFloat(document.getElementById('pipe_qty').value) || 0;
        pricePerKg = parseFloat(document.getElementById('pipe_price_kg').value) || 0;

        const weightPerMeter = 0.02466 * t * (d - t);
        weightKg = weightPerMeter * len * qty;

        defaultName = `ماسورة دائرية قطر ${d} مم (سُمك ${t} مم)`;
        unitPriceVal = pricePerKg;
    } else if (activeTab === 'sheet-tab') {
        const w = parseFloat(document.getElementById('sheet_w').value) || 0;
        const l = parseFloat(document.getElementById('sheet_l').value) || 0;
        const t = parseFloat(document.getElementById('sheet_t').value) || 0;
        const qty = parseFloat(document.getElementById('sheet_qty').value) || 0;
        pricePerKg = parseFloat(document.getElementById('sheet_price_kg').value) || 0;

        const weightPerSheet = w * l * t * 7.85;
        weightKg = weightPerSheet * qty;

        defaultName = `لوح صاج ${w}×${l} متر (سُمك ${t} مم)`;
        unitPriceVal = pricePerKg;
    } else if (activeTab === 'solid-tab') {
        const size = parseFloat(document.getElementById('solid_size').value) || 0;
        const len = parseFloat(document.getElementById('solid_len').value) || 0;
        const qty = parseFloat(document.getElementById('solid_qty').value) || 0;
        pricePerKg = parseFloat(document.getElementById('solid_price_kg').value) || 0;

        const weightPerMeter = (size * size * 0.00617);
        weightKg = weightPerMeter * len * qty;

        defaultName = `حديد مربع مصمت ${size} مم`;
        unitPriceVal = pricePerKg;
    }

    const totalCost = weightKg * pricePerKg;
    const tons = weightKg / 1000;

    document.getElementById('res_weight').textContent = weightKg.toFixed(2);
    document.getElementById('res_tons').textContent = tons.toFixed(3);
    document.getElementById('res_cost').textContent = totalCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    document.getElementById('mat_name_input').value = defaultName;
    document.getElementById('mat_price_input').value = unitPriceVal.toFixed(2);
}

document.querySelectorAll('input').forEach(input => {
    input.addEventListener('input', calculateSteel);
});

document.querySelectorAll('#calcTabs button').forEach(button => {
    button.addEventListener('shown.bs.tab', calculateSteel);
});

document.addEventListener('DOMContentLoaded', calculateSteel);
</script>

<?php include 'includes/footer.php'; ?>
