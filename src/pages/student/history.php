<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

// Fetch student's past submissions
$stmt = $pdo->prepare("
    SELECT s.*, t.title, g.range_score, g.accuracy_score, g.fluency_score, g.coherence_score, g.comments
    FROM submissions s
    JOIN tests t ON s.test_id = t.id
    LEFT JOIN grades g ON s.id = g.submission_id
    WHERE s.user_id = ?
    ORDER BY s.started_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$submissions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jules - History</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-background text-textMain font-sans pb-20">

    <nav class="bg-white shadow-sm p-4 sticky top-0 z-10">
        <h1 class="text-xl font-bold text-primary text-center">Exam History</h1>
    </nav>

    <div class="container mx-auto p-4 max-w-lg">
        <?php if (count($submissions) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($submissions as $sub):
                    $overall = null;
                    if ($sub['status'] === 'graded') {
                        $overall = ($sub['range_score'] + $sub['accuracy_score'] + $sub['fluency_score'] + $sub['coherence_score']) / 4;
                    }
                ?>
                    <div class="bg-white p-4 rounded-lg shadow border-l-4 <?php echo $sub['status'] === 'graded' ? 'border-success' : 'border-primary'; ?>">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-bold text-textMain"><?php echo htmlspecialchars($sub['title']); ?></h3>
                                <span class="text-textMuted text-xs"><?php echo date('M j, Y H:i', strtotime($sub['started_at'])); ?></span>
                            </div>
                            <div>
                                    <?php if ($sub['status'] === 'graded'): ?>
                                    <span class="inline-block px-2 py-1 text-xs font-bold rounded bg-green-100 text-green-700">
                                        <?php echo number_format($overall, 1); ?>/6.0
                                    </span>
                                <?php elseif ($sub['status'] === 'completed'): ?>
                                    <span class="inline-block px-2 py-1 text-xs font-bold rounded bg-yellow-100 text-yellow-700">Pending</span>
                                <?php else: ?>
                                    <span class="inline-block px-2 py-1 text-xs font-bold rounded bg-blue-100 text-blue-700">In Progress</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($sub['status'] === 'graded' && $sub['comments']): ?>
                            <div class="mt-2 bg-gray-50 p-2 rounded text-sm text-textMuted border border-gray-100">
                                <p class="italic">"<?php echo htmlspecialchars($sub['comments']); ?>"</p>
                            </div>
                        <?php endif; ?>

                        <?php if ($sub['status'] === 'in_progress'): ?>
                             <a href="../exam/runner.php?submission_id=<?php echo $sub['id']; ?>&part=1.1" class="block mt-2 text-center text-sm bg-primary text-white py-1 rounded">Continue Exam</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-8 text-center text-textMuted">You haven't taken any exams yet.</div>
        <?php endif; ?>
    </div>

    <!-- Bottom Nav -->
    <div class="fixed bottom-0 w-full bg-white border-t border-gray-200 flex justify-around p-2 z-30 pb-safe">
        <a href="dashboard.php" class="flex flex-col items-center p-2 text-textMuted hover:text-primary transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-xs font-medium">Home</span>
        </a>
        <a href="history.php" class="flex flex-col items-center p-2 text-primary">
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-xs font-medium">History</span>
        </a>
        <a href="#" class="flex flex-col items-center p-2 text-textMuted hover:text-primary transition">
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span class="text-xs font-medium">Profile</span>
        </a>
    </div>

</body>
</html>
