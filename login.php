<?php
require_once __DIR__ . '/bootstrap.php';

if (!empty($_SESSION['user_id'])) {
    redirect('modules/dashboard/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } elseif (Auth::login($username, $password)) {
        flash('success', 'Welcome back, ' . $_SESSION['full_name'] . '!');
        redirect('modules/dashboard/index.php');
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
</head>
<body>
<div class="login-page">
  <div class="login-card">
    <div class="login-logo">
      <div class="logo-box">📦</div>
      <h1><?= APP_NAME ?></h1>
      <p>Sign in to manage your inventory</p>
    </div>

    <div class="demo-box">
      <strong>Demo credentials</strong><br>
      Admin: <strong>admin</strong> / <strong>demo1234</strong><br>
      Staff: <strong>staff1</strong> / <strong>demo1234</strong>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger" style="margin-bottom:16px">
        <i class="fa fa-circle-xmark"></i> <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input id="username" name="username" type="text" class="form-control"
               placeholder="Enter username" value="<?= e($_POST['username'] ?? '') ?>" autofocus required>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div style="position:relative">
          <input id="password" name="password" type="password" class="form-control"
                 placeholder="Enter password" required style="padding-right:40px">
          <button type="button" onclick="togglePwd()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8" id="pwd-toggle">
            <i class="fa fa-eye" id="pwd-icon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100" style="margin-top:4px;padding:11px">
        <i class="fa fa-right-to-bracket"></i> Sign In
      </button>
    </form>
  </div>
</div>
<script>
function togglePwd() {
  const i = document.getElementById('password');
  const icon = document.getElementById('pwd-icon');
  if (i.type === 'password') { i.type = 'text'; icon.className = 'fa fa-eye-slash'; }
  else { i.type = 'password'; icon.className = 'fa fa-eye'; }
}
</script>
</body>
</html>
