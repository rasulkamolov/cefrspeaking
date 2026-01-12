<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

// Fetch available tests
$stmt = $pdo->query("SELECT * FROM tests ORDER BY created_at DESC");
$tests = $stmt->fetchAll();

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
    <title>Jules - Student Dashboard</title>
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
<body class="bg-midnight text-white font-sans mb-16">

    <nav class="bg-gray-800 p-4 border-b border-gray-700">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold text-neon">Jules <span class="text-sm text-gray-400 font-normal">Exam Room</span></h1>
            <div>
                 <a href="../../includes/logout_handler.php" class="text-gray-400 hover:text-white">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-4 md:p-6">

        <!-- Available Tests -->
        <h2 class="text-2xl font-bold mb-4">Available Exams</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <?php foreach ($tests as $test): ?>
                <div class="bg-gray-800 p-6 rounded-lg shadow-lg border-t-4 border-neon hover:bg-gray-750 transition">
                    <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($test['title']); ?></h3>
                    <p class="text-gray-400 text-sm mb-4"><?php echo htmlspecialchars($test['description']); ?></p>

                    <div class="flex flex-col space-y-2">
                        <a href="../exam/intro.php?test_id=<?php echo $test['id']; ?>&mode=full" class="bg-neon hover:bg-neonHover text-white text-center font-bold py-2 rounded transition">
                            Start Full Exam
                        </a>
                         <div class="grid grid-cols-2 gap-2">
                            <a href="../exam/intro.php?test_id=<?php echo $test['id']; ?>&mode=part&part=1.1" class="bg-gray-700 hover:bg-gray-600 text-center py-1 rounded text-sm">Part 1.1</a>
                            <a href="../exam/intro.php?test_id=<?php echo $test['id']; ?>&mode=part&part=1.2" class="bg-gray-700 hover:bg-gray-600 text-center py-1 rounded text-sm">Part 1.2</a>
                            <a href="../exam/intro.php?test_id=<?php echo $test['id']; ?>&mode=part&part=2" class="bg-gray-700 hover:bg-gray-600 text-center py-1 rounded text-sm">Part 2</a>
                            <a href="../exam/intro.php?test_id=<?php echo $test['id']; ?>&mode=part&part=3" class="bg-gray-700 hover:bg-gray-600 text-center py-1 rounded text-sm">Part 3</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- History -->
        <h2 class="text-2xl font-bold mb-4">Your History</h2>
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
             <?php if (count($submissions) > 0): ?>
                <div class="divide-y divide-gray-700">
                    <?php foreach ($submissions as $sub):
                        $overall = null;
                        if ($sub['status'] === 'graded') {
                            $overall = ($sub['range_score'] + $sub['accuracy_score'] + $sub['fluency_score'] + $sub['coherence_score']) / 4;
                        }
                    ?>
                        <div class="p-4 md:p-6 hover:bg-gray-750 transition">
                            <div class="flex justify-between items-start md:items-center flex-col md:flex-row mb-2">
                                <div>
                                    <h3 class="font-bold text-lg"><?php echo htmlspecialchars($sub['title']); ?></h3>
                                    <span class="text-gray-400 text-sm"><?php echo date('M j, Y H:i', strtotime($sub['started_at'])); ?></span>
                                </div>
                                <div class="mt-2 md:mt-0">
                                     <?php if ($sub['status'] === 'graded'): ?>
                                        <span class="inline-block px-3 py-1 text-sm font-bold rounded bg-green-900 text-green-300">
                                            Score: <?php echo number_format($overall, 1); ?>/6.0
                                        </span>
                                    <?php elseif ($sub['status'] === 'completed'): ?>
                                        <span class="inline-block px-3 py-1 text-sm font-bold rounded bg-yellow-900 text-yellow-300">Pending Grade</span>
                                    <?php else: ?>
                                        <span class="inline-block px-3 py-1 text-sm font-bold rounded bg-blue-900 text-blue-300">In Progress</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($sub['status'] === 'graded' && $sub['comments']): ?>
                                <div class="mt-2 bg-gray-700 p-3 rounded text-sm text-gray-300">
                                    <p><strong>Feedback:</strong> <?php echo htmlspecialchars($sub['comments']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="p-8 text-center text-gray-400">You haven't taken any exams yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bottom Nav for Mobile -->
    <div class="fixed bottom-0 w-full bg-gray-900 border-t border-gray-700 p-3 flex justify-around md:hidden">
        <a href="#" class="text-neon flex flex-col items-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-xs">Home</span>
        </a>
        <a href="#" class="text-gray-500 flex flex-col items-center">
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span class="text-xs">Profile</span>
        </a>
    </div>

</body>
</html>
