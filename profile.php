<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_login(); $user = current_user(); $userId = (int) $user['id'];
$stmt = db()->prepare('SELECT * FROM student_profiles WHERE user_id = ?'); $stmt->execute([$userId]);
$profile = $stmt->fetch() ?: ['education' => '', 'interests' => '', 'existing_skills' => '', 'career_goal' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $profile = ['education' => trim($_POST['education'] ?? ''), 'interests' => trim($_POST['interests'] ?? ''), 'existing_skills' => trim($_POST['existing_skills'] ?? ''), 'career_goal' => trim($_POST['career_goal'] ?? '')];
    $update = db()->prepare('INSERT INTO student_profiles (user_id, education, interests, existing_skills, career_goal) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE education = VALUES(education), interests = VALUES(interests), existing_skills = VALUES(existing_skills), career_goal = VALUES(career_goal)');
    $update->execute([$userId, $profile['education'], $profile['interests'], $profile['existing_skills'], $profile['career_goal']]);
    flash('success', 'Your profile has been updated.'); redirect('profile.php');
}
$skillNames = db()->query('SELECT name FROM skills ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
$page_title = 'My profile'; require __DIR__ . '/includes/header.php';
?>
<section class="container py-4 py-lg-5"><div class="row justify-content-center"><div class="col-lg-8"><div class="mb-4"><p class="text-primary fw-semibold mb-1">STUDENT PROFILE</p><h1 class="section-title">Tell us where you want to go.</h1><p class="text-secondary">We use these details, alongside assessment results, for recommendations.</p></div><div class="card p-4 p-md-5"><form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
  <div class="mb-3"><label class="form-label" for="education">Educational background</label><input class="form-control" id="education" name="education" maxlength="150" value="<?= e($profile['education']) ?>" placeholder="e.g. TYBSc Computer Science"></div>
  <div class="mb-3"><label class="form-label" for="interests">Interests</label><textarea class="form-control" id="interests" name="interests" rows="3" placeholder="e.g. web development, data analysis, designing interfaces"><?= e($profile['interests']) ?></textarea><div class="form-text">Separate interests with commas for best results.</div></div>
  <div class="mb-3"><label class="form-label" for="existing_skills">Existing skills</label><textarea class="form-control" id="existing_skills" name="existing_skills" rows="3" placeholder="e.g. HTML, basic Python, communication"><?= e($profile['existing_skills']) ?></textarea><div class="form-text">Suggested skills: <?= e(implode(', ', $skillNames)) ?></div></div>
  <div class="mb-4"><label class="form-label" for="career_goal">Career goal</label><input class="form-control" id="career_goal" name="career_goal" maxlength="180" value="<?= e($profile['career_goal']) ?>" placeholder="e.g. Become a full-stack web developer"></div>
  <div class="d-flex justify-content-end"><button class="btn btn-primary px-4">Save profile</button></div>
</form></div></div></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
