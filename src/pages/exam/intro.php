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
$stmt = $pdo->prepare("INSERT INTO submissions (user_id, test_id, status) VALUES (?, ?, 'in_progress')");
$stmt->execute([$_SESSION['user_id'], $testId]);
$submissionId = $pdo->lastInsertId();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Oxford CEFR - Start Exam</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-primary text-white font-sans h-screen flex flex-col items-center justify-center p-6 relative overflow-hidden">

    <!-- Background Decor -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-accent opacity-20 rounded-full blur-3xl -ml-20 -mt-20"></div>
    <div class="absolute bottom-0 right-0 w-80 h-80 bg-blue-500 opacity-20 rounded-full blur-3xl -mr-20 -mb-20"></div>

    <div class="relative z-10 w-full max-w-sm animate-fade-in-down">

        <div class="bg-surface text-textMain p-8 rounded-3xl shadow-2xl w-full text-center">

            <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>
            </div>

            <h1 class="text-2xl font-bold text-textMain mb-2">Get Ready</h1>
            <p class="text-textMuted text-sm mb-8 leading-relaxed">
                You are about to start <strong><?php echo $mode === 'full' ? 'the full exam' : 'Part ' . $part; ?></strong>.
                Find a quiet place.
            </p>

            <div class="space-y-4">
                <a href="runner.php?submission_id=<?php echo $submissionId; ?>&mode=<?php echo $mode; ?>&part=<?php echo $part; ?>" class="block w-full bg-primary hover:bg-primaryLight text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-500/25 transition transform active:scale-95">
                    Start Now
                </a>

                <a href="../student/dashboard.php" class="block text-textMuted hover:text-textMain text-sm font-medium py-2">Cancel</a>
            </div>
        </div>

        <p class="text-center text-indigo-200 text-xs mt-8">
            <svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            Session Secured
        </p>

    </div>

</body>
</html>
