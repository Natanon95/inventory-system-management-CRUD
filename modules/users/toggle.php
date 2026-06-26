<?php
require_once __DIR__ . '/../../bootstrap.php';
Auth::requireAdmin();

$db = Database::getInstance();
$id = (int)($_GET['id'] ?? 0);

// Prevent self-deactivation
if ($id === Auth::id()) {
    flash('error', 'You cannot deactivate your own account.');
    redirect('modules/users/index.php');
}

$db->prepare('UPDATE users SET is_active = NOT is_active WHERE id = ?')->execute([$id]);
flash('success', 'User status updated.');
redirect('modules/users/index.php');
