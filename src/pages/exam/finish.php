<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$submissionId = $_GET['submission_id'] ?? null;

if ($submissionId) {
    $stmt = $pdo->prepare("UPDATE submissions SET status = 'completed', completed_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$submissionId]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford CEFR Speaking - Exam Finished</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-background text-textMain font-sans h-screen flex flex-col items-center justify-center p-6">

    <div class="bg-white p-8 rounded-xl shadow-lg max-w-md w-full text-center">
        <div class="w-20 h-20 bg-green-100 text-success rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>

        <h1 class="text-3xl font-bold text-textMain mb-2">Exam Completed!</h1>
        <p class="text-textMuted mb-8">
            Your recording has been saved successfully. Our administrators will review your performance shortly.
        </p>

        <a href="../student/dashboard.php" class="block w-full bg-primary hover:bg-primaryHover text-white font-bold py-3 rounded-lg shadow transition">
            Return to Dashboard
        </a>
    </div>

</body>
</html>
