<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_login(); $userId = (int) current_user()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(); $courseId = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT); $status = $_POST['status'] ?? ''; $progress = filter_input(INPUT_POST, 'progress', FILTER_VALIDATE_INT);
    if ($courseId && in_array($status, ['Saved','Started','Completed'], true) && $progress !== false && $progress >= 0 && $progress <= 100) {
        if ($status === 'Completed') { $progress = 100; }
        if ($progress > 0 && $status === 'Saved') { $status = 'Started'; }
        db()->prepare('UPDATE saved_courses SET status = ?, progress = ? WHERE user_id = ? AND course_id = ?')->execute([$status, $progress, $userId, $courseId]);
        flash('success', 'Learning progress updated.');
    }
    redirect('saved-courses.php');
}
$stmt = db()->prepare('SELECT c.*, sc.status, sc.progress, sc.updated_at FROM saved_courses sc JOIN courses c ON c.id = sc.course_id WHERE sc.user_id = ? ORDER BY FIELD(sc.status, "Started", "Saved", "Completed"), sc.updated_at DESC');
$stmt->execute([$userId]); $courses = $stmt->fetchAll();
$page_title = 'Saved courses'; require __DIR__ . '/includes/header.php';
?>
<section class="container py-4 py-lg-5"><div class="d-flex justify-content-between align-items-end mb-4"><div><p class="text-primary fw-semibold mb-1">MY LEARNING PLAN</p><h1 class="section-title mb-1">Saved courses</h1><p class="text-secondary mb-0">Update your progress as you learn.</p></div><a class="btn btn-outline-primary" href="courses.php">Browse courses</a></div>
<?php if ($courses): ?><div class="row g-4"><?php foreach ($courses as $course): ?><div class="col-lg-6"><article class="card p-4 h-100"><div class="d-flex justify-content-between gap-3"><div><span class="badge text-bg-light border text-secondary mb-2"><?= e($course['category']) ?></span><h5 class="mb-1"><?= e($course['title']) ?></h5><p class="small text-secondary mb-3"><?= e($course['provider']) ?> · <?= e($course['duration']) ?></p></div><span class="badge align-self-start status-<?= strtolower($course['status']) ?>"><?= e($course['status']) ?></span></div><div class="d-flex justify-content-between small mb-1"><span>Progress</span><span><?= (int) $course['progress'] ?>%</span></div><div class="progress mb-3"><div class="progress-bar" style="width:<?= (int) $course['progress'] ?>%"></div></div>
<form method="post" class="row g-2 align-items-end"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>"><div class="col-sm-5"><label class="form-label small mb-1">Status</label><select class="form-select form-select-sm" name="status"><?php foreach (['Saved','Started','Completed'] as $option): ?><option <?= $course['status'] === $option ? 'selected' : '' ?>><?= $option ?></option><?php endforeach; ?></select></div><div class="col-sm-4"><label class="form-label small mb-1">Progress (%)</label><input class="form-control form-control-sm" type="number" name="progress" min="0" max="100" value="<?= (int) $course['progress'] ?>"></div><div class="col-sm-3"><button class="btn btn-primary btn-sm w-100">Update</button></div></form></article></div><?php endforeach; ?></div><?php else: ?><div class="card p-5 text-center"><div class="fs-2 mb-2">📚</div><h4>Your learning list is empty.</h4><p class="text-secondary">Browse the course catalog or choose from your recommendations.</p><div><a class="btn btn-primary" href="courses.php">Explore courses</a></div></div><?php endif; ?></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
