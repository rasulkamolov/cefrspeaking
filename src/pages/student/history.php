<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

// Fetch submission history
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
    <title>Oxford CEFR - History</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-background text-textMain font-sans pb-24">

    <!-- Top Header -->
    <header class="bg-primary text-white pt-safe sticky top-0 z-20 shadow-md">
        <div class="px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold tracking-tight">History</h1>
             <div class="w-8"></div> <!-- Spacer for center alignment visual -->
        </div>
    </header>

    <main class="container mx-auto px-4 mt-6 max-w-lg">
        <?php if (count($submissions) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($submissions as $sub):
                    $overall = null;
                    if ($sub['status'] === 'graded') {
                        $overall = ($sub['range_score'] + $sub['accuracy_score'] + $sub['fluency_score'] + $sub['coherence_score']) / 4;
                    }
                ?>
                    <div class="bg-surface p-5 rounded-2xl shadow-sm border border-gray-100 <?php echo $sub['status'] === 'graded' ? 'border-l-4 border-l-success' : 'border-l-4 border-l-secondary'; ?>">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-bold text-textMain text-lg"><?php echo htmlspecialchars($sub['title']); ?></h3>
                                <div class="flex items-center text-textMuted text-xs mt-1">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <?php echo date('M j, Y • H:i', strtotime($sub['started_at'])); ?>
                                </div>
                            </div>
                            <div>
                                    <?php if ($sub['status'] === 'graded'): ?>
                                    <div class="flex flex-col items-end">
                                        <span class="text-2xl font-bold text-primary"><?php echo number_format($overall, 1); ?></span>
                                        <span class="text-[10px] text-textMuted uppercase font-bold tracking-wider">Score</span>
                                    </div>
                                <?php elseif ($sub['status'] === 'completed'): ?>
                                    <span class="inline-block px-2 py-1 text-xs font-bold rounded-lg bg-yellow-100 text-yellow-700">Pending</span>
                                <?php else: ?>
                                    <span class="inline-block px-2 py-1 text-xs font-bold rounded-lg bg-blue-100 text-blue-700">In Progress</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($sub['status'] === 'graded' && $sub['comments']): ?>
                            <div class="mt-3 bg-gray-50 p-3 rounded-xl text-sm text-textMuted border border-gray-100 relative">
                                <svg class="w-4 h-4 text-gray-300 absolute top-2 left-2" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21L14.017 18C14.017 16.8954 13.1216 16 12.017 16H9.01735C9.01735 15.0531 9.71585 14.3796 10.5695 13.9045L11.8395 13.1977C13.2514 12.4119 14.2882 11.0494 14.6184 9.46398C15.0118 7.57508 14.2255 5.62945 12.6366 4.47959C11.0477 3.32973 8.94191 3.17646 7.19942 4.08331L6.70295 4.34169C5.7275 4.84933 5.35328 6.04655 5.86092 7.022C6.36856 7.99745 7.56578 8.37167 8.54123 7.86403L9.0377 7.60565C9.69709 7.26245 10.4942 7.32049 11.0957 7.75586C11.6973 8.19124 11.995 8.92782 11.846 9.64309C11.721 10.2435 11.3284 10.7595 10.7938 11.0569L9.5238 11.7638C7.57688 12.8474 6.01735 14.7766 6.01735 17H6.01735V21H14.017Z" /></svg>
                                <p class="italic pl-6">"<?php echo htmlspecialchars($sub['comments']); ?>"</p>
                            </div>
                        <?php endif; ?>

                        <?php if ($sub['status'] === 'graded' || $sub['status'] === 'completed'): ?>
                             <a href="submission_details.php?submission_id=<?php echo $sub['id']; ?>" class="block mt-4 text-center text-sm font-bold bg-gray-100 hover:bg-gray-200 text-textMain py-3 rounded-xl transition">View Details</a>
                        <?php endif; ?>

                        <?php if ($sub['status'] === 'in_progress'): ?>
                             <a href="../exam/runner.php?submission_id=<?php echo $sub['id']; ?>&part=1.1" class="block mt-4 text-center text-sm font-bold bg-primary hover:bg-primaryLight text-white py-3 rounded-xl transition">Continue Exam</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="flex flex-col items-center justify-center p-10 text-center text-textMuted mt-10">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                     <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-textMain mb-1">No Exams Yet</h3>
                <p class="text-sm">Your exam history will appear here once you start practicing.</p>
                <a href="dashboard.php" class="mt-6 bg-primary text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20 text-sm">Go to Dashboard</a>
            </div>
        <?php endif; ?>
    </main>

    <!-- Bottom Nav -->
    <div class="fixed bottom-0 w-full bg-surface/90 backdrop-blur-md border-t border-gray-200 flex justify-around items-center px-2 py-2 pb-safe z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
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
