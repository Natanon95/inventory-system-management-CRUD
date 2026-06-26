<?php
require_once __DIR__ . '/../../bootstrap.php';
Auth::check();

$db = Database::getInstance();

$search    = trim($_GET['q']    ?? '');
$typeFilter= $_GET['type']      ?? '';
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 20;

$where  = ['1=1'];
$params = [];

if ($search !== '') {
    $where[]  = '(p.name LIKE ? OR p.sku LIKE ? OR sm.note LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (in_array($typeFilter, ['in','out','adjustment'])) {
    $where[]  = 'sm.type = ?';
    $params[] = $typeFilter;
}

$whereSql = implode(' AND ', $where);

$total = $db->prepare("SELECT COUNT(*) FROM stock_movements sm JOIN products p ON p.id=sm.product_id WHERE $whereSql");
$total->execute($params);
$total = (int)$total->fetchColumn();

['pages' => $pages, 'offset' => $offset] = paginate($total, $perPage, $page);

$st = $db->prepare("
  SELECT sm.*, p.name AS product_name, p.sku, u.full_name AS username
  FROM   stock_movements sm
  JOIN   products p ON p.id = sm.product_id
  JOIN   users    u ON u.id = sm.user_id
  WHERE  $whereSql
  ORDER  BY sm.created_at DESC
  LIMIT  $perPage OFFSET $offset
");
$st->execute($params);
$movements = $st->fetchAll();

$pageTitle   = 'Stock Movements';
$activeNav   = 'stock';
$topbarTitle = 'Stock Movements';
$lowStockCount = (int)$db->query('SELECT COUNT(*) FROM products WHERE stock_qty <= low_stock_threshold AND is_active=1')->fetchColumn();
include __DIR__ . '/../../includes/header.php';
?>

<div class="card-header" style="margin-bottom:16px;padding:0">
  <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;flex:1">
    <div class="search-bar">
      <span class="icon"><i class="fa fa-magnifying-glass"></i></span>
      <input id="table-search" name="q" type="text" placeholder="Search product or note…" value="<?= e($search) ?>">
    </div>
    <select name="type" class="form-control" style="width:auto" onchange="this.form.submit()">
      <option value="">All Types</option>
      <option value="in"         <?= $typeFilter==='in'         ? 'selected':'' ?>>▲ Stock In</option>
      <option value="out"        <?= $typeFilter==='out'        ? 'selected':'' ?>>▼ Stock Out</option>
      <option value="adjustment" <?= $typeFilter==='adjustment' ? 'selected':'' ?>>~ Adjustment</option>
    </select>
    <?php if ($search || $typeFilter): ?>
      <a href="?" class="btn btn-outline btn-sm">Clear</a>
    <?php endif; ?>
  </form>
  <a href="add.php" class="btn btn-primary"><i class="fa fa-plus"></i> New Movement</a>
</div>

<div class="card">
  <div class="card-header">
    <span class="card-title">Movement Log</span>
    <span class="text-muted fs-sm"><?= number_format($total) ?> records</span>
  </div>
  <div class="table-wrap">
    <?php if (empty($movements)): ?>
      <div class="empty-state"><div class="icon">📋</div><p>No movements found.</p></div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Product</th>
          <th>Type</th>
          <th class="text-right">Qty</th>
          <th>Note</th>
          <th>By</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($movements as $m): ?>
        <tr>
          <td class="text-muted fs-sm" style="white-space:nowrap">
            <?= date('d M Y', strtotime($m['created_at'])) ?><br>
            <span style="font-size:.68rem"><?= date('H:i', strtotime($m['created_at'])) ?></span>
          </td>
          <td>
            <div class="fw-600"><?= e($m['product_name']) ?></div>
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
          <td class="text-right fw-700 <?= $m['type']==='in' ? 'mv-in' : ($m['type']==='out' ? 'mv-out' : 'mv-adj') ?>">
            <?= $m['type']==='in' ? '+' : ($m['type']==='out' ? '-' : '±') ?><?= number_format(abs($m['quantity'])) ?>
          </td>
          <td class="text-muted fs-sm"><?= e($m['note'] ?: '—') ?></td>
          <td class="text-muted fs-sm"><?= e($m['username']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <?php if ($pages > 1): ?>
  <div class="pagination">
    <?php
    $qBase = http_build_query(array_filter(['q' => $search, 'type' => $typeFilter]));
    if ($qBase) $qBase = '&' . $qBase;
    ?>
    <button class="page-btn" onclick="location.href='?page=<?= max(1,$page-1) ?><?= $qBase ?>'" <?= $page<=1?'disabled':'' ?>>‹</button>
    <?php for ($i=1; $i<=$pages; $i++): ?>
      <button class="page-btn <?= $i===$page?'active':'' ?>" onclick="location.href='?page=<?= $i ?><?= $qBase ?>'"><?= $i ?></button>
    <?php endfor; ?>
    <button class="page-btn" onclick="location.href='?page=<?= min($pages,$page+1) ?><?= $qBase ?>'" <?= $page>=$pages?'disabled':'' ?>>›</button>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
