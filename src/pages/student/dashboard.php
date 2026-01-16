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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford CEFR Speaking - Dashboard</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-background text-textMain font-sans pb-20">

    <nav class="bg-white shadow-sm p-4 flex justify-between items-center sticky top-0 z-10">
        <h1 class="text-xl font-bold text-primary">Exam Room</h1>
        <a href="../../includes/logout_handler.php" class="text-textMuted text-sm hover:text-primary">Logout</a>
    </nav>

    <div class="container mx-auto p-4 max-w-lg">

        <div class="bg-blue-600 rounded-xl p-6 mb-8 text-white shadow-lg bg-gradient-to-r from-blue-600 to-blue-500">
            <h2 class="text-2xl font-bold mb-2">Ready to practice?</h2>
            <p class="text-blue-100 mb-4">Select an exam below to start your speaking session.</p>
        </div>

        <h3 class="text-lg font-bold mb-4 text-textMain">Available Exams</h3>

        <div class="space-y-6">
            <?php foreach ($tests as $test): ?>
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 hover:shadow-lg transition">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xl font-bold text-textMain"><?php echo htmlspecialchars($test['title']); ?></h3>
                        <span class="bg-blue-50 text-primary text-xs font-bold px-2 py-1 rounded">CEFR</span>
                    </div>
                    <p class="text-textMuted text-sm mb-6 leading-relaxed"><?php echo htmlspecialchars($test['description']); ?></p>

                    <a href="../exam/intro.php?test_id=<?php echo $test['id']; ?>&mode=full" class="block w-full bg-primary hover:bg-primaryHover text-white text-center font-bold py-3 rounded-lg shadow-sm transition mb-3">
                        Start Full Exam
                    </a>

                    <div class="grid grid-cols-4 gap-2">
                        <a href="../exam/intro.php?test_id=<?php echo $test['id']; ?>&mode=part&part=1.1" class="bg-gray-50 hover:bg-gray-100 text-textMuted hover:text-primary border border-gray-200 text-center py-2 rounded text-xs font-medium transition">Part 1.1</a>
                        <a href="../exam/intro.php?test_id=<?php echo $test['id']; ?>&mode=part&part=1.2" class="bg-gray-50 hover:bg-gray-100 text-textMuted hover:text-primary border border-gray-200 text-center py-2 rounded text-xs font-medium transition">Part 1.2</a>
                        <a href="../exam/intro.php?test_id=<?php echo $test['id']; ?>&mode=part&part=2" class="bg-gray-50 hover:bg-gray-100 text-textMuted hover:text-primary border border-gray-200 text-center py-2 rounded text-xs font-medium transition">Part 2</a>
                        <a href="../exam/intro.php?test_id=<?php echo $test['id']; ?>&mode=part&part=3" class="bg-gray-50 hover:bg-gray-100 text-textMuted hover:text-primary border border-gray-200 text-center py-2 rounded text-xs font-medium transition">Part 3</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bottom Nav -->
    <div class="fixed bottom-0 w-full bg-white border-t border-gray-200 flex justify-around p-2 z-30 pb-safe">
        <a href="dashboard.php" class="flex flex-col items-center p-2 text-primary">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-xs font-medium">Home</span>
        </a>
        <a href="history.php" class="flex flex-col items-center p-2 text-textMuted hover:text-primary transition">
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-xs font-medium">History</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center p-2 text-textMuted hover:text-primary transition">
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span class="text-xs font-medium">Profile</span>
        </a>
    </div>

</body>
</html>
