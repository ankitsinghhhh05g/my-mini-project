<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) { redirect('dashboard.php'); }
$email = ''; $form_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $account = $stmt->fetch();
    if ($account && password_verify($password, $account['password_hash'])) {
        login_user((int) $account['id']);
        redirect('dashboard.php');
    }
    $form_error = 'Email address or password is incorrect.';
}
$page_title = 'Log in'; require __DIR__ . '/includes/header.php';
?>
<div class="container"><div class="auth-wrap"><div class="text-center mb-4"><h1 class="section-title">Welcome back</h1><p class="text-secondary">Continue your learning journey.</p></div><div class="card p-4 p-md-5">
<?php if ($form_error): ?><div class="alert alert-danger"><?= e($form_error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
  <div class="mb-3"><label class="form-label" for="email">Email address</label><input class="form-control" type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus></div>
  <div class="mb-4"><label class="form-label" for="password">Password</label><input class="form-control" type="password" id="password" name="password" required></div>
  <button class="btn btn-primary w-100 py-2">Log in</button>
</form></div><p class="text-center small text-secondary mt-3">New to SkillPath? <a href="register.php">Create an account</a></p></div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
