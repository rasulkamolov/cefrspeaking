<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$testId = $_GET['test_id'] ?? null;
$mode = $_GET['mode'] ?? 'full';
$part = $_GET['part'] ?? '1.1';

if (!$testId) {
    header("Location: ../student/dashboard.php");
    exit;
}

// Create Submission
// We create a submission record now to track this attempt
// If mode is 'part', we still create a submission but it might be partial.
// In a real app, we might handle 'practice' differently, but let's just log it.

$stmt = $pdo->prepare("INSERT INTO submissions (user_id, test_id, status) VALUES (?, ?, 'in_progress')");
$stmt->execute([$_SESSION['user_id'], $testId]);
$submissionId = $pdo->lastInsertId();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jules - Start Exam</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-background text-textMain font-sans h-screen flex flex-col items-center justify-center p-6">

    <div class="bg-white p-8 rounded-xl shadow-lg max-w-md w-full text-center">
        <h1 class="text-3xl font-bold text-primary mb-4">Get Ready</h1>
        <p class="text-textMuted mb-8">
            You are about to start <strong><?php echo $mode === 'full' ? 'the full exam' : 'Part ' . $part; ?></strong>.
            Please ensure you are in a quiet environment and your microphone is working.
        </p>

        <div class="space-y-4">
            <div class="flex items-center justify-center space-x-2 text-sm text-gray-500 bg-gray-50 p-3 rounded">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>
                <span>Microphone Check Recommended</span>
            </div>

            <a href="runner.php?submission_id=<?php echo $submissionId; ?>&mode=<?php echo $mode; ?>&part=<?php echo $part; ?>" class="block w-full bg-primary hover:bg-primaryHover text-white font-bold py-4 rounded-lg shadow transition transform hover:scale-105">
                I'm Ready - Start
            </a>

            <a href="../student/dashboard.php" class="block text-textMuted hover:text-gray-800 text-sm">Cancel</a>
        </div>
    </div>

</body>
</html>
