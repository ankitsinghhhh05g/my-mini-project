<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/recommendation.php';
require_login(); $userId = (int) current_user()['id'];
$data = learning_recommendations($userId);
$savedStmt = db()->prepare('SELECT course_id FROM saved_courses WHERE user_id = ?'); $savedStmt->execute([$userId]);
$saved = array_flip(array_map('intval', $savedStmt->fetchAll(PDO::FETCH_COLUMN)));
$page_title = 'Recommendations'; require __DIR__ . '/includes/header.php';
?>
<section class="container py-4 py-lg-5"><div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3 mb-5"><div><p class="text-primary fw-semibold mb-1">PERSONALIZED FOR YOU</p><h1 class="section-title mb-1">Your learning recommendations</h1><p class="text-secondary mb-0">A transparent match based on your profile<?= $data['assessment_id'] ? ' and latest assessment.' : '. Take the assessment to make it sharper.' ?></p></div><a class="btn btn-outline-primary" href="assessment.php">Retake assessment</a></div>
  <h2 class="h4 mb-3">Skills to focus on</h2><div class="row g-3 mb-5"><?php foreach (array_slice($data['skills'], 0, 4) as $skill): ?><div class="col-md-6 col-xl-3"><div class="card h-100 p-3"><span class="badge skill-pill align-self-start mb-3"><?= e($skill['category']) ?></span><h5><?= e($skill['name']) ?></h5><p class="small text-secondary mb-0"><?= e($skill['reason']) ?></p></div></div><?php endforeach; ?></div>
  <div class="d-flex align-items-center justify-content-between mb-3"><h2 class="h4 mb-0">Recommended courses</h2><a href="courses.php" class="small">Browse all courses</a></div><div class="row g-4">
  <?php foreach (array_slice($data['courses'], 0, 6) as $course): ?><div class="col-md-6 col-lg-4"><div class="card course-card card-hover h-100 p-4 d-flex flex-column"><div class="d-flex justify-content-between gap-2 mb-3"><span class="badge text-bg-light border text-secondary"><?= e($course['category']) ?></span><span class="badge skill-pill"><?= e($course['level']) ?></span></div><h5 class="course-title"><?= e($course['title']) ?></h5><p class="small text-secondary mb-3"><?= e($course['provider']) ?> · <?= e($course['duration']) ?></p><p class="small flex-grow-1"><?= e($course['description']) ?></p><div class="soft-panel rounded-3 px-3 py-2 small mb-3"><strong>Why this fits:</strong> <?= e($course['reason']) ?></div><?php if (isset($saved[(int) $course['id']])): ?><a class="btn btn-outline-primary w-100" href="saved-courses.php">View saved course</a><?php else: ?><form method="post" action="courses.php"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>"><input type="hidden" name="action" value="save"><button class="btn btn-primary w-100">Save course</button></form><?php endif; ?></div></div><?php endforeach; ?>
  </div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
