<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

// Fetch available tests
$stmt = $pdo->query("SELECT * FROM tests ORDER BY created_at DESC");
$tests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Oxford CEFR - Dashboard</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body>

    <!-- Fixed Header -->
    <header class="bg-primary text-white pt-safe sticky top-0 z-50 shadow-md w-full flex-none">
        <div class="px-6 py-4 flex justify-between items-center h-16">
            <div>
                <h1 class="text-xl font-bold tracking-tight">Oxford CEFR</h1>
                <p class="text-indigo-200 text-xs">Speaking Exam</p>
            </div>
            <a href="../../includes/logout_handler.php" class="bg-primaryLight hover:bg-indigo-800 p-2 rounded-full transition">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </a>
        </div>
    </header>

    <!-- Scrollable Main Content -->
    <main class="app-content bg-gray-50 pb-24">
        <div class="container mx-auto px-4 mt-6 max-w-lg">

            <!-- Hero -->
            <div class="bg-gradient-to-r from-primary to-accent rounded-3xl p-6 mb-8 text-white shadow-xl relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-2xl"></div>
                <div class="relative z-10">
                    <h2 class="text-2xl font-bold mb-2">Ready to practice?</h2>
                    <p class="text-indigo-100 text-sm mb-4">Select an exam below to start your speaking session.</p>
                    <div class="inline-flex items-center bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-medium">
                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                        Microphone Ready
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between mb-4 px-1">
                <h3 class="text-lg font-bold text-textMain">Available Exams</h3>
                <span class="text-xs text-textMuted bg-gray-200 px-2 py-1 rounded-full"><?php echo count($tests); ?> Tests</span>
            </div>

            <div class="space-y-5">
                <?php foreach ($tests as $test): ?>
                    <div class="bg-surface p-5 rounded-2xl shadow-sm border border-gray-100 active-scale transition duration-200">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-bold text-textMain line-clamp-1"><?php echo htmlspecialchars($test['title']); ?></h3>
                            <span class="bg-indigo-50 text-primary text-xs font-bold px-2 py-1 rounded border border-indigo-100">B2-C1</span>
                        </div>
                        <p class="text-textMuted text-sm mb-5 leading-relaxed line-clamp-2"><?php echo htmlspecialchars($test['description']); ?></p>

                        <a href="../exam/intro.php?test_id=<?php echo $test['id']; ?>&mode=full" class="block w-full bg-primary hover:bg-primaryLight text-white text-center font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-500/20 transition mb-4 flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Start Full Exam
                        </a>

                        <div class="grid grid-cols-4 gap-2">
                            <?php
                            $parts = [
                                '1.1' => 'Part 1.1',
                                '1.2' => 'Part 1.2',
                                '2' => 'Part 2',
                                '3' => 'Part 3'
                            ];
                            foreach($parts as $key => $label): ?>
                            <a href="../exam/intro.php?test_id=<?php echo $test['id']; ?>&mode=part&part=<?php echo $key; ?>" class="bg-gray-50 hover:bg-gray-100 text-textMuted hover:text-accent border border-gray-200 hover:border-accent text-center py-2 rounded-lg text-[10px] font-bold uppercase tracking-wide transition flex flex-col items-center justify-center h-10">
                                <?php echo $label; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Bottom Spacer for Tab Bar -->
            <div class="h-8"></div>
        </div>
    </main>

    <!-- Fixed Bottom Nav -->
    <nav class="fixed bottom-0 w-full bg-surface/95 backdrop-blur-md border-t border-gray-200 flex justify-around items-center px-2 py-2 pb-safe z-50 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] flex-none">
        <a href="dashboard.php" class="flex flex-col items-center p-2 text-primary w-16">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-[10px] font-bold">Home</span>
        </a>
        <a href="history.php" class="flex flex-col items-center p-2 text-textMuted hover:text-accent transition w-16">
             <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-[10px] font-medium">History</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center p-2 text-textMuted hover:text-accent transition w-16">
             <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span class="text-[10px] font-medium">Profile</span>
        </a>
    </nav>

</body>
</html>
