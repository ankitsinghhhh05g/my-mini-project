<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) { redirect('dashboard.php'); }

$values = ['full_name' => '', 'email' => ''];
$form_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $values['full_name'] = trim($_POST['full_name'] ?? '');
    $values['email'] = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (strlen($values['full_name']) < 2 || !filter_var($values['email'], FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $form_error = 'Enter your name, a valid email address, and a password of at least 8 characters.';
    } elseif ($password !== $confirm) {
        $form_error = 'The password confirmation does not match.';
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)');
            $stmt->execute([$values['full_name'], $values['email'], password_hash($password, PASSWORD_DEFAULT)]);
            $id = (int) db()->lastInsertId();
            db()->prepare('INSERT INTO student_profiles (user_id) VALUES (?)')->execute([$id]);
            login_user($id);
            flash('success', 'Welcome to SkillPath. Complete your profile to personalize your recommendations.');
            redirect('profile.php');
        } catch (PDOException $e) {
            $form_error = $e->getCode() === '23000' ? 'That email address already has an account.' : 'Unable to create the account right now.';
        }
    }
}
$page_title = 'Create account';
require __DIR__ . '/includes/header.php';
?>
<div class="container"><div class="auth-wrap"><div class="text-center mb-4"><h1 class="section-title">Create your account</h1><p class="text-secondary">Start shaping your learning roadmap.</p></div><div class="card p-4 p-md-5">
<?php if ($form_error): ?><div class="alert alert-danger"><?= e($form_error) ?></div><?php endif; ?>
<form method="post" novalidate><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
  <div class="mb-3"><label class="form-label" for="full_name">Full name</label><input class="form-control" id="full_name" name="full_name" value="<?= e($values['full_name']) ?>" required autofocus></div>
  <div class="mb-3"><label class="form-label" for="email">Email address</label><input class="form-control" type="email" id="email" name="email" value="<?= e($values['email']) ?>" required></div>
  <div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control" type="password" id="password" name="password" minlength="8" required><div class="form-text">Use at least 8 characters.</div></div>
  <div class="mb-4"><label class="form-label" for="confirm_password">Confirm password</label><input class="form-control" type="password" id="confirm_password" name="confirm_password" required></div>
  <button class="btn btn-primary w-100 py-2">Create account</button>
</form></div><p class="text-center small text-secondary mt-3">Already learning with us? <a href="login.php">Log in</a></p></div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
