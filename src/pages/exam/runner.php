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

// Fetch Submission to get Test ID
$stmt = $pdo->prepare("SELECT * FROM submissions WHERE id = ? AND user_id = ?");
$stmt->execute([$submissionId, $_SESSION['user_id']]);
$submission = $stmt->fetch();

if (!$submission) {
    die("Invalid submission.");
}

// Fetch Questions for this part
$stmt = $pdo->prepare("SELECT * FROM test_questions WHERE test_id = ? AND part_type = ? ORDER BY sequence");
$stmt->execute([$submission['test_id'], $currentPart]);
$questions = $stmt->fetchAll();

if (count($questions) === 0) {
    // End of exam or invalid part
    // If full mode, transition logic handles this, but if we run out of questions:
    header("Location: ../student/dashboard.php");
    exit;
}

// Calculate Next Part for Full Exam Flow
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
    <title>Jules - Exam Room</title>
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
    <style>
        .progress-ring__circle {
            transition: stroke-dashoffset 0.35s;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
    </style>
</head>
<body class="bg-midnight text-white font-sans h-screen flex flex-col overflow-hidden">

    <!-- Top Bar -->
    <div class="bg-gray-800 p-4 flex justify-between items-center shadow-lg z-10">
        <h1 class="text-xl font-bold text-neon">Part <?php echo $currentPart; ?></h1>
        <div class="text-gray-400 text-sm">Question <span id="q-current">1</span>/<?php echo count($questions); ?></div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-grow relative flex flex-col items-center justify-center p-6" id="exam-container">

        <!-- Loading / Transition Overlay -->
        <div id="overlay" class="absolute inset-0 bg-midnight z-50 flex items-center justify-center hidden">
            <div class="text-center">
                <h2 class="text-3xl font-bold mb-4" id="overlay-text">Get Ready</h2>
                <div class="text-6xl font-mono text-neon" id="countdown">3</div>
            </div>
        </div>

        <!-- Question Container -->
        <div id="question-area" class="w-full max-w-4xl text-center space-y-6">
            <!-- Dynamic Content Injected Here by JS -->
        </div>

        <!-- Part 2 Note Pad (Hidden by default) -->
        <div id="notepad-area" class="hidden w-full max-w-2xl bg-gray-800 p-4 rounded-lg absolute top-4 bottom-32 left-1/2 transform -translate-x-1/2">
            <h3 class="text-gray-400 mb-2">Preparation Notes (Will be discarded)</h3>
            <textarea class="w-full h-full bg-gray-700 text-white p-4 rounded resize-none focus:outline-none" placeholder="Type your notes here..."></textarea>
        </div>

        <!-- Part 3 Sidebar (Hidden by default) -->
        <div id="logic-sidebar" class="hidden absolute right-0 top-0 bottom-32 w-64 bg-gray-900 p-4 border-l border-gray-700 overflow-y-auto">
            <h3 class="text-neon font-bold mb-4">Logical Flow</h3>
            <div class="space-y-4">
                <div>
                    <h4 class="text-green-400 text-sm font-bold uppercase mb-2">For</h4>
                    <ul class="list-disc list-inside text-sm text-gray-300 space-y-2" id="for-points"></ul>
                </div>
                 <div>
                    <h4 class="text-red-400 text-sm font-bold uppercase mb-2">Against</h4>
                    <ul class="list-disc list-inside text-sm text-gray-300 space-y-2" id="against-points"></ul>
                </div>
            </div>
        </div>

    </div>

    <!-- Bottom Control Bar -->
    <div class="bg-gray-800 p-4 border-t border-gray-700 flex justify-between items-center h-24 z-20">

        <!-- Timer -->
        <div class="flex items-center space-x-3">
             <div class="relative w-16 h-16">
                <svg class="w-full h-full" viewBox="0 0 100 100">
                    <circle class="text-gray-700 stroke-current" stroke-width="8" cx="50" cy="50" r="40" fill="transparent"></circle>
                    <circle id="timer-ring" class="text-neon progress-ring__circle stroke-current" stroke-width="8" stroke-linecap="round" cx="50" cy="50" r="40" fill="transparent" stroke-dasharray="251.2" stroke-dashoffset="0"></circle>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center font-mono font-bold text-lg" id="timer-text">30</div>
            </div>
            <div class="text-xs text-gray-400 hidden md:block">Time Remaining</div>
        </div>

        <!-- Record Button / Status -->
        <div class="flex-grow flex justify-center">
            <button id="action-btn" class="w-20 h-20 rounded-full bg-red-600 border-4 border-gray-800 shadow-lg flex items-center justify-center transform transition hover:scale-105 active:scale-95">
                <div id="rec-icon" class="w-8 h-8 bg-white rounded-sm"></div> <!-- Square for Stop, Circle for Rec -->
            </button>
        </div>

        <!-- Skip / Next -->
        <div class="w-24 text-right">
            <button id="next-btn" class="text-gray-400 hover:text-white text-sm uppercase tracking-wider hidden">Next</button>
        </div>
    </div>

    <script>
        // Pass PHP data to JS
        const questions = <?php echo json_encode($questions); ?>;
        const submissionId = <?php echo $submissionId; ?>;
        const nextPartUrl = "<?php echo ($nextPart === 'finish') ? 'finish.php?submission_id='.$submissionId : 'runner.php?submission_id='.$submissionId.'&mode='.$mode.'&part='.$nextPart; ?>";
        const currentPart = "<?php echo $currentPart; ?>";
    </script>
    <script src="exam_logic.js"></script>
</body>
</html>
