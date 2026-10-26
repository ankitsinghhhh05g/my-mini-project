<?php
/** @var string $page_title */
require_once __DIR__ . '/auth.php';
$user = current_user();
$success = flash('success');
$error = flash('error');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title ?? APP_NAME) ?> · <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark app-nav sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><span class="brand-dot">✦</span> SkillPath</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
      <?php if ($user): ?>
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
        <li class="nav-item"><a class="nav-link" href="recommendations.php">Recommendations</a></li>
        <?php if ($user['role'] === 'admin'): ?><li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li><?php endif; ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"><?= e($user['full_name']) ?></a>
          <ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="profile.php">My profile</a></li><li><a class="dropdown-item" href="saved-courses.php">Saved courses</a></li><li><hr class="dropdown-divider"></li><li><a class="dropdown-item" href="logout.php">Log out</a></li></ul>
        </li>
      <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="login.php">Log in</a></li>
        <li class="nav-item"><a class="btn btn-light btn-sm px-3 ms-lg-2" href="register.php">Create account</a></li>
      <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<main>
<?php if ($success || $error): ?><div class="container pt-3"><div class="alert alert-<?= $success ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert"><?= e($success ?: $error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div><?php endif; ?>
