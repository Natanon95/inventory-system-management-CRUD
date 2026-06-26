<?php
require_once __DIR__ . '/../../bootstrap.php';
Auth::requireAdmin();

$db = Database::getInstance();
$id = (int)($_GET['id'] ?? 0);

// Safety: don't delete if products exist
$count = $db->prepare('SELECT COUNT(*) FROM products WHERE category_id = ? AND is_active = 1');
$count->execute([$id]);
if ($count->fetchColumn() > 0) {
    flash('error', 'Cannot delete a category that has active products.');
    redirect('modules/categories/index.php');
}

$db->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
flash('success', 'Category deleted.');
redirect('modules/categories/index.php');
