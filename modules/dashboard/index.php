<?php
require_once __DIR__ . '/../../bootstrap.php';
Auth::check();

$db = Database::getInstance();

// ── Stats ──────────────────────────────────────────────────
$totalProducts  = $db->query('SELECT COUNT(*) FROM products WHERE is_active=1')->fetchColumn();
$totalCategories= $db->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$lowStock       = $db->query('SELECT COUNT(*) FROM products WHERE stock_qty <= low_stock_threshold AND is_active=1')->fetchColumn();

$totalValue     = $db->query('SELECT SUM(price * stock_qty) FROM products WHERE is_active=1')->fetchColumn() ?: 0;

$todayIn  = $db->query("SELECT COALESCE(SUM(quantity),0) FROM stock_movements WHERE type='in'  AND DATE(created_at)=CURDATE()")->fetchColumn();
$todayOut = $db->query("SELECT COALESCE(SUM(quantity),0) FROM stock_movements WHERE type='out' AND DATE(created_at)=CURDATE()")->fetchColumn();

// ── Chart: stock movements last 14 days ────────────────────
$movChart = $db->query("
  SELECT DATE(created_at) AS d,
         SUM(CASE WHEN type='in'  THEN quantity ELSE 0 END) AS total_in,
         SUM(CASE WHEN type='out' THEN quantity ELSE 0 END) AS total_out
  FROM   stock_movements
  WHERE  created_at >= CURDATE() - INTERVAL 13 DAY
  GROUP  BY d ORDER BY d
")->fetchAll();

$chartLabels   = [];
$chartIn       = [];
$chartOut      = [];
// Fill all 14 days even if no data
$baseDate = new DateTime('-13 days');
$dayMap   = array_column($movChart, null, 'd');
for ($i = 0; $i < 14; $i++) {
    $key           = $baseDate->format('Y-m-d');
    $chartLabels[] = $baseDate->format('d M');
    $chartIn[]     = (int)($dayMap[$key]['total_in']  ?? 0);
    $chartOut[]    = (int)($dayMap[$key]['total_out'] ?? 0);
    $baseDate->modify('+1 day');
}

// ── Chart: top 5 categories by stock value ─────────────────
$catChart = $db->query("
  SELECT c.name, SUM(p.price * p.stock_qty) AS total
  FROM   products p JOIN categories c ON c.id = p.category_id
  WHERE  p.is_active = 1
  GROUP  BY c.id ORDER BY total DESC LIMIT 5
")->fetchAll();

// ── Recent movements ───────────────────────────────────────
$recentMovements = $db->query("
  SELECT sm.*, p.name AS product_name, p.sku, u.full_name AS username
  FROM   stock_movements sm
  JOIN   products  p ON p.id = sm.product_id
  JOIN   users     u ON u.id = sm.user_id
  ORDER  BY sm.created_at DESC LIMIT 8
")->fetchAll();

// ── Low stock products ─────────────────────────────────────
$lowStockItems = $db->query("
  SELECT p.*, c.name AS category_name
  FROM   products p JOIN categories c ON c.id = p.category_id
  WHERE  p.stock_qty <= p.low_stock_threshold AND p.is_active = 1
  ORDER  BY (p.stock_qty / p.low_stock_threshold) ASC LIMIT 6
")->fetchAll();

$pageTitle   = 'Dashboard';
$activeNav   = 'dashboard';
$topbarTitle = 'Dashboard';
$lowStockCount = (int)$lowStock;
$extraHead   = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>';
include __DIR__ . '/../../includes/header.php';
?>

<!-- Stat Cards -->
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa fa-boxes-stacked"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= number_format($totalProducts) ?></div>
      <div class="stat-label">Total Products</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple"><i class="fa fa-tags"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= number_format($totalCategories) ?></div>
      <div class="stat-label">Categories</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa fa-sack-dollar"></i></div>
    <div class="stat-body">
      <div class="stat-value" style="font-size:1.2rem"><?= money($totalValue) ?></div>
      <div class="stat-label">Total Inventory Value</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon <?= $lowStock > 0 ? 'red' : 'green' ?>"><i class="fa fa-triangle-exclamation"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= number_format($lowStock) ?></div>
      <div class="stat-label">Low Stock Items</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa fa-arrow-down"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= number_format($todayIn) ?></div>
      <div class="stat-label">Stock In Today</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon yellow"><i class="fa fa-arrow-up"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= number_format($todayOut) ?></div>
      <div class="stat-label">Stock Out Today</div>
    </div>
  </div>
</div>

<?php if ($lowStock > 0): ?>
<div class="low-stock-banner">
  <i class="fa fa-triangle-exclamation"></i>
  <strong><?= $lowStock ?> product(s)</strong> are at or below their low-stock threshold.
  <a href="<?= BASE_URL ?>/modules/products/index.php?filter=low_stock" style="margin-left:8px;color:var(--warning);font-weight:700">View →</a>
</div>
<?php endif; ?>

<!-- Charts -->
<div class="chart-grid">
  <div class="card">
    <div class="card-header">
      <span class="card-title">Stock Movements — Last 14 Days</span>
    </div>
    <div class="chart-canvas-wrap">
      <canvas id="movChart"></canvas>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <span class="card-title">Inventory Value by Category</span>
    </div>
    <div class="chart-canvas-wrap">
      <canvas id="catChart"></canvas>
    </div>
  </div>
</div>

<!-- Recent Movements + Low Stock side by side -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
  <div class="card">
    <div class="card-header">
      <span class="card-title">Recent Movements</span>
      <a href="<?= BASE_URL ?>/modules/stock/index.php" class="btn btn-ghost btn-sm">View all</a>
    </div>
    <?php if (empty($recentMovements)): ?>
      <div class="empty-state"><div class="icon">📦</div>No movements yet</div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Product</th><th>Type</th><th>Qty</th><th>Time</th></tr></thead>
        <tbody>
          <?php foreach ($recentMovements as $m): ?>
          <tr>
            <td>
              <div class="fw-600 fs-sm"><?= e($m['product_name']) ?></div>
              <div class="text-muted" style="font-size:.72rem"><?= e($m['sku']) ?></div>
            </td>
            <td>
              <?php if ($m['type'] === 'in'): ?>
                <span class="badge badge-success">▲ IN</span>
              <?php elseif ($m['type'] === 'out'): ?>
                <span class="badge badge-danger">▼ OUT</span>
              <?php else: ?>
                <span class="badge badge-warning">~ ADJ</span>
              <?php endif; ?>
            </td>
            <td class="fw-700"><?= number_format(abs($m['quantity'])) ?></td>
            <td class="text-muted fs-sm"><?= timeAgo($m['created_at']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title">Low Stock Alert</span>
      <a href="<?= BASE_URL ?>/modules/products/index.php?filter=low_stock" class="btn btn-ghost btn-sm">View all</a>
    </div>
    <?php if (empty($lowStockItems)): ?>
      <div class="empty-state"><div class="icon">✅</div>All products well stocked!</div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Product</th><th>Stock</th><th>Min</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($lowStockItems as $p): ?>
          <tr>
            <td>
              <div class="fw-600 fs-sm"><?= e($p['name']) ?></div>
              <div class="text-muted" style="font-size:.72rem"><?= e($p['category_name']) ?></div>
            </td>
            <td>
              <span class="badge <?= $p['stock_qty'] == 0 ? 'badge-danger' : 'badge-warning' ?>">
                <?= number_format($p['stock_qty']) ?>
              </span>
            </td>
            <td class="text-muted fs-sm"><?= number_format($p['low_stock_threshold']) ?></td>
            <td>
              <a href="<?= BASE_URL ?>/modules/stock/add.php?product_id=<?= $p['id'] ?>" class="btn btn-success btn-sm">
                <i class="fa fa-plus"></i> Restock
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php
$extraScript = '<script>
const movCtx = document.getElementById("movChart").getContext("2d");
new Chart(movCtx, {
  type: "line",
  data: {
    labels: ' . json_encode($chartLabels) . ',
    datasets: [
      {
        label: "Stock In",
        data: ' . json_encode($chartIn) . ',
        borderColor: "#22c55e",
        backgroundColor: "rgba(34,197,94,.12)",
        tension: .35, fill: true, pointRadius: 3
      },
      {
        label: "Stock Out",
        data: ' . json_encode($chartOut) . ',
        borderColor: "#ef4444",
        backgroundColor: "rgba(239,68,68,.08)",
        tension: .35, fill: true, pointRadius: 3
      }
    ]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { position: "top" } },
    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
  }
});

const catCtx = document.getElementById("catChart").getContext("2d");
new Chart(catCtx, {
  type: "doughnut",
  data: {
    labels: ' . json_encode(array_column($catChart, 'name')) . ',
    datasets: [{
      data: ' . json_encode(array_column($catChart, 'total')) . ',
      backgroundColor: ["#4f46e5","#06b6d4","#22c55e","#f59e0b","#ef4444"],
      borderWidth: 2, borderColor: "#fff"
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: {
      legend: { position: "right" },
      tooltip: {
        callbacks: {
          label: function(ctx) {
            return " ฿" + Number(ctx.parsed).toLocaleString("en", {minimumFractionDigits:2});
          }
        }
      }
    }
  }
});
</script>';
include __DIR__ . '/../../includes/footer.php';
?>
