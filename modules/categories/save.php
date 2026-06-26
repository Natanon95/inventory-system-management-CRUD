<?php
require_once __DIR__ . '/../../bootstrap.php';
Auth::requireAdmin();
csrfVerify();

$db     = Database::getInstance();
$action = $_POST['action'] ?? '';
$name   = trim($_POST['name'] ?? '');
$desc   = trim($_POST['description'] ?? '');

if ($name === '') {
    flash('error', 'Category name is required.');
    redirect('modules/categories/index.php');
}

if ($action === 'add') {
    try {
        $db->prepare('INSERT INTO categories (name, description) VALUES (?, ?)')->execute([$name, $desc]);
        flash('success', "Category \"$name\" added.");
    } catch (PDOException $e) {
        flash('error', 'Category name already exists.');
    }
} elseif ($action === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    try {
        $db->prepare('UPDATE categories SET name=?, description=? WHERE id=?')->execute([$name, $desc, $id]);
        flash('success', "Category updated.");
    } catch (PDOException $e) {
        flash('error', 'Category name already exists.');
    }
}

redirect('modules/categories/index.php');
