<?php
$initials = strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 2));
$role     = $_SESSION['role'] ?? 'staff';

// Low stock count (cached in session for 5 min)
if (!isset($_SESSION['low_stock_cache']) || time() - $_SESSION['low_stock_cache']['ts'] > 300) {
    try {
        $db = Database::getInstance();
        $n  = $db->query('SELECT COUNT(*) FROM products WHERE stock_qty <= low_stock_threshold AND is_active = 1')->fetchColumn();
        $_SESSION['low_stock_cache'] = ['count' => (int)$n, 'ts' => time()];
    } catch (Exception $e) {
        $_SESSION['low_stock_cache'] = ['count' => 0, 'ts' => time()];
    }
}
$lowStockCount = $_SESSION['low_stock_cache']['count'];
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="logo-icon">📦</div>
    <span><?= APP_NAME ?></span>
  </div>

  <nav class="sidebar-nav">
    <p class="nav-section">Main</p>

    <a href="<?= BASE_URL ?>/modules/dashboard/index.php" class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
      <span class="icon"><i class="fa fa-gauge-high"></i></span> Dashboard
    </a>

    <a href="<?= BASE_URL ?>/modules/products/index.php" class="nav-item <?= $activeNav === 'products' ? 'active' : '' ?>">
      <span class="icon"><i class="fa fa-boxes-stacked"></i></span> Products
      <?php if ($lowStockCount > 0): ?>
        <span class="nav-badge"><?= $lowStockCount ?></span>
      <?php endif; ?>
    </a>

    <a href="<?= BASE_URL ?>/modules/categories/index.php" class="nav-item <?= $activeNav === 'categories' ? 'active' : '' ?>">
      <span class="icon"><i class="fa fa-tags"></i></span> Categories
    </a>

    <a href="<?= BASE_URL ?>/modules/stock/index.php" class="nav-item <?= $activeNav === 'stock' ? 'active' : '' ?>">
      <span class="icon"><i class="fa fa-right-left"></i></span> Stock Movements
    </a>

    <p class="nav-section" style="margin-top:8px">Reports</p>

    <a href="<?= BASE_URL ?>/modules/reports/index.php" class="nav-item <?= $activeNav === 'reports' ? 'active' : '' ?>">
      <span class="icon"><i class="fa fa-chart-bar"></i></span> Reports &amp; Export
    </a>

    <?php if ($role === 'admin'): ?>
    <p class="nav-section" style="margin-top:8px">Admin</p>

    <a href="<?= BASE_URL ?>/modules/users/index.php" class="nav-item <?= $activeNav === 'users' ? 'active' : '' ?>">
      <span class="icon"><i class="fa fa-users"></i></span> Users
    </a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar"><?= e($initials) ?></div>
      <div class="user-info">
        <div class="user-name"><?= e($_SESSION['full_name'] ?? '') ?></div>
        <div class="user-role"><?= e($role) ?></div>
      </div>
      <a href="<?= BASE_URL ?>/logout.php" class="logout-btn" title="Logout">
        <i class="fa fa-right-from-bracket"></i>
      </a>
    </div>
  </div>
</aside>
