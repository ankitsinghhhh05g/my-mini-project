<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/recommendation.php';
require_login();
$user = current_user();
$userId = (int) $user['id'];
$summary = db()->prepare('SELECT COUNT(*) AS saved, SUM(status = "Started") AS started, SUM(status = "Completed") AS completed, ROUND(AVG(progress)) AS avg_progress FROM saved_courses WHERE user_id = ?');
$summary->execute([$userId]); $summary = $summary->fetch();
$assessment = db()->prepare('SELECT score, total_questions, completed_at FROM assessments WHERE user_id = ? ORDER BY completed_at DESC, id DESC LIMIT 1');
$assessment->execute([$userId]); $assessment = $assessment->fetch();
$recent = db()->prepare('SELECT c.*, sc.status, sc.progress FROM saved_courses sc JOIN courses c ON c.id = sc.course_id WHERE sc.user_id = ? ORDER BY sc.updated_at DESC LIMIT 3');
$recent->execute([$userId]); $recent = $recent->fetchAll();
$recommendations = learning_recommendations($userId);
$profileComplete = !empty($recommendations['profile']['education']) && !empty($recommendations['profile']['interests']) && !empty($recommendations['profile']['career_goal']);
$page_title = 'Dashboard'; require __DIR__ . '/includes/header.php';
?>
<section class="container py-4 py-lg-5">
  <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3 mb-4"><div><p class="text-primary fw-semibold mb-1">YOUR LEARNING SPACE</p><h1 class="section-title mb-1">Welcome back, <?= e(explode(' ', $user['full_name'])[0]) ?>.</h1><p class="text-secondary mb-0">A clear next step makes momentum easier.</p></div><a class="btn btn-primary" href="assessment.php">Take skill assessment</a></div>
  <?php if (!$profileComplete): ?><div class="alert alert-primary d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2"><span><strong>Personalize your path:</strong> add your education, interests, and career goal to improve matches.</span><a class="btn btn-sm btn-primary" href="profile.php">Complete profile</a></div><?php endif; ?>
  <div class="row g-3 mb-5">
    <div class="col-6 col-lg-3"><div class="card p-3 h-100"><div class="stat-icon mb-3">📚</div><div class="metric"><?= (int) ($summary['saved'] ?? 0) ?></div><div class="metric-label">Courses saved</div></div></div>
    <div class="col-6 col-lg-3"><div class="card p-3 h-100"><div class="stat-icon mb-3">🚀</div><div class="metric"><?= (int) ($summary['started'] ?? 0) ?></div><div class="metric-label">In progress</div></div></div>
    <div class="col-6 col-lg-3"><div class="card p-3 h-100"><div class="stat-icon mb-3">✓</div><div class="metric"><?= (int) ($summary['completed'] ?? 0) ?></div><div class="metric-label">Completed</div></div></div>
    <div class="col-6 col-lg-3"><div class="card p-3 h-100"><div class="stat-icon mb-3">🎯</div><div class="metric"><?= $assessment ? (int) round(100 * $assessment['score'] / $assessment['total_questions']) . '%' : '—' ?></div><div class="metric-label">Latest assessment</div></div></div>
  </div>
  <div class="row g-4">
    <div class="col-lg-7"><div class="d-flex justify-content-between align-items-center mb-3"><h3 class="h4 mb-0">Continue learning</h3><a href="saved-courses.php" class="small">View all</a></div>
      <?php if ($recent): foreach ($recent as $course): ?><div class="card p-3 mb-3"><div class="d-flex justify-content-between gap-3"><div><h5 class="mb-1"><?= e($course['title']) ?></h5><p class="small text-secondary mb-3"><?= e($course['provider']) ?> · <?= e($course['level']) ?></p></div><span class="badge align-self-start status-<?= strtolower($course['status']) ?>"><?= e($course['status']) ?></span></div><div class="d-flex justify-content-between small mb-1"><span>Progress</span><span><?= (int) $course['progress'] ?>%</span></div><div class="progress"><div class="progress-bar" style="width:<?= (int) $course['progress'] ?>%"></div></div></div><?php endforeach; else: ?><div class="card p-4 text-center text-secondary">No courses saved yet. <a href="courses.php">Browse the catalog</a> to get started.</div><?php endif; ?>
    </div>
    <div class="col-lg-5"><div class="d-flex justify-content-between align-items-center mb-3"><h3 class="h4 mb-0">Your next skills</h3><a href="recommendations.php" class="small">See all</a></div><div class="card p-3">
      <?php foreach (array_slice($recommendations['skills'], 0, 4) as $skill): ?><div class="py-2 border-bottom last-border-0"><div class="d-flex justify-content-between align-items-start gap-2"><div><strong><?= e($skill['name']) ?></strong><div class="small text-secondary"><?= e($skill['reason']) ?></div></div><span class="badge skill-pill"><?= e($skill['category']) ?></span></div></div><?php endforeach; ?>
    </div></div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
