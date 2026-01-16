<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$submissionId = $_GET['submission_id'] ?? null;
$currentPart = $_GET['part'] ?? '1.1';
$mode = $_GET['mode'] ?? 'full';

if (!$submissionId) {
    header("Location: ../student/dashboard.php");
    exit;
}

// Fetch Submission
$stmt = $pdo->prepare("SELECT * FROM submissions WHERE id = ? AND user_id = ?");
$stmt->execute([$submissionId, $_SESSION['user_id']]);
$submission = $stmt->fetch();

if (!$submission) {
    die("Invalid submission.");
}

// Fetch Questions
$stmt = $pdo->prepare("SELECT * FROM test_questions WHERE test_id = ? AND part_type = ? ORDER BY sequence");
$stmt->execute([$submission['test_id'], $currentPart]);
$questions = $stmt->fetchAll();

if (count($questions) === 0) {
    header("Location: ../student/dashboard.php");
    exit;
}

// Calculate Next Part
$nextPart = null;
if ($mode === 'full') {
    if ($currentPart === '1.1') $nextPart = '1.2';
    elseif ($currentPart === '1.2') $nextPart = '2';
    elseif ($currentPart === '2') $nextPart = '3';
    else $nextPart = 'finish';
} else {
    $nextPart = 'finish';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford CEFR Speaking - Exam Room</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
    <style>
        .progress-ring__circle {
            transition: stroke-dashoffset 0.35s;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
    </style>
</head>
<body class="bg-background text-textMain font-sans h-screen flex flex-col overflow-hidden">

    <!-- Top Bar / Wizard -->
    <div class="bg-white p-4 shadow-sm z-10">
        <div class="container mx-auto max-w-4xl flex justify-between items-center">
            <h1 class="text-xl font-bold text-primary">Part <?php echo $currentPart; ?></h1>

            <!-- Progress Steps (Simplified Visual) -->
            <div class="flex items-center space-x-2">
                <?php
                $partsOrder = ['1.1', '1.2', '2', '3'];
                $currentIndex = array_search($currentPart, $partsOrder);
                foreach ($partsOrder as $idx => $p) {
                    if ($idx < $currentIndex) {
                        // Completed
                        echo '<div class="w-8 h-8 rounded-full bg-success text-white flex items-center justify-center font-bold text-xs"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>';
                        if ($idx < 3) echo '<div class="w-4 h-0.5 bg-success"></div>';
                    } elseif ($idx === $currentIndex) {
                        // Active
                        echo '<div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center font-bold text-sm">' . ($idx + 1) . '</div>';
                        if ($idx < 3) echo '<div class="w-4 h-0.5 bg-gray-200"></div>';
                    } else {
                        // Pending
                        echo '<div class="w-8 h-8 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center font-bold text-sm">' . ($idx + 1) . '</div>';
                        if ($idx < 3) echo '<div class="w-4 h-0.5 bg-gray-200"></div>';
                    }
                }
                ?>
            </div>

            <div class="text-textMuted text-sm font-medium">Q <span id="q-current">1</span>/<?php echo count($questions); ?></div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-grow relative flex flex-col items-center justify-center p-6 overflow-y-auto" id="exam-container">

        <!-- Loading / Transition Overlay -->
        <div id="overlay" class="absolute inset-0 bg-white/90 z-50 flex items-center justify-center hidden backdrop-blur-sm">
            <div class="text-center">
                <h2 class="text-3xl font-bold mb-4 text-textMain" id="overlay-text">Get Ready</h2>
                <div class="text-6xl font-mono text-primary font-bold" id="countdown">3</div>
            </div>
        </div>

        <!-- Question Container -->
        <div id="question-area" class="w-full max-w-5xl text-center space-y-6">
            <!-- Dynamic Content Injected Here -->
        </div>

        <!-- Part 3 Logic/Arguments Container (Hidden by default, used for C1) -->
        <div id="c1-container" class="hidden w-full max-w-5xl">
            <h2 id="c1-topic" class="text-3xl font-bold mb-8 text-center text-textMain">Topic</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- FOR -->
                <div class="bg-white p-6 rounded-xl shadow-md border-t-4 border-success">
                    <h3 class="text-success font-bold text-lg mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Arguments FOR
                    </h3>
                    <ul class="space-y-3" id="c1-for-list">
                        <!-- Items injected here -->
                    </ul>
                </div>
                <!-- AGAINST -->
                <div class="bg-white p-6 rounded-xl shadow-md border-t-4 border-danger">
                    <h3 class="text-danger font-bold text-lg mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Arguments AGAINST
                    </h3>
                    <ul class="space-y-3" id="c1-against-list">
                        <!-- Items injected here -->
                    </ul>
                </div>
            </div>
            <div class="mt-8 text-center text-textMuted bg-blue-50 p-4 rounded-lg border border-blue-100">
                <p class="font-medium text-primary">Task: Choose two items from each list and debate the topic.</p>
            </div>
        </div>

    </div>

    <!-- Bottom Control Bar -->
    <div class="bg-white p-4 border-t border-gray-200 flex justify-between items-center h-24 z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">

        <!-- Timer -->
        <div class="flex items-center space-x-3 pl-4">
             <div class="relative w-16 h-16">
                <svg class="w-full h-full" viewBox="0 0 100 100">
                    <circle class="text-gray-100 stroke-current" stroke-width="8" cx="50" cy="50" r="40" fill="transparent"></circle>
                    <circle id="timer-ring" class="text-primary progress-ring__circle stroke-current" stroke-width="8" stroke-linecap="round" cx="50" cy="50" r="40" fill="transparent" stroke-dasharray="251.2" stroke-dashoffset="0"></circle>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center font-mono font-bold text-lg text-textMain" id="timer-text">30</div>
            </div>
            <div id="timer-label" class="text-xs text-textMuted hidden md:block font-medium">Time<br>Remaining</div>
        </div>

        <!-- Record Button -->
        <div class="flex-grow flex justify-center">
            <button id="action-btn" class="w-20 h-20 rounded-full bg-red-500 border-4 border-gray-100 shadow-xl flex items-center justify-center transform transition hover:scale-105 active:scale-95 ring-4 ring-transparent hover:ring-red-100">
                <div id="rec-icon" class="w-8 h-8 bg-white rounded-sm shadow-sm"></div>
            </button>
        </div>

        <!-- Skip / Next -->
        <div class="w-24 text-right pr-4">
            <button id="next-btn" class="text-textMuted hover:text-primary text-sm font-bold tracking-wider hidden transition">NEXT &rarr;</button>
        </div>
    </div>

    <script>
        const questions = <?php echo json_encode($questions); ?>;
        const submissionId = <?php echo $submissionId; ?>;
        const nextPartUrl = "<?php echo ($nextPart === 'finish') ? 'finish.php?submission_id='.$submissionId : 'runner.php?submission_id='.$submissionId.'&mode='.$mode.'&part='.$nextPart; ?>";
        const currentPart = "<?php echo $currentPart; ?>";
    </script>
    <script src="exam_logic.js"></script>
</body>
</html>
