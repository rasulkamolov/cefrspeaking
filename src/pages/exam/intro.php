<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$testId = $_GET['test_id'] ?? null;
$mode = $_GET['mode'] ?? 'full'; // 'full' or 'part'
$part = $_GET['part'] ?? '1.1'; // Only if mode is 'part'

if (!$testId) {
    header("Location: ../student/dashboard.php");
    exit;
}

// Fetch Test Details
$stmt = $pdo->prepare("SELECT * FROM tests WHERE id = ?");
$stmt->execute([$testId]);
$test = $stmt->fetch();

if (!$test) {
    die("Test not found.");
}

// Create a new submission record
// If retaking or starting new, we always create a new submission for simplicity in this model
$stmt = $pdo->prepare("INSERT INTO submissions (user_id, test_id, status) VALUES (?, ?, 'in_progress')");
$stmt->execute([$_SESSION['user_id'], $testId]);
$submissionId = $pdo->lastInsertId();

// Determine starting part
$startPart = ($mode === 'full') ? '1.1' : $part;

// Redirect to Runner
header("Location: runner.php?submission_id=$submissionId&part=$startPart&mode=$mode");
exit;
?>
