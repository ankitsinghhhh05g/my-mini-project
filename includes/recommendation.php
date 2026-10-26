<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

/**
 * A transparent rule-based recommender. It weighs profile keywords, career goals,
 * existing skills, and low assessment scores. It is intentionally readable so it
 * can be explained and extended in a mini-project viva.
 */
function learning_recommendations(int $userId): array
{
    $profileStmt = db()->prepare('SELECT * FROM student_profiles WHERE user_id = ?');
    $profileStmt->execute([$userId]);
    $profile = $profileStmt->fetch() ?: [];
    $profileText = strtolower(implode(' ', [
        $profile['interests'] ?? '', $profile['career_goal'] ?? '', $profile['existing_skills'] ?? ''
    ]));

    $latest = db()->prepare('SELECT id FROM assessments WHERE user_id = ? ORDER BY completed_at DESC, id DESC LIMIT 1');
    $latest->execute([$userId]);
    $assessmentId = $latest->fetchColumn();
    $skillScores = [];
    if ($assessmentId) {
        $scoreStmt = db()->prepare(
            'SELECT s.id, s.name, s.category, COUNT(q.id) AS total, SUM(aa.selected_option = q.correct_option) AS correct
             FROM skills s JOIN assessment_questions q ON q.skill_id = s.id
             LEFT JOIN assessment_answers aa ON aa.question_id = q.id AND aa.assessment_id = ?
             GROUP BY s.id, s.name, s.category'
        );
        $scoreStmt->execute([$assessmentId]);
        foreach ($scoreStmt as $row) {
            $skillScores[(int) $row['id']] = $row;
        }
    }

    $courses = db()->query(
        'SELECT c.*, GROUP_CONCAT(s.name SEPARATOR "|") AS skill_names, GROUP_CONCAT(s.id) AS skill_ids
         FROM courses c
         LEFT JOIN course_skills cs ON cs.course_id = c.id
         LEFT JOIN skills s ON s.id = cs.skill_id
         GROUP BY c.id ORDER BY c.id'
    )->fetchAll();

    foreach ($courses as &$course) {
        $score = 0;
        $reasons = [];
        $category = strtolower($course['category']);
        if (str_contains($profileText, $category)) {
            $score += 5; $reasons[] = 'matches your interests or career goal';
        }
        foreach (explode('|', (string) $course['skill_names']) as $index => $skillName) {
            if ($skillName === '') { continue; }
            $skillId = (int) (explode(',', (string) $course['skill_ids'])[$index] ?? 0);
            if (str_contains($profileText, strtolower($skillName))) {
                $score += 4; $reasons[] = 'builds on ' . $skillName;
            }
            if (isset($skillScores[$skillId]) && (int) $skillScores[$skillId]['correct'] < (int) $skillScores[$skillId]['total']) {
                $score += 3; $reasons[] = 'strengthens ' . $skillName;
            }
        }
        if ($score === 0 && !$assessmentId) { $score = 1; $reasons[] = 'a useful beginner foundation'; }
        $course['match_score'] = $score;
        $course['reason'] = $reasons ? ucfirst($reasons[0]) . '.' : 'A practical learning next step.';
    }
    unset($course);
    usort($courses, static fn(array $a, array $b): int => $b['match_score'] <=> $a['match_score']);

    $skills = db()->query('SELECT id, name, category, description FROM skills ORDER BY category, name')->fetchAll();
    foreach ($skills as &$skill) {
        $skill['priority'] = 0;
        $skill['reason'] = 'A valuable foundation.';
        if (str_contains($profileText, strtolower($skill['name'])) || str_contains($profileText, strtolower($skill['category']))) {
            $skill['priority'] += 4; $skill['reason'] = 'Aligned with your profile.';
        }
        if (isset($skillScores[(int) $skill['id']])) {
            $result = $skillScores[(int) $skill['id']];
            if ((int) $result['correct'] < (int) $result['total']) {
                $skill['priority'] += 5;
                $skill['reason'] = 'A growth opportunity from your latest assessment.';
            } else {
                $skill['priority'] += 2;
                $skill['reason'] = 'A confirmed strength to build on.';
            }
        }
    }
    unset($skill);
    usort($skills, static fn(array $a, array $b): int => $b['priority'] <=> $a['priority']);
    return ['profile' => $profile, 'assessment_id' => $assessmentId, 'courses' => $courses, 'skills' => $skills, 'skill_scores' => $skillScores];
}
