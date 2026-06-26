<?php
require_once __DIR__ . '/../../bootstrap.php';
Auth::requireAdmin();

$db = Database::getInstance();
$id = (int)($_GET['id'] ?? 0);

$product = $db->prepare('SELECT * FROM products WHERE id = ?');
$product->execute([$id]);
$product = $product->fetch();

if (!$product) {
    flash('error', 'Product not found.');
    redirect('modules/products/index.php');
}

// Soft-delete (set is_active = 0) to preserve stock movement history
$db->prepare('UPDATE products SET is_active = 0 WHERE id = ?')->execute([$id]);

flash('success', '"' . $product['name'] . '" has been removed.');
redirect('modules/products/index.php');
