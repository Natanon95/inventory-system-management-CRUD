<?php
require_once __DIR__ . '/../../bootstrap.php';
Auth::check();

$db       = Database::getInstance();
$errors   = [];
$preselect= (int)($_GET['product_id'] ?? 0);
$products = $db->query('SELECT id, name, sku, stock_qty FROM products WHERE is_active=1 ORDER BY name')->fetchAll();

$input = [
    'product_id' => $preselect,
    'type'       => 'in',
    'quantity'   => '',
    'note'       => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $input = array_map('trim', $_POST);
    $input['product_id'] = (int)$input['product_id'];

    if (!$input['product_id']) $errors['product_id'] = 'Please select a product.';
    if (!in_array($input['type'], ['in','out','adjustment'])) $errors['type'] = 'Invalid type.';
    if (!ctype_digit($input['quantity']) || (int)$input['quantity'] <= 0) $errors['quantity'] = 'Enter a positive whole number.';

    // Check sufficient stock for OUT
    if (empty($errors) && $input['type'] === 'out') {
        $st = $db->prepare('SELECT stock_qty FROM products WHERE id=?');
        $st->execute([$input['product_id']]);
        $currentStock = (int)$st->fetchColumn();
        if ((int)$input['quantity'] > $currentStock) {
            $errors['quantity'] = "Only $currentStock units available.";
        }
    }

    if (empty($errors)) {
        $qty = (int)$input['quantity'];

        // Update stock_qty
        $delta = match($input['type']) {
            'in'         => $qty,
            'out'        => -$qty,
            'adjustment' => (int)$input['adj_qty'] - (int)$input['base_qty'], // handled below
            default      => 0,
        };

        if ($input['type'] === 'adjustment') {
            // Adjustment: set absolute quantity
            $st = $db->prepare('SELECT stock_qty FROM products WHERE id=?');
            $st->execute([$input['product_id']]);
            $cur   = (int)$st->fetchColumn();
            $delta = $qty - $cur;
            $db->prepare('UPDATE products SET stock_qty=? WHERE id=?')->execute([$qty, $input['product_id']]);
        } else {
            $db->prepare("UPDATE products SET stock_qty = stock_qty + ? WHERE id=?")->execute([$delta, $input['product_id']]);
        }

        // Insert movement record
        $mv = $db->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, note) VALUES (?,?,?,?,?)");
        $mv->execute([
            $input['product_id'],
            Auth::id(),
            $input['type'],
            $qty,
            $input['note'],
        ]);

        // Invalidate low stock cache
        unset($_SESSION['low_stock_cache']);

        flash('success', 'Stock movement recorded successfully.');
        redirect('modules/stock/index.php');
    }
}

$pageTitle   = 'New Stock Movement';
$activeNav   = 'stock';
$topbarTitle = 'Record Stock Movement';
include __DIR__ . '/../../includes/header.php';
?>

<div style="max-width:560px">
  <div class="card">
    <div class="card-header">
      <span class="card-title">Stock Movement</span>
      <a href="index.php" class="btn btn-outline btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
    </div>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <div class="form-group">
        <label class="form-label">Product <span class="req">*</span></label>
        <select name="product_id" id="sel-product" class="form-control <?= isset($errors['product_id']) ? 'is-invalid' : '' ?>" onchange="updateStock(this)">
          <option value="">-- Select product --</option>
          <?php foreach ($products as $p): ?>
            <option value="<?= $p['id'] ?>" data-stock="<?= $p['stock_qty'] ?>"
                    <?= $input['product_id'] == $p['id'] ? 'selected' : '' ?>>
              <?= e($p['sku']) ?> — <?= e($p['name']) ?> (<?= number_format($p['stock_qty']) ?> in stock)
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['product_id'])): ?><div class="invalid-feedback"><?= e($errors['product_id']) ?></div><?php endif; ?>
      </div>

      <div id="current-stock-info" style="display:none;margin-bottom:12px">
        <div class="alert alert-info" style="margin:0">
          <i class="fa fa-circle-info"></i>
          Current stock: <strong id="current-stock-val">0</strong> units
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Movement Type <span class="req">*</span></label>
        <select name="type" id="sel-type" class="form-control" onchange="updateTypeHint(this.value)">
          <option value="in"         <?= $input['type']==='in'         ? 'selected':'' ?>>▲ Stock In  (add to stock)</option>
          <option value="out"        <?= $input['type']==='out'        ? 'selected':'' ?>>▼ Stock Out (remove from stock)</option>
          <option value="adjustment" <?= $input['type']==='adjustment' ? 'selected':'' ?>>~ Adjustment (set absolute quantity)</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" id="qty-label">Quantity <span class="req">*</span></label>
        <input name="quantity" id="inp-qty" type="number" min="1" class="form-control <?= isset($errors['quantity']) ? 'is-invalid' : '' ?>"
               value="<?= e($input['quantity']) ?>" placeholder="e.g. 50">
        <div id="qty-hint" class="text-muted fs-sm mt-1"></div>
        <?php if (isset($errors['quantity'])): ?><div class="invalid-feedback"><?= e($errors['quantity']) ?></div><?php endif; ?>
      </div>

      <div class="form-group">
        <label class="form-label">Note / Reason</label>
        <input name="note" type="text" class="form-control" value="<?= e($input['note']) ?>" placeholder="e.g. Purchase order #1234">
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save Movement</button>
        <a href="index.php" class="btn btn-outline">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php $extraScript = '<script>
function updateStock(sel) {
  const opt   = sel.options[sel.selectedIndex];
  const stock = opt.dataset.stock;
  const info  = document.getElementById("current-stock-info");
  if (stock !== undefined && sel.value) {
    document.getElementById("current-stock-val").textContent = Number(stock).toLocaleString();
    info.style.display = "block";
  } else {
    info.style.display = "none";
  }
}

function updateTypeHint(type) {
  const hint  = document.getElementById("qty-hint");
  const label = document.getElementById("qty-label");
  if (type === "adjustment") {
    label.innerHTML = "New Absolute Quantity <span class=\'req\'>*</span>";
    hint.textContent = "The stock will be set to exactly this value.";
  } else if (type === "in") {
    label.innerHTML = "Quantity to Add <span class=\'req\'>*</span>";
    hint.textContent = "This amount will be added to current stock.";
  } else {
    label.innerHTML = "Quantity to Remove <span class=\'req\'>*</span>";
    hint.textContent = "This amount will be deducted from current stock.";
  }
}

// Init
const selP = document.getElementById("sel-product");
if (selP && selP.value) updateStock(selP);
updateTypeHint(document.getElementById("sel-type").value);
</script>'; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
