<?php
$pageTitle   = $pageTitle   ?? APP_NAME;
$activeNav   = $activeNav   ?? '';
$topbarTitle = $topbarTitle ?? $pageTitle;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> — <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
  <?= $extraHead ?? '' ?>
</head>
<body>
<div class="layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main-wrap">
  <header class="topbar">
    <button id="sidebar-toggle" class="btn btn-ghost btn-icon" style="display:none" aria-label="Menu">
      <i class="fa fa-bars"></i>
    </button>
    <span class="topbar-title"><?= e($topbarTitle) ?></span>
    <div class="topbar-actions">
      <?php
      // Low stock count badge in topbar
      if (isset($lowStockCount) && $lowStockCount > 0): ?>
        <a href="<?= BASE_URL ?>/modules/products/index.php?filter=low_stock" class="btn btn-warning btn-sm">
          <i class="fa fa-triangle-exclamation"></i>
          <?= $lowStockCount ?> Low Stock
        </a>
      <?php endif; ?>
    </div>
  </header>
  <main class="content">
    <?php
    $flash_success = getFlash('success');
    $flash_error   = getFlash('error');
    if ($flash_success): ?>
      <div class="alert alert-success" data-auto-dismiss>
        <i class="fa fa-circle-check"></i> <?= e($flash_success) ?>
      </div>
    <?php endif;
    if ($flash_error): ?>
      <div class="alert alert-danger" data-auto-dismiss>
        <i class="fa fa-circle-xmark"></i> <?= e($flash_error) ?>
      </div>
    <?php endif; ?>
