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
    <title>Oxford CEFR - Exam Room</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
    <style>
        .progress-ring__circle {
            transition: stroke-dashoffset 0.35s;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        /* Pulse animation for recording state */
        .recording-pulse {
            animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 20px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
    </style>
</head>
<body class="bg-background text-textMain font-sans h-screen flex flex-col overflow-hidden">

    <!-- Top Bar / Wizard -->
    <header class="bg-primary text-white pt-safe shadow-lg z-20">
        <div class="px-4 py-3 flex justify-between items-center">
            <h1 class="text-lg font-bold tracking-tight text-white flex items-center">
                <span class="bg-white/20 px-2 py-0.5 rounded text-sm mr-2">Part <?php echo $currentPart; ?></span>
            </h1>

            <!-- Progress Steps (Simplified Visual) -->
            <div class="flex items-center space-x-1">
                <?php
                $partsOrder = ['1.1', '1.2', '2', '3'];
                $currentIndex = array_search($currentPart, $partsOrder);
                foreach ($partsOrder as $idx => $p) {
                    if ($idx < $currentIndex) {
                        // Completed
                        echo '<div class="w-2 h-2 rounded-full bg-success"></div>';
                    } elseif ($idx === $currentIndex) {
                        // Active
                        echo '<div class="w-6 h-2 rounded-full bg-accent"></div>';
                    } else {
                        // Pending
                        echo '<div class="w-2 h-2 rounded-full bg-white/20"></div>';
                    }
                }
                ?>
            </div>

            <div class="text-indigo-200 text-xs font-medium bg-primaryLight px-2 py-1 rounded-full">
                Q <span id="q-current">1</span>/<?php echo count($questions); ?>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <div class="flex-grow relative flex flex-col items-center justify-center p-4 overflow-y-auto w-full" id="exam-container">

        <!-- Loading / Transition Overlay -->
        <div id="overlay" class="absolute inset-0 bg-white/95 z-50 flex items-center justify-center hidden backdrop-blur-sm">
            <div class="text-center animate-fade-in-down">
                <h2 class="text-2xl font-bold mb-6 text-primary" id="overlay-text">Get Ready</h2>
                <div class="relative w-24 h-24 mx-auto flex items-center justify-center">
                    <div class="absolute inset-0 border-4 border-gray-200 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-accent border-t-transparent rounded-full animate-spin"></div>
                    <div class="text-5xl font-mono text-textMain font-bold relative z-10" id="countdown">3</div>
                </div>
            </div>
        </div>

        <!-- Question Container -->
        <div id="question-area" class="w-full max-w-2xl text-center space-y-4">
            <!-- Dynamic Content Injected Here -->
        </div>

        <!-- Part 3 Logic/Arguments Container (Hidden by default, used for C1) -->
        <div id="c1-container" class="hidden w-full max-w-4xl pb-4">
            <h2 id="c1-topic" class="text-xl md:text-2xl font-bold mb-4 text-center text-primary">Topic</h2>
            <div class="grid grid-cols-1 gap-4 overflow-y-auto max-h-[50vh] pr-1">
                <!-- FOR -->
                <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-success">
                    <h3 class="text-success font-bold text-sm uppercase tracking-wide mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Arguments FOR
                    </h3>
                    <ul class="space-y-2 text-sm" id="c1-for-list">
                        <!-- Items injected here -->
                    </ul>
                </div>
                <!-- AGAINST -->
                <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-danger">
                    <h3 class="text-danger font-bold text-sm uppercase tracking-wide mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Arguments AGAINST
                    </h3>
                    <ul class="space-y-2 text-sm" id="c1-against-list">
                        <!-- Items injected here -->
                    </ul>
                </div>
            </div>
        </div>

    </div>

    <!-- Bottom Control Bar -->
    <div class="bg-surface p-4 pb-safe pt-4 border-t border-gray-100 flex justify-between items-center z-30 shadow-[0_-8px_30px_rgba(0,0,0,0.04)] relative">

        <!-- Timer -->
        <div class="flex items-center justify-center w-20">
             <div class="relative w-14 h-14">
                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                    <circle class="text-gray-100 stroke-current" stroke-width="8" cx="50" cy="50" r="42" fill="transparent"></circle>
                    <circle id="timer-ring" class="text-accent progress-ring__circle stroke-current transition-all duration-1000 ease-linear" stroke-width="8" stroke-linecap="round" cx="50" cy="50" r="42" fill="transparent" stroke-dasharray="264" stroke-dashoffset="0"></circle>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center font-mono font-bold text-lg text-textMain" id="timer-text">30</div>
            </div>
        </div>

        <!-- Record Button (Centered & Prominent) -->
        <div class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2">
            <button id="action-btn" class="w-20 h-20 rounded-full bg-danger border-4 border-white shadow-xl flex items-center justify-center transform transition active:scale-95 focus:outline-none z-40">
                <!-- Inner Icon -->
                <div id="rec-icon" class="w-8 h-8 bg-white rounded shadow-inner transition-all duration-200"></div>
            </button>
        </div>

        <!-- Skip / Next -->
        <div class="w-20 text-right">
            <button id="next-btn" class="text-textMuted hover:text-primary text-xs font-bold uppercase tracking-wider hidden transition py-2 px-1">Next &rarr;</button>
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
