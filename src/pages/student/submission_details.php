<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$submissionId = $_GET['submission_id'] ?? null;
$submission = null;
$answers = [];
$grade = null;

if ($submissionId) {
    // Fetch Submission Details & Verify Ownership
    $stmt = $pdo->prepare("
        SELECT s.*, t.title
        FROM submissions s
        JOIN tests t ON s.test_id = t.id
        WHERE s.id = ? AND s.user_id = ?
    ");
    $stmt->execute([$submissionId, $_SESSION['user_id']]);
    $submission = $stmt->fetch();

    if ($submission) {
        // Fetch Answers (Audio)
        $stmt = $pdo->prepare("
            SELECT sa.*, tq.part_type, tq.content, tq.sequence
            FROM submission_answers sa
            JOIN test_questions tq ON sa.question_id = tq.id
            WHERE sa.submission_id = ?
            ORDER BY tq.part_type, tq.sequence
        ");
        $stmt->execute([$submissionId]);
        $answers = $stmt->fetchAll();

        // Fetch Grade/Feedback
        $stmt = $pdo->prepare("SELECT * FROM grades WHERE submission_id = ?");
        $stmt->execute([$submissionId]);
        $grade = $stmt->fetch();
    } else {
        header("Location: history.php");
        exit;
    }
} else {
    header("Location: history.php");
    exit;
}

// Calculate Overall Score if graded
$overall = null;
if ($grade) {
    $overall = ($grade['range_score'] + $grade['accuracy_score'] + $grade['fluency_score'] + $grade['coherence_score']) / 4;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford CEFR Speaking - Exam Details</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-background text-textMain font-sans flex flex-col h-screen-dvh overflow-hidden">

    <!-- Top Header -->
    <header class="bg-primary text-white pt-safe z-20 shadow-md flex-none">
        <div class="px-6 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <a href="history.php" class="mr-4 text-indigo-200 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <h1 class="text-xl font-bold tracking-tight">Exam Details</h1>
            </div>
             <div class="w-8"></div>
        </div>
    </header>

    <main class="container mx-auto px-4 mt-6 max-w-lg flex-1 overflow-y-auto pb-24">

        <!-- Summary Card -->
        <div class="bg-surface p-5 rounded-2xl shadow-sm border border-gray-100 mb-6">
            <h2 class="text-xl font-bold text-textMain mb-1"><?php echo htmlspecialchars($submission['title']); ?></h2>
            <p class="text-textMuted text-sm mb-4"><?php echo date('M j, Y • H:i', strtotime($submission['started_at'])); ?></p>

            <?php if ($grade): ?>
                <div class="flex items-center justify-between bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                    <div>
                        <span class="text-xs text-textMuted uppercase font-bold tracking-wider">Overall Score</span>
                        <div class="text-3xl font-bold text-primary"><?php echo number_format($overall, 1); ?></div>
                    </div>
                    <?php if ($grade['comments']): ?>
                        <div class="text-sm text-textMuted italic max-w-[60%] text-right">"<?php echo htmlspecialchars($grade['comments']); ?>"</div>
                    <?php endif; ?>
                </div>

                <!-- Score Breakdown -->
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="text-center p-2 bg-gray-50 rounded-lg">
                        <span class="block text-xs text-textMuted font-bold uppercase">Range</span>
                        <span class="text-lg font-bold text-textMain"><?php echo $grade['range_score']; ?></span>
                    </div>
                    <div class="text-center p-2 bg-gray-50 rounded-lg">
                        <span class="block text-xs text-textMuted font-bold uppercase">Accuracy</span>
                        <span class="text-lg font-bold text-textMain"><?php echo $grade['accuracy_score']; ?></span>
                    </div>
                    <div class="text-center p-2 bg-gray-50 rounded-lg">
                        <span class="block text-xs text-textMuted font-bold uppercase">Fluency</span>
                        <span class="text-lg font-bold text-textMain"><?php echo $grade['fluency_score']; ?></span>
                    </div>
                    <div class="text-center p-2 bg-gray-50 rounded-lg">
                        <span class="block text-xs text-textMuted font-bold uppercase">Coherence</span>
                        <span class="text-lg font-bold text-textMain"><?php echo $grade['coherence_score']; ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-yellow-50 text-yellow-800 p-4 rounded-xl text-center text-sm font-medium">
                    This exam is currently being graded. Check back later for your score and feedback.
                </div>
            <?php endif; ?>
        </div>

        <!-- Responses -->
        <h3 class="text-textMuted text-xs font-bold uppercase tracking-wider mb-3 ml-4">Your Responses</h3>
        <div class="space-y-4">
            <?php foreach ($answers as $ans): ?>
                <div class="bg-surface p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="mb-3">
                        <span class="inline-block bg-indigo-50 text-primary text-[10px] font-bold px-2 py-1 rounded mb-2 uppercase tracking-wide">Part <?php echo $ans['part_type']; ?></span>
                        <div class="text-textMain font-medium text-sm">
                            <?php
                                $content = $ans['content'];
                                $decoded = json_decode($content, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                     if (isset($decoded['topic'])) echo "Topic: " . htmlspecialchars($decoded['topic']);
                                     elseif (is_array($decoded)) echo htmlspecialchars(implode(" ", $decoded));
                                } else {
                                    echo htmlspecialchars($content);
                                }
                            ?>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-2">
                        <audio controls class="w-full">
                            <?php
                                $ext = pathinfo($ans['audio_path'], PATHINFO_EXTENSION);
                                $mime = 'audio/webm';
                                if ($ext === 'mp4') $mime = 'audio/mp4';
                                elseif ($ext === 'ogg') $mime = 'audio/ogg';
                            ?>
                            <source src="../../<?php echo htmlspecialchars($ans['audio_path']); ?>" type="<?php echo $mime; ?>">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <!-- Bottom Nav -->
    <div class="fixed bottom-0 w-full bg-surface/90 backdrop-blur-md border-t border-gray-200 flex justify-around items-center px-2 py-2 pb-safe z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] flex-none">
        <a href="dashboard.php" class="flex flex-col items-center p-2 text-textMuted hover:text-accent w-16 transition">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-[10px] font-medium">Home</span>
        </a>
        <a href="history.php" class="flex flex-col items-center p-2 text-primary w-16">
             <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-[10px] font-bold">History</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center p-2 text-textMuted hover:text-accent w-16 transition">
             <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span class="text-[10px] font-medium">Profile</span>
        </a>
    </div>

</body>
</html>
