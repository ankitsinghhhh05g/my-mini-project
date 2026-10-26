<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_login(); $userId = (int) current_user()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $courseId = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
    if ($_POST['action'] === 'save' && $courseId) {
        $exists = db()->prepare('SELECT id FROM courses WHERE id = ?'); $exists->execute([$courseId]);
        if ($exists->fetch()) {
            db()->prepare('INSERT INTO saved_courses (user_id, course_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE updated_at = updated_at')->execute([$userId, $courseId]);
            flash('success', 'Course saved to your learning list.');
        }
    }
    redirect('courses.php');
}
$search = trim($_GET['q'] ?? ''); $category = trim($_GET['category'] ?? ''); $level = trim($_GET['level'] ?? '');
$where = []; $params = [];
if ($search !== '') { $where[] = '(c.title LIKE ? OR c.provider LIKE ? OR c.description LIKE ?)'; $like = "%{$search}%"; array_push($params, $like, $like, $like); }
if ($category !== '') { $where[] = 'c.category = ?'; $params[] = $category; }
if (in_array($level, ['Beginner','Intermediate','Advanced'], true)) { $where[] = 'c.level = ?'; $params[] = $level; }
$sql = 'SELECT c.*, GROUP_CONCAT(s.name SEPARATOR ", ") AS skill_names FROM courses c LEFT JOIN course_skills cs ON cs.course_id = c.id LEFT JOIN skills s ON s.id = cs.skill_id';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); } $sql .= ' GROUP BY c.id ORDER BY c.category, c.title';
$stmt = db()->prepare($sql); $stmt->execute($params); $courses = $stmt->fetchAll();
$categories = db()->query('SELECT DISTINCT category FROM courses ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
$savedStmt = db()->prepare('SELECT course_id FROM saved_courses WHERE user_id = ?'); $savedStmt->execute([$userId]); $saved = array_flip(array_map('intval', $savedStmt->fetchAll(PDO::FETCH_COLUMN)));
$page_title = 'Course catalog'; require __DIR__ . '/includes/header.php';
?>
<section class="container py-4 py-lg-5"><div class="mb-4"><p class="text-primary fw-semibold mb-1">COURSE CATALOG</p><h1 class="section-title">Find your next course.</h1><p class="text-secondary mb-0">Search the curated learning resources and save the ones that fit your plan.</p></div>
<form class="card p-3 mb-4" method="get"><div class="row g-2"><div class="col-lg-5"><input class="form-control" name="q" value="<?= e($search) ?>" placeholder="Search courses or providers"></div><div class="col-sm-4 col-lg-3"><select class="form-select" name="category"><option value="">All categories</option><?php foreach ($categories as $item): ?><option value="<?= e($item) ?>" <?= $category === $item ? 'selected' : '' ?>><?= e($item) ?></option><?php endforeach; ?></select></div><div class="col-sm-4 col-lg-2"><select class="form-select" name="level"><option value="">All levels</option><?php foreach (['Beginner','Intermediate','Advanced'] as $item): ?><option <?= $level === $item ? 'selected' : '' ?>><?= $item ?></option><?php endforeach; ?></select></div><div class="col-sm-4 col-lg-2"><button class="btn btn-primary w-100">Filter</button></div></div></form>
<div class="d-flex justify-content-between align-items-center mb-3"><span class="text-secondary small"><?= count($courses) ?> course<?= count($courses) === 1 ? '' : 's' ?> found</span><a class="small" href="courses.php">Clear filters</a></div>
<div class="row g-4"><?php foreach ($courses as $course): ?><div class="col-md-6 col-xl-4"><article class="card course-card card-hover h-100 p-4 d-flex flex-column"><div class="d-flex justify-content-between gap-2 mb-3"><span class="badge text-bg-light border text-secondary"><?= e($course['category']) ?></span><span class="badge skill-pill"><?= e($course['level']) ?></span></div><h5 class="course-title"><?= e($course['title']) ?></h5><p class="small text-secondary mb-3"><?= e($course['provider']) ?> · <?= e($course['duration']) ?></p><p class="small flex-grow-1"><?= e($course['description']) ?></p><?php if ($course['skill_names']): ?><p class="small mb-3"><span class="text-secondary">Skills:</span> <?= e($course['skill_names']) ?></p><?php endif; ?><div class="d-flex gap-2 mt-auto"><?php if ($course['course_url']): ?><a class="btn btn-outline-secondary flex-fill" target="_blank" rel="noopener" href="<?= e($course['course_url']) ?>">Visit</a><?php endif; ?><?php if (isset($saved[(int) $course['id']])): ?><a class="btn btn-outline-primary flex-fill" href="saved-courses.php">Saved</a><?php else: ?><form class="flex-fill" method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>"><input type="hidden" name="action" value="save"><button class="btn btn-primary w-100">Save</button></form><?php endif; ?></div></article></div><?php endforeach; ?></div>
<?php if (!$courses): ?><div class="card p-5 text-center text-secondary">No courses match those filters. <a href="courses.php">View the full catalog</a>.</div><?php endif; ?></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
