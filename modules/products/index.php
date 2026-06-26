<?php
require_once __DIR__ . '/../../bootstrap.php';
Auth::check();

$db = Database::getInstance();

// ── Filters ────────────────────────────────────────────────
$search      = trim($_GET['q']      ?? '');
$catFilter   = (int)($_GET['cat']   ?? 0);
$filter      = $_GET['filter']      ?? '';
$page        = max(1, (int)($_GET['page'] ?? 1));
$perPage     = 15;

// ── Build query ────────────────────────────────────────────
$where  = ['p.is_active = 1'];
$params = [];

if ($search !== '') {
    $where[]  = '(p.name LIKE ? OR p.sku LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($catFilter > 0) {
    $where[]  = 'p.category_id = ?';
    $params[] = $catFilter;
}
if ($filter === 'low_stock') {
    $where[] = 'p.stock_qty <= p.low_stock_threshold';
}

$whereSql = implode(' AND ', $where);

$total = $db->prepare("SELECT COUNT(*) FROM products p WHERE $whereSql");
$total->execute($params);
$total = (int)$total->fetchColumn();

['pages' => $pages, 'offset' => $offset] = paginate($total, $perPage, $page);

$st = $db->prepare("
  SELECT p.*, c.name AS category_name
  FROM   products p
  JOIN   categories c ON c.id = p.category_id
  WHERE  $whereSql
  ORDER  BY p.created_at DESC
  LIMIT  $perPage OFFSET $offset
");
$st->execute($params);
$products = $st->fetchAll();

$categories = $db->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$pageTitle   = 'Products';
$activeNav   = 'products';
$topbarTitle = 'Products';
$lowStockCount = (int)$db->query('SELECT COUNT(*) FROM products WHERE stock_qty <= low_stock_threshold AND is_active=1')->fetchColumn();
include __DIR__ . '/../../includes/header.php';
?>

<div class="card-header" style="margin-bottom:16px;padding:0">
  <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;flex:1">
    <div class="search-bar">
      <span class="icon"><i class="fa fa-magnifying-glass"></i></span>
      <input id="table-search" name="q" type="text" placeholder="Search name or SKU…" value="<?= e($search) ?>">
    </div>
    <select name="cat" class="form-control" style="width:auto" onchange="this.form.submit()">
      <option value="0">All Categories</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $catFilter == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="filter" class="form-control" style="width:auto" onchange="this.form.submit()">
      <option value="">All Products</option>
      <option value="low_stock" <?= $filter === 'low_stock' ? 'selected' : '' ?>>⚠ Low Stock Only</option>
    </select>
    <?php if ($search || $catFilter || $filter): ?>
      <a href="?" class="btn btn-outline btn-sm">Clear</a>
    <?php endif; ?>
  </form>
  <?php if (Auth::isAdmin()): ?>
  <a href="<?= BASE_URL ?>/modules/products/add.php" class="btn btn-primary">
    <i class="fa fa-plus"></i> Add Product
  </a>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-header">
    <span class="card-title">
      Products
      <?php if ($filter === 'low_stock'): ?><span class="badge badge-warning">Low Stock Filter</span><?php endif; ?>
    </span>
    <span class="text-muted fs-sm"><?= number_format($total) ?> records</span>
  </div>

  <div class="table-wrap">
    <?php if (empty($products)): ?>
      <div class="empty-state">
        <div class="icon">📦</div>
        <p>No products found.</p>
        <?php if (Auth::isAdmin()): ?>
          <a href="add.php" class="btn btn-primary mt-3">Add your first product</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>SKU</th>
          <th>Product</th>
          <th>Category</th>
          <th class="text-right">Price</th>
          <th class="text-right">Stock</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p):
          $isLow = $p['stock_qty'] <= $p['low_stock_threshold'];
        ?>
        <tr>
          <td><code style="font-size:.78rem;color:var(--muted)"><?= e($p['sku']) ?></code></td>
          <td>
            <div class="fw-600"><?= e($p['name']) ?></div>
            <?php if ($p['description']): ?>
              <div class="text-muted fs-sm" style="max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($p['description']) ?></div>
            <?php endif; ?>
          </td>
          <td><span class="badge badge-muted"><?= e($p['category_name']) ?></span></td>
          <td class="text-right fw-600"><?= money($p['price']) ?></td>
          <td class="text-right">
            <span class="badge <?= $isLow ? ($p['stock_qty'] == 0 ? 'badge-danger' : 'badge-warning') : 'badge-success' ?>">
              <?= number_format($p['stock_qty']) ?>
            </span>
            <?php if ($isLow): ?><br><span class="text-muted" style="font-size:.68rem">min <?= $p['low_stock_threshold'] ?></span><?php endif; ?>
          </td>
          <td>
            <?php if ($p['is_active']): ?>
              <span class="badge badge-success">Active</span>
            <?php else: ?>
              <span class="badge badge-muted">Inactive</span>
            <?php endif; ?>
          </td>
          <td>
            <div class="d-flex gap-2">
              <a href="<?= BASE_URL ?>/modules/stock/add.php?product_id=<?= $p['id'] ?>" class="btn btn-success btn-sm btn-icon" title="Add/Remove Stock">
                <i class="fa fa-right-left"></i>
              </a>
              <?php if (Auth::isAdmin()): ?>
              <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm btn-icon" title="Edit">
                <i class="fa fa-pen"></i>
              </a>
              <a href="delete.php?id=<?= $p['id'] ?>" class="btn btn-danger btn-sm btn-icon" title="Delete"
                 data-confirm="Delete '<?= e($p['name']) ?>'? This cannot be undone.">
                <i class="fa fa-trash"></i>
              </a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <?php if ($pages > 1): ?>
  <div class="pagination">
    <?php
    $qBase = http_build_query(array_filter(['q' => $search, 'cat' => $catFilter ?: null, 'filter' => $filter]));
    if ($qBase) $qBase = '&' . $qBase;
    ?>
    <button class="page-btn" onclick="location.href='?page=<?= max(1,$page-1) ?><?= $qBase ?>'" <?= $page<=1 ? 'disabled' : '' ?>>‹</button>
    <?php for ($i = 1; $i <= $pages; $i++): ?>
      <button class="page-btn <?= $i === $page ? 'active' : '' ?>" onclick="location.href='?page=<?= $i ?><?= $qBase ?>'"><?= $i ?></button>
    <?php endfor; ?>
    <button class="page-btn" onclick="location.href='?page=<?= min($pages,$page+1) ?><?= $qBase ?>'" <?= $page>=$pages ? 'disabled' : '' ?>>›</button>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
