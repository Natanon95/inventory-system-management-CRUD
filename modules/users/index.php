<?php
require_once __DIR__ . '/../../bootstrap.php';
Auth::requireAdmin();

$db    = Database::getInstance();
$users = $db->query('SELECT * FROM users ORDER BY role, username')->fetchAll();

$pageTitle   = 'Users';
$activeNav   = 'users';
$topbarTitle = 'User Management';
$lowStockCount = (int)$db->query('SELECT COUNT(*) FROM products WHERE stock_qty <= low_stock_threshold AND is_active=1')->fetchColumn();
include __DIR__ . '/../../includes/header.php';
?>

<div class="card-header" style="margin-bottom:16px;padding:0">
  <span></span>
  <button class="btn btn-primary" data-modal-open="modal-add">
    <i class="fa fa-plus"></i> Add User
  </button>
</div>

<div class="card">
  <div class="card-header">
    <span class="card-title">All Users</span>
    <span class="text-muted fs-sm"><?= count($users) ?> total</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>User</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Created</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td>
            <div class="d-flex align-center gap-2">
              <div class="user-avatar" style="width:30px;height:30px;font-size:.72rem">
                <?= strtoupper(substr($u['full_name'], 0, 2)) ?>
              </div>
              <div>
                <div class="fw-600"><?= e($u['full_name']) ?></div>
                <div class="text-muted fs-sm">@<?= e($u['username']) ?></div>
              </div>
            </div>
          </td>
          <td class="text-muted fs-sm"><?= e($u['email']) ?></td>
          <td>
            <span class="badge <?= $u['role']==='admin' ? 'badge-primary' : 'badge-muted' ?>">
              <?= ucfirst($u['role']) ?>
            </span>
          </td>
          <td>
            <span class="badge <?= $u['is_active'] ? 'badge-success' : 'badge-danger' ?>">
              <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
          </td>
          <td class="text-muted fs-sm"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          <td>
            <div class="d-flex gap-2">
              <?php if ($u['id'] !== Auth::id()): ?>
              <a href="toggle.php?id=<?= $u['id'] ?>" class="btn btn-outline btn-sm"
                 data-confirm="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?> this user?">
                <?= $u['is_active'] ? '<i class="fa fa-ban"></i>' : '<i class="fa fa-check"></i>' ?>
              </a>
              <?php endif; ?>
              <button class="btn btn-outline btn-sm btn-icon" title="Reset Password"
                      onclick="openResetModal(<?= $u['id'] ?>, <?= htmlspecialchars(json_encode($u['username']), ENT_QUOTES) ?>)">
                <i class="fa fa-key"></i>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="modal-add">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Add User</span>
      <button class="btn btn-ghost btn-icon" data-modal-close><i class="fa fa-xmark"></i></button>
    </div>
    <form method="POST" action="save.php">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Username <span class="req">*</span></label>
            <input name="username" type="text" class="form-control" required autocomplete="off">
          </div>
          <div class="form-group">
            <label class="form-label">Role <span class="req">*</span></label>
            <select name="role" class="form-control">
              <option value="staff">Staff</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Full Name <span class="req">*</span></label>
          <input name="full_name" type="text" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email <span class="req">*</span></label>
          <input name="email" type="email" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password <span class="req">*</span></label>
          <input name="password" type="password" class="form-control" required minlength="8" autocomplete="new-password">
          <div class="text-muted fs-sm mt-1">Minimum 8 characters.</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">Create User</button>
      </div>
    </form>
  </div>
</div>

<!-- Reset Password Modal -->
<div class="modal-overlay" id="modal-reset">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Reset Password</span>
      <button class="btn btn-ghost btn-icon" data-modal-close><i class="fa fa-xmark"></i></button>
    </div>
    <form method="POST" action="save.php">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="action" value="reset_password">
      <input type="hidden" name="id" id="reset-id">
      <div class="modal-body">
        <p class="mb-3">Reset password for: <strong id="reset-username"></strong></p>
        <div class="form-group">
          <label class="form-label">New Password <span class="req">*</span></label>
          <input name="password" type="password" class="form-control" required minlength="8" autocomplete="new-password">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-warning"><i class="fa fa-key"></i> Reset</button>
      </div>
    </form>
  </div>
</div>

<?php $extraScript = '<script>
function openResetModal(id, username) {
  document.getElementById("reset-id").value       = id;
  document.getElementById("reset-username").textContent = username;
  document.getElementById("modal-reset").classList.add("open");
}
</script>'; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
