<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$submissionId = $_GET['submission_id'] ?? null;

if ($submissionId) {
    // Mark submission as completed
    $stmt = $pdo->prepare("UPDATE submissions SET status = 'completed', completed_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
    $stmt->execute([$submissionId, $_SESSION['user_id']]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jules - Exam Finished</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        midnight: '#1a1f3c',
                        neon: '#a855f7',
                        neonHover: '#9333ea',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-midnight text-white font-sans flex items-center justify-center h-screen text-center p-6">

    <div class="max-w-md w-full bg-gray-800 p-8 rounded-lg shadow-lg border-t-4 border-neon">
        <div class="mb-6">
            <svg class="w-20 h-20 text-green-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <h1 class="text-3xl font-bold mb-4">Exam Completed!</h1>
        <p class="text-gray-400 mb-8">Great job. Your recordings have been securely submitted to the Command Center for grading.</p>

        <a href="../student/dashboard.php" class="block w-full bg-neon hover:bg-neonHover text-white font-bold py-3 rounded transition duration-200">
            Return to Dashboard
        </a>
    </div>

</body>
</html>
