<?php
require_once __DIR__ . '/../../bootstrap.php';
Auth::check();

$db = Database::getInstance();

$categories = $db->query("
  SELECT c.*, COUNT(p.id) AS product_count
  FROM   categories c
  LEFT   JOIN products p ON p.category_id = c.id AND p.is_active = 1
  GROUP  BY c.id
  ORDER  BY c.name
")->fetchAll();

$pageTitle   = 'Categories';
$activeNav   = 'categories';
$topbarTitle = 'Categories';
$lowStockCount = (int)$db->query('SELECT COUNT(*) FROM products WHERE stock_qty <= low_stock_threshold AND is_active=1')->fetchColumn();
include __DIR__ . '/../../includes/header.php';
?>

<div class="card-header" style="margin-bottom:16px;padding:0">
  <span></span>
  <?php if (Auth::isAdmin()): ?>
  <button class="btn btn-primary" data-modal-open="modal-add">
    <i class="fa fa-plus"></i> Add Category
  </button>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-header">
    <span class="card-title">All Categories</span>
    <span class="text-muted fs-sm"><?= count($categories) ?> total</span>
  </div>
  <div class="table-wrap">
    <?php if (empty($categories)): ?>
      <div class="empty-state"><div class="icon">🏷️</div><p>No categories yet.</p></div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Description</th>
          <th class="text-right">Products</th>
          <th>Created</th>
          <?php if (Auth::isAdmin()): ?><th></th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $i => $c): ?>
        <tr>
          <td class="text-muted fs-sm"><?= $i + 1 ?></td>
          <td class="fw-600"><?= e($c['name']) ?></td>
          <td class="text-muted"><?= e($c['description'] ?? '—') ?></td>
          <td class="text-right">
            <a href="<?= BASE_URL ?>/modules/products/index.php?cat=<?= $c['id'] ?>" class="badge badge-primary">
              <?= number_format($c['product_count']) ?>
            </a>
          </td>
          <td class="text-muted fs-sm"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
          <?php if (Auth::isAdmin()): ?>
          <td>
            <div class="d-flex gap-2">
              <button class="btn btn-outline btn-sm btn-icon" title="Edit"
                      onclick="openEditModal(<?= $c['id'] ?>, <?= htmlspecialchars(json_encode($c['name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($c['description'] ?? ''), ENT_QUOTES) ?>)">
                <i class="fa fa-pen"></i>
              </button>
              <?php if ($c['product_count'] == 0): ?>
              <a href="delete.php?id=<?= $c['id'] ?>" class="btn btn-danger btn-sm btn-icon" title="Delete"
                 data-confirm="Delete category '<?= e($c['name']) ?>'?">
                <i class="fa fa-trash"></i>
              </a>
              <?php else: ?>
              <button class="btn btn-danger btn-sm btn-icon" disabled title="Cannot delete — has products">
                <i class="fa fa-trash"></i>
              </button>
              <?php endif; ?>
            </div>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?php if (Auth::isAdmin()): ?>
<!-- Add Modal -->
<div class="modal-overlay" id="modal-add">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Add Category</span>
      <button class="btn btn-ghost btn-icon" data-modal-close><i class="fa fa-xmark"></i></button>
    </div>
    <form method="POST" action="save.php">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Name <span class="req">*</span></label>
          <input name="name" type="text" class="form-control" required placeholder="e.g. Electronics">
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" placeholder="Optional"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="modal-edit">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Edit Category</span>
      <button class="btn btn-ghost btn-icon" data-modal-close><i class="fa fa-xmark"></i></button>
    </div>
    <form method="POST" action="save.php">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="edit-id">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Name <span class="req">*</span></label>
          <input id="edit-name" name="name" type="text" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea id="edit-desc" name="description" class="form-control"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
    </form>
  </div>
</div>

<?php $extraScript = '<script>
function openEditModal(id, name, desc) {
  document.getElementById("edit-id").value   = id;
  document.getElementById("edit-name").value = name;
  document.getElementById("edit-desc").value = desc;
  document.getElementById("modal-edit").classList.add("open");
}
</script>'; ?>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
