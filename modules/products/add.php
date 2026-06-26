<?php
require_once __DIR__ . '/../../bootstrap.php';
Auth::requireAdmin();

$db         = Database::getInstance();
$categories = $db->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$errors     = [];
$input      = ['sku' => '', 'name' => '', 'description' => '', 'category_id' => '', 'price' => '', 'stock_qty' => '0', 'low_stock_threshold' => '10', 'is_active' => '1'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $input = array_map('trim', $_POST);

    // Validate
    if (empty($input['sku']))  $errors['sku']  = 'SKU is required.';
    if (empty($input['name'])) $errors['name'] = 'Product name is required.';
    if (!is_numeric($input['price']) || $input['price'] < 0) $errors['price'] = 'Enter a valid price.';
    if (!ctype_digit($input['stock_qty'])) $errors['stock_qty'] = 'Enter a valid initial stock quantity.';
    if (!ctype_digit($input['low_stock_threshold'])) $errors['low_stock_threshold'] = 'Enter a valid threshold.';
    if (empty($input['category_id'])) $errors['category_id'] = 'Please select a category.';

    // Duplicate SKU check
    if (empty($errors['sku'])) {
        $chk = $db->prepare('SELECT id FROM products WHERE sku = ?');
        $chk->execute([$input['sku']]);
        if ($chk->fetch()) $errors['sku'] = 'SKU already exists.';
    }

    if (empty($errors)) {
        $st = $db->prepare("
            INSERT INTO products (sku, name, description, category_id, price, stock_qty, low_stock_threshold, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $st->execute([
            strtoupper($input['sku']),
            $input['name'],
            $input['description'],
            $input['category_id'],
            $input['price'],
            $input['stock_qty'],
            $input['low_stock_threshold'],
            isset($input['is_active']) ? 1 : 0,
        ]);
        $productId = $db->lastInsertId();

        // Log initial stock movement if qty > 0
        if ((int)$input['stock_qty'] > 0) {
            $mv = $db->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, note) VALUES (?,?,'in',?,'Initial stock on product creation')");
            $mv->execute([$productId, Auth::id(), $input['stock_qty']]);
        }

        flash('success', 'Product "' . $input['name'] . '" added successfully.');
        redirect('modules/products/index.php');
    }
}

$pageTitle   = 'Add Product';
$activeNav   = 'products';
$topbarTitle = 'Add Product';
include __DIR__ . '/../../includes/header.php';
?>

<div style="max-width:680px">
  <div class="card">
    <div class="card-header">
      <span class="card-title">New Product</span>
      <a href="index.php" class="btn btn-outline btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
    </div>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">SKU <span class="req">*</span></label>
          <input name="sku" type="text" class="form-control <?= isset($errors['sku']) ? 'is-invalid' : '' ?>"
                 value="<?= e($input['sku']) ?>" placeholder="e.g. ELEC-010">
          <?php if (isset($errors['sku'])): ?><div class="invalid-feedback"><?= e($errors['sku']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">Category <span class="req">*</span></label>
          <select name="category_id" class="form-control <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>">
            <option value="">-- Select --</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $input['category_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['category_id'])): ?><div class="invalid-feedback"><?= e($errors['category_id']) ?></div><?php endif; ?>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Product Name <span class="req">*</span></label>
        <input name="name" type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
               value="<?= e($input['name']) ?>" placeholder="e.g. Wireless Mouse">
        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
      </div>

      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" placeholder="Optional product description"><?= e($input['description']) ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Price (฿) <span class="req">*</span></label>
          <input name="price" type="number" step="0.01" min="0" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>"
                 value="<?= e($input['price']) ?>" placeholder="0.00">
          <?php if (isset($errors['price'])): ?><div class="invalid-feedback"><?= e($errors['price']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">Initial Stock Qty</label>
          <input name="stock_qty" type="number" min="0" class="form-control <?= isset($errors['stock_qty']) ? 'is-invalid' : '' ?>"
                 value="<?= e($input['stock_qty']) ?>">
          <?php if (isset($errors['stock_qty'])): ?><div class="invalid-feedback"><?= e($errors['stock_qty']) ?></div><?php endif; ?>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Low Stock Threshold</label>
        <input name="low_stock_threshold" type="number" min="0" class="form-control <?= isset($errors['low_stock_threshold']) ? 'is-invalid' : '' ?>"
               value="<?= e($input['low_stock_threshold']) ?>">
        <div class="text-muted fs-sm mt-1">Alert will show when stock falls to or below this number.</div>
        <?php if (isset($errors['low_stock_threshold'])): ?><div class="invalid-feedback"><?= e($errors['low_stock_threshold']) ?></div><?php endif; ?>
      </div>

      <div class="form-group">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
          <input type="checkbox" name="is_active" value="1" <?= $input['is_active'] ? 'checked' : '' ?>>
          <span class="form-label" style="margin:0">Active (visible in listings)</span>
        </label>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save Product</button>
        <a href="index.php" class="btn btn-outline">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
