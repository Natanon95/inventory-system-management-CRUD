<?php
require_once __DIR__ . '/../../bootstrap.php';
Auth::requireAdmin();
csrfVerify();

$db     = Database::getInstance();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $username  = trim($_POST['username']  ?? '');
        $full_name = trim($_POST['full_name']  ?? '');
        $email     = trim($_POST['email']      ?? '');
        $password  = $_POST['password']        ?? '';
        $role      = in_array($_POST['role'] ?? '', ['admin','staff']) ? $_POST['role'] : 'staff';

        if (!$username || !$full_name || !$email || strlen($password) < 8) {
            flash('error', 'All fields required; password min 8 chars.');
            redirect('modules/users/index.php');
        }

        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $db->prepare('INSERT INTO users (username, password, full_name, email, role) VALUES (?,?,?,?,?)')
               ->execute([$username, $hash, $full_name, $email, $role]);
            flash('success', "User \"$username\" created.");
        } catch (PDOException) {
            flash('error', 'Username or email already exists.');
        }
        break;

    case 'reset_password':
        $id       = (int)($_POST['id']       ?? 0);
        $password = $_POST['password']       ?? '';
        if (!$id || strlen($password) < 8) {
            flash('error', 'Password must be at least 8 characters.');
            redirect('modules/users/index.php');
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db->prepare('UPDATE users SET password=? WHERE id=?')->execute([$hash, $id]);
        flash('success', 'Password reset successfully.');
        break;
}

redirect('modules/users/index.php');
