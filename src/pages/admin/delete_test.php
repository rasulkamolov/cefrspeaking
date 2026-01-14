<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$testId = $_GET['test_id'] ?? null;

if ($testId) {
    try {
        $pdo->beginTransaction();

        // 1. Get Submissions
        $stmt = $pdo->prepare("SELECT id FROM submissions WHERE test_id = ?");
        $stmt->execute([$testId]);
        $submissionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($submissionIds)) {
            $inQuery = implode(',', array_fill(0, count($submissionIds), '?'));

            // 2. Delete Grades
            $stmt = $pdo->prepare("DELETE FROM grades WHERE submission_id IN ($inQuery)");
            $stmt->execute($submissionIds);

            // 3. Delete Submission Answers
            $stmt = $pdo->prepare("DELETE FROM submission_answers WHERE submission_id IN ($inQuery)");
            $stmt->execute($submissionIds);

            // 4. Delete Submissions
            $stmt = $pdo->prepare("DELETE FROM submissions WHERE test_id = ?");
            $stmt->execute([$testId]);
        }

        // 5. Delete Questions
        $stmt = $pdo->prepare("DELETE FROM test_questions WHERE test_id = ?");
        $stmt->execute([$testId]);

        // 6. Delete Test
        $stmt = $pdo->prepare("DELETE FROM tests WHERE id = ?");
        $stmt->execute([$testId]);

        $pdo->commit();
        header("Location: manage_tests.php?msg=deleted");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error deleting test: " . $e->getMessage());
    }
} else {
    header("Location: manage_tests.php");
}
