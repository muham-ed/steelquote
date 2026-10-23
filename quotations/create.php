<?php
require_once '../includes/auth.php';
require_login();

$page_title = 'إنشاء عرض سعر جديد';
$user_id = current_user_id();

// Fetch materials and customers scoped by user_id
$materials = $pdo->prepare("SELECT * FROM materials WHERE user_id = ? ORDER BY name ASC");
$materials->execute([$user_id]);
$materials = $materials->fetchAll();

$customers = $pdo->prepare("SELECT * FROM customers WHERE user_id = ? ORDER BY name ASC");
$customers->execute([$user_id]);
$customers = $customers->fetchAll();

$quote_num = generate_quote_number($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $project_name = clean($_POST['project_name'] ?? '');
    $status = clean($_POST['status'] ?? 'draft');
    $valid_until = !empty($_POST['valid_until']) ? clean($_POST['valid_until']) : null;
    $notes = clean($_POST['notes'] ?? '');

    $labor_cost = (float)($_POST['labor_cost'] ?? 0);
    $overhead_cost = (float)($_POST['overhead_cost'] ?? 0);
    $profit_margin_percent = (float)($_POST['profit_margin_percent'] ?? 0);
    $deposit_amount = (float)($_POST['deposit_amount'] ?? 0);

    // Items array from form
    $item_materials = $_POST['item_material_id'] ?? [];
    $item_descriptions = $_POST['item_description'] ?? [];
    $item_units = $_POST['item_unit'] ?? [];
    $item_quantities = $_POST['item_quantity'] ?? [];
    $item_prices = $_POST['item_unit_price'] ?? [];

    $materials_cost = 0;
    $valid_items = [];

    for ($i = 0; $i < count($item_descriptions); $i++) {
        $desc = clean($item_descriptions[$i] ?? '');
        $unit = clean($item_units[$i] ?? '');
        $qty = (float)($item_quantities[$i] ?? 0);
        $price = (float)($item_prices[$i] ?? 0);
        $mat_id = !empty($item_materials[$i]) ? (int)$item_materials[$i] : null;

        if ($desc !== '' && $qty > 0 && $price >= 0) {
            $total_price = $qty * $price;
            $materials_cost += $total_price;
            $valid_items[] = [
                'material_id' => $mat_id,
                'description' => $desc,
                'unit' => $unit,
                'quantity' => $qty,
                'unit_price' => $price,
                'total_price' => $total_price
            ];
        }
    }

    if ($customer_id <= 0) {
        set_flash('error', 'الرجاء اختيار العميل.');
    } elseif (empty($project_name)) {
        set_flash('error', 'الرجاء إدخال اسم المشروع.');
    } elseif (empty($valid_items)) {
        set_flash('error', 'الرجاء إضافة بند واحد على الأقل يحتوي على كمية وسعر صحيحين.');
    } else {
        // Calculate totals
        $total_cost = $materials_cost + $labor_cost + $overhead_cost;
        $selling_price = calculate_selling_price($total_cost, $profit_margin_percent);
        $profit_amount = $selling_price - $total_cost;

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO quotations (user_id, quote_number, customer_id, project_name, labor_cost, overhead_cost, materials_cost, total_cost, profit_margin_percent, profit_amount, selling_price, deposit_amount, status, valid_until, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                $quote_num,
                $customer_id,
                $project_name,
                $labor_cost,
                $overhead_cost,
                $materials_cost,
                $total_cost,
                $profit_margin_percent,
                $profit_amount,
                $selling_price,
                $deposit_amount,
                $status,
                $valid_until,
                $notes
            ]);

            $quotation_id = $pdo->lastInsertId();

            $item_stmt = $pdo->prepare("INSERT INTO quotation_items (quotation_id, material_id, item_description, unit, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($valid_items as $item) {
                $item_stmt->execute([
                    $quotation_id,
                    $item['material_id'],
                    $item['description'],
                    $item['unit'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['total_price']
                ]);
            }

            $pdo->commit();
            set_flash('success', 'تم إنشاء عرض السعر بنجاح برقم: ' . $quote_num);
            redirect('view.php?id=' . $quotation_id);
        } catch (Exception $e) {
            $pdo->rollBack();
            set_flash('error', 'حدث خطأ أثناء حفظ عرض السعر: ' . $e->getMessage());
        }
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4><i class="bi bi-file-earmark-plus text-primary me-2"></i>إنشاء عرض سعر جديد (<?= $quote_num ?>)</h4>
  <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right"></i> إلغاء ورجوع</a>
</div>

<form method="POST" id="quoteForm">
  <!-- Section 1: General Info -->
  <div class="card p-4 mb-4 shadow-sm">
    <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary"><i class="bi bi-info-circle me-1"></i> المعلومات الأساسية</h5>
    <div class="row g-3">
      <div class="col-md-5">
        <label class="form-label fw-bold">العميل <span class="text-danger">*</span></label>
        <div class="input-group">
          <select name="customer_id" class="form-select" required>
            <option value="">-- اختر العميل --</option>
            <?php foreach ($customers as $c): ?>
              <option value="<?= $c['id'] ?>" <?= (isset($_POST['customer_id']) && $_POST['customer_id'] == $c['id']) ? 'selected' : '' ?>>
                <?= clean($c['name']) ?> <?= !empty($c['phone']) ? '('.clean($c['phone']).')' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
          <a href="../customers/create.php" target="_blank" class="btn btn-outline-secondary" title="إضافة عميل جديد"><i class="bi bi-person-plus"></i></a>
        </div>
      </div>

      <div class="col-md-7">
        <label class="form-label fw-bold">اسم المشروع / وصف العمل <span class="text-danger">*</span></label>
        <input type="text" name="project_name" class="form-control" placeholder="مثال: تصنيع وتركيب بوابات وحديد فورفورجيه" value="<?= clean($_POST['project_name'] ?? '') ?>" required>
      </div>

      <div class="col-md-4">
        <label class="form-label fw-bold">حالة العرض</label>
        <select name="status" class="form-select">
          <option value="draft">مسودة</option>
          <option value="sent">مرسل للعميل</option>
          <option value="accepted">مقبول</option>
          <option value="rejected">مرفوض</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label fw-bold">تاريخ صلاحية العرض حتى</label>
        <input type="date" name="valid_until" class="form-control" value="<?= date('Y-m-d', strtotime('+15 days')) ?>">
      </div>
    </div>
  </div>

  <!-- Section 2: Items Table -->
  <div class="card p-4 mb-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
      <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-box-seam me-1"></i> بنود الخامات والمستلزمات</h5>
      <button type="button" class="btn btn-success btn-sm" id="addRowBtn"><i class="bi bi-plus-circle me-1"></i> إضافة بند خامة</button>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered align-middle" id="itemsTable">
        <thead class="table-light text-center">
          <tr>
            <th style="width: 28%;">اختر من قائمة الخامات</th>
            <th style="width: 25%;">اسم/وصف البند</th>
            <th style="width: 12%;">الوحدة</th>
            <th style="width: 10%;">الكمية</th>
            <th style="width: 12%;">سعر الوحدة (ج.م)</th>
            <th style="width: 13%;">الإجمالي (ج.م)</th>
            <th style="width: 5%;"></th>
          </tr>
        </thead>
        <tbody id="itemsBody">
          <!-- Dynamic rows inserted via JS -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Section 3: Cost Calculator & Margin -->
  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="card p-4 h-100 shadow-sm">
        <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary"><i class="bi bi-chat-left-text me-1"></i> شروط وملاحظات العرض</h5>
        <textarea name="notes" class="form-control" rows="8" placeholder="أدخل أي ملاحظات، شروط الدفع، مدة التوريد، أو تفاصيل الضمان..."></textarea>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card p-4 bg-white shadow-sm border-primary">
        <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary"><i class="bi bi-calculator me-1"></i> حاسبة التكلفة والتسعير المتكاملة</h5>

        <div class="row g-2 align-items-center mb-2">
          <div class="col-6"><label class="fw-semibold">إجمالي الخامات:</label></div>
          <div class="col-6 text-end"><span id="materialsCostDisplay" class="fw-bold fs-6">0.00</span> ج.م</div>
        </div>

        <div class="row g-2 align-items-center mb-2">
          <div class="col-6"><label class="fw-semibold" for="labor_cost">تكلفة المصنعية / العمالة:</label></div>
          <div class="col-6">
            <div class="input-group input-group-sm">
              <input type="number" step="0.01" min="0" name="labor_cost" id="labor_cost" class="form-control text-end" value="0.00">
              <span class="input-group-text">ج.م</span>
            </div>
          </div>
        </div>

        <div class="row g-2 align-items-center mb-2">
          <div class="col-6"><label class="fw-semibold" for="overhead_cost">مصاريف إدارية / نقل / أخرى:</label></div>
          <div class="col-6">
            <div class="input-group input-group-sm">
              <input type="number" step="0.01" min="0" name="overhead_cost" id="overhead_cost" class="form-control text-end" value="0.00">
              <span class="input-group-text">ج.م</span>
            </div>
          </div>
        </div>

        <hr>

        <div class="row g-2 align-items-center mb-2 bg-light p-2 rounded">
          <div class="col-6"><label class="fw-bold text-dark">إجمالي التكلفة الحقيقية:</label></div>
          <div class="col-6 text-end"><span id="totalCostDisplay" class="fw-bold fs-5 text-dark">0.00</span> ج.م</div>
        </div>

        <div class="row g-2 align-items-center mb-2">
          <div class="col-6"><label class="fw-semibold" for="profit_margin_percent">هامش الربح المقترح (%):</label></div>
          <div class="col-6">
            <div class="input-group input-group-sm">
              <input type="number" step="0.1" min="0" max="99" name="profit_margin_percent" id="profit_margin_percent" class="form-control text-end" value="20.0">
              <span class="input-group-text">%</span>
            </div>
          </div>
        </div>

        <div class="row g-2 align-items-center mb-2">
          <div class="col-6"><label class="fw-semibold text-success">مبلغ الربح المقدر:</label></div>
          <div class="col-6 text-end"><span id="profitAmountDisplay" class="fw-bold text-success">0.00</span> ج.م</div>
        </div>

        <div class="row g-2 align-items-center mt-2 p-3 bg-primary text-white rounded">
          <div class="col-6"><label class="fw-bold fs-5">سعر البيع النهائي للعميل:</label></div>
          <div class="col-6 text-end"><span id="sellingPriceDisplay" class="fw-bold fs-4">0.00</span> ج.م</div>
        </div>

        <div class="row g-2 align-items-center mt-2 pt-2 border-top">
          <div class="col-6"><label class="fw-semibold text-success" for="deposit_amount">العربون / الدفعة المقدمة:</label></div>
          <div class="col-6">
            <div class="input-group input-group-sm">
              <input type="number" step="0.01" min="0" name="deposit_amount" id="deposit_amount" class="form-control text-end" value="0.00">
              <span class="input-group-text">ج.م</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-end gap-2">
    <a href="index.php" class="btn btn-secondary px-4">إلغاء</a>
    <button type="submit" class="btn btn-primary px-5 fw-bold"><i class="bi bi-save me-1"></i> حفظ عرض السعر</button>
  </div>
</form>

<script>
const materialsList = <?= json_encode($materials, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

function createRow(data = {}) {
    const tbody = document.getElementById('itemsBody');
    const tr = document.createElement('tr');

    let matOptions = '<option value="">-- اختر خامة من القائمة --</option>';
    materialsList.forEach(m => {
        const selected = data.material_id == m.id ? 'selected' : '';
        matOptions += `<option value="${m.id}" data-name="${m.name}" data-unit="${m.unit}" data-price="${m.unit_price}" ${selected}>${m.name} (${m.unit_price} ج/${m.unit})</option>`;
    });

    tr.innerHTML = `
        <td>
            <select name="item_material_id[]" class="form-select form-select-sm mat-select">
                ${matOptions}
            </select>
        </td>
        <td>
            <input type="text" name="item_description[]" class="form-control form-control-sm mat-desc" placeholder="اسم/وصف البند" value="${data.description || ''}" required>
        </td>
        <td>
            <input type="text" name="item_unit[]" class="form-control form-control-sm mat-unit" placeholder="الوحدة" value="${data.unit || ''}" required>
        </td>
        <td>
            <input type="number" step="0.01" min="0.01" name="item_quantity[]" class="form-control form-control-sm mat-qty" value="${data.quantity || 1}" required>
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="item_unit_price[]" class="form-control form-control-sm mat-price" value="${data.unit_price || 0}" required>
        </td>
        <td class="text-end fw-bold">
            <span class="row-total">0.00</span> ج.م
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button>
        </td>
    `;

    tbody.appendChild(tr);

    const select = tr.querySelector('.mat-select');
    const desc = tr.querySelector('.mat-desc');
    const unit = tr.querySelector('.mat-unit');
    const qty = tr.querySelector('.mat-qty');
    const price = tr.querySelector('.mat-price');
    const removeBtn = tr.querySelector('.remove-row');

    select.addEventListener('change', function() {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            desc.value = opt.getAttribute('data-name') || '';
            unit.value = opt.getAttribute('data-unit') || '';
            price.value = opt.getAttribute('data-price') || 0;
        }
        calculateAll();
    });

    qty.addEventListener('input', calculateAll);
    price.addEventListener('input', calculateAll);
    removeBtn.addEventListener('click', function() {
        tr.remove();
        calculateAll();
    });

    calculateAll();
}

function calculateAll() {
    let materialsTotal = 0;
    const rows = document.querySelectorAll('#itemsBody tr');

    rows.forEach(tr => {
        const qty = parseFloat(tr.querySelector('.mat-qty').value) || 0;
        const price = parseFloat(tr.querySelector('.mat-price').value) || 0;
        const rowTotal = qty * price;
        tr.querySelector('.row-total').textContent = rowTotal.toFixed(2);
        materialsTotal += rowTotal;
    });

    document.getElementById('materialsCostDisplay').textContent = materialsTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    const laborCost = parseFloat(document.getElementById('labor_cost').value) || 0;
    const overheadCost = parseFloat(document.getElementById('overhead_cost').value) || 0;

    const totalCost = materialsTotal + laborCost + overheadCost;
    document.getElementById('totalCostDisplay').textContent = totalCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    const marginPercent = parseFloat(document.getElementById('profit_margin_percent').value) || 0;
    let sellingPrice = totalCost;

    if (marginPercent > 0 && marginPercent < 100) {
        sellingPrice = totalCost / (1 - (marginPercent / 100));
    } else if (marginPercent >= 100) {
        sellingPrice = totalCost * (1 + (marginPercent / 100));
    }

    const profitAmount = sellingPrice - totalCost;

    document.getElementById('profitAmountDisplay').textContent = profitAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('sellingPriceDisplay').textContent = sellingPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

document.getElementById('addRowBtn').addEventListener('click', () => createRow());
document.getElementById('labor_cost').addEventListener('input', calculateAll);
document.getElementById('overhead_cost').addEventListener('input', calculateAll);
document.getElementById('profit_margin_percent').addEventListener('input', calculateAll);

document.addEventListener('DOMContentLoaded', function() {
    createRow();
    createRow();
});
</script>

<?php include '../includes/footer.php'; ?>
