<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$testId = $_GET['test_id'] ?? null;
$questionId = $_GET['question_id'] ?? null;

if ($testId && $questionId) {
    $stmt = $pdo->prepare("DELETE FROM test_questions WHERE id = ? AND test_id = ?");
    $stmt->execute([$questionId, $testId]);
    header("Location: edit_test.php?test_id=" . $testId . "&msg=deleted");
} else {
    header("Location: manage_tests.php");
}
