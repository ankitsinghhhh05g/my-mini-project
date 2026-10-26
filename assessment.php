<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_login(); $userId = (int) current_user()['id'];
$questions = db()->query('SELECT q.*, s.name AS skill_name, s.category FROM assessment_questions q JOIN skills s ON s.id = q.skill_id ORDER BY q.skill_id, q.id')->fetchAll();
$form_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $answers = $_POST['answer'] ?? [];
    if (count($answers) !== count($questions)) {
        $form_error = 'Please answer every question before submitting the assessment.';
    } else {
        $score = 0;
        foreach ($questions as $question) {
            $selected = $answers[$question['id']] ?? '';
            if (!in_array($selected, ['A', 'B', 'C', 'D'], true)) { $form_error = 'One or more responses are invalid.'; break; }
            if ($selected === $question['correct_option']) { $score++; }
        }
        if (!$form_error) {
            $pdo = db(); $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('INSERT INTO assessments (user_id, score, total_questions) VALUES (?, ?, ?)');
                $stmt->execute([$userId, $score, count($questions)]); $assessmentId = (int) $pdo->lastInsertId();
                $insert = $pdo->prepare('INSERT INTO assessment_answers (assessment_id, question_id, selected_option) VALUES (?, ?, ?)');
                foreach ($questions as $question) { $insert->execute([$assessmentId, $question['id'], $answers[$question['id']]]); }
                $pdo->commit();
                flash('success', "Assessment complete: {$score} out of " . count($questions) . '. Your recommendations have been refreshed.');
                redirect('recommendations.php');
            } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
        }
    }
}
$page_title = 'Skill assessment'; require __DIR__ . '/includes/header.php';
?>
<section class="container py-4 py-lg-5"><div class="row justify-content-center"><div class="col-lg-9"><div class="mb-4"><p class="text-primary fw-semibold mb-1">SKILL ASSESSMENT</p><h1 class="section-title">Check your current foundations.</h1><p class="text-secondary">Choose the answer you think is best. The results are used to find skills worth building next.</p></div>
<?php if ($form_error): ?><div class="alert alert-danger"><?= e($form_error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
<?php foreach ($questions as $position => $question): ?><article class="card p-4 mb-3"><div class="d-flex gap-3"><span class="badge text-bg-primary align-self-start rounded-pill"><?= $position + 1 ?></span><div class="flex-grow-1"><div class="small text-primary fw-semibold mb-2"><?= e($question['skill_name']) ?> · <?= e($question['category']) ?></div><h5 class="mb-3"><?= e($question['question']) ?></h5><?php foreach (['A','B','C','D'] as $letter): $field = 'option_' . strtolower($letter); ?><label class="assessment-option border rounded-3 p-3 d-flex gap-2 mb-2"><input class="form-check-input flex-shrink-0" type="radio" name="answer[<?= (int) $question['id'] ?>]" value="<?= $letter ?>" <?= (($_POST['answer'][$question['id']] ?? '') === $letter) ? 'checked' : '' ?> required><span><strong><?= $letter ?>.</strong> <?= e($question[$field]) ?></span></label><?php endforeach; ?></div></div></article><?php endforeach; ?>
<div class="d-flex justify-content-between align-items-center mt-4"><span class="small text-secondary"><?= count($questions) ?> questions · No time limit</span><button class="btn btn-primary px-4">Submit assessment</button></div>
</form></div></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
