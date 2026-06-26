<?php
require_once __DIR__ . '/../../bootstrap.php';
Auth::check();

$db = Database::getInstance();

// Summary stats for report cards
$totalProducts = $db->query('SELECT COUNT(*) FROM products WHERE is_active=1')->fetchColumn();
$totalValue    = $db->query('SELECT COALESCE(SUM(price*stock_qty),0) FROM products WHERE is_active=1')->fetchColumn();
$lowStock      = $db->query('SELECT COUNT(*) FROM products WHERE stock_qty <= low_stock_threshold AND is_active=1')->fetchColumn();

// Movements this month
$monthIn  = $db->query("SELECT COALESCE(SUM(quantity),0) FROM stock_movements WHERE type='in'  AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();
$monthOut = $db->query("SELECT COALESCE(SUM(quantity),0) FROM stock_movements WHERE type='out' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();

// Top 10 products by value
$topByValue = $db->query("
  SELECT p.name, p.sku, c.name AS category, p.stock_qty, p.price,
         (p.price * p.stock_qty) AS total_value
  FROM   products p JOIN categories c ON c.id = p.category_id
  WHERE  p.is_active = 1
  ORDER  BY total_value DESC LIMIT 10
")->fetchAll();

// Movement summary by product this month
$movSummary = $db->query("
  SELECT p.name, p.sku,
         SUM(CASE WHEN sm.type='in'  THEN sm.quantity ELSE 0 END) AS total_in,
         SUM(CASE WHEN sm.type='out' THEN sm.quantity ELSE 0 END) AS total_out
  FROM   stock_movements sm
  JOIN   products p ON p.id = sm.product_id
  WHERE  MONTH(sm.created_at)=MONTH(NOW()) AND YEAR(sm.created_at)=YEAR(NOW())
  GROUP  BY p.id ORDER BY (SUM(CASE WHEN sm.type='in' THEN sm.quantity ELSE 0 END)+SUM(CASE WHEN sm.type='out' THEN sm.quantity ELSE 0 END)) DESC LIMIT 10
")->fetchAll();

$pageTitle   = 'Reports';
$activeNav   = 'reports';
$topbarTitle = 'Reports & Export';
$lowStockCount = (int)$lowStock;
include __DIR__ . '/../../includes/header.php';
?>

<!-- Summary -->
<div class="stat-grid" style="margin-bottom:24px">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa fa-boxes-stacked"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= number_format($totalProducts) ?></div>
      <div class="stat-label">Active Products</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa fa-sack-dollar"></i></div>
    <div class="stat-body">
      <div class="stat-value" style="font-size:1.1rem"><?= money($totalValue) ?></div>
      <div class="stat-label">Total Inventory Value</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa fa-arrow-down"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= number_format($monthIn) ?></div>
      <div class="stat-label">Units In This Month</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon yellow"><i class="fa fa-arrow-up"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= number_format($monthOut) ?></div>
      <div class="stat-label">Units Out This Month</div>
    </div>
  </div>
</div>

<!-- Export buttons -->
<div class="card mb-4">
  <div class="card-header">
    <span class="card-title"><i class="fa fa-file-csv" style="color:var(--success)"></i> Export CSV</span>
  </div>
  <div class="d-flex gap-3" style="flex-wrap:wrap">
    <a href="export.php?type=products" class="btn btn-success">
      <i class="fa fa-download"></i> Products List
    </a>
    <a href="export.php?type=stock_movements" class="btn btn-success">
      <i class="fa fa-download"></i> Stock Movements
    </a>
    <a href="export.php?type=low_stock" class="btn btn-warning">
      <i class="fa fa-triangle-exclamation"></i> Low Stock Report
    </a>
    <a href="export.php?type=inventory_value" class="btn btn-primary">
      <i class="fa fa-download"></i> Inventory Valuation
    </a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
  <!-- Top products by value -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Top 10 Products by Inventory Value</span>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Product</th><th class="text-right">Qty</th><th class="text-right">Value</th></tr></thead>
        <tbody>
          <?php foreach ($topByValue as $i => $p): ?>
          <tr>
            <td class="text-muted"><?= $i+1 ?></td>
            <td>
              <div class="fw-600 fs-sm"><?= e($p['name']) ?></div>
              <div class="text-muted" style="font-size:.72rem"><?= e($p['category']) ?></div>
            </td>
            <td class="text-right"><?= number_format($p['stock_qty']) ?></td>
            <td class="text-right fw-700"><?= money($p['total_value']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Monthly movement summary -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Movement Summary — This Month</span>
    </div>
    <div class="table-wrap">
      <?php if (empty($movSummary)): ?>
        <div class="empty-state"><div class="icon">📊</div>No movements this month.</div>
      <?php else: ?>
      <table>
        <thead><tr><th>Product</th><th class="text-right">In</th><th class="text-right">Out</th></tr></thead>
        <tbody>
          <?php foreach ($movSummary as $m): ?>
          <tr>
            <td>
              <div class="fw-600 fs-sm"><?= e($m['name']) ?></div>
              <div class="text-muted" style="font-size:.72rem"><?= e($m['sku']) ?></div>
            </td>
            <td class="text-right mv-in">+<?= number_format($m['total_in']) ?></td>
            <td class="text-right mv-out">-<?= number_format($m['total_out']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
