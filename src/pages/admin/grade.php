<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$submissionId = $_GET['submission_id'] ?? null;
$submission = null;
$answers = [];
$existingGrade = null;

if ($submissionId) {
    // Fetch Submission Details
    $stmt = $pdo->prepare("
        SELECT s.*, u.email, t.title
        FROM submissions s
        JOIN users u ON s.user_id = u.id
        JOIN tests t ON s.test_id = t.id
        WHERE s.id = ?
    ");
    $stmt->execute([$submissionId]);
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

        // Fetch Existing Grade
        $stmt = $pdo->prepare("SELECT * FROM grades WHERE submission_id = ?");
        $stmt->execute([$submissionId]);
        $existingGrade = $stmt->fetch();
    }
}

// Handle Grade Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $submissionId) {
    $range = $_POST['range'] ?? 0;
    $accuracy = $_POST['accuracy'] ?? 0;
    $fluency = $_POST['fluency'] ?? 0;
    $coherence = $_POST['coherence'] ?? 0;
    $comments = $_POST['comments'] ?? '';

    if ($existingGrade) {
        $stmt = $pdo->prepare("UPDATE grades SET range_score = ?, accuracy_score = ?, fluency_score = ?, coherence_score = ?, comments = ?, graded_at = CURRENT_TIMESTAMP WHERE submission_id = ?");
        $stmt->execute([$range, $accuracy, $fluency, $coherence, $comments, $submissionId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO grades (submission_id, range_score, accuracy_score, fluency_score, coherence_score, comments) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$submissionId, $range, $accuracy, $fluency, $coherence, $comments]);
    }

    // Update Submission Status
    $stmt = $pdo->prepare("UPDATE submissions SET status = 'graded' WHERE id = ?");
    $stmt->execute([$submissionId]);

    // Refresh to show updated data
    header("Location: grade.php?submission_id=$submissionId");
    exit;
}

if (!$submission) {
    die("Submission not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jules - Grade Submission</title>
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
<body class="bg-midnight text-white font-sans">

    <nav class="bg-gray-800 p-4 border-b border-gray-700">
        <div class="container mx-auto flex justify-between items-center">
             <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold text-neon">Jules</h1>
                <a href="dashboard.php" class="text-gray-400 hover:text-white">Dashboard</a>
                <span class="text-gray-500">/</span>
                <a href="submissions.php" class="text-gray-400 hover:text-white">Submissions</a>
                <span class="text-gray-500">/</span>
                <span class="text-white">Grade</span>
            </div>
            <div>
                 <span class="mr-4">Welcome, Admin</span>
                <a href="../../includes/logout_handler.php" class="text-gray-400 hover:text-white">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6 flex flex-col lg:flex-row gap-8">

        <!-- Left Column: Audio & Questions -->
        <div class="lg:w-2/3 space-y-6">
            <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($submission['title']); ?></h2>
                <p class="text-gray-400">Student: <?php echo htmlspecialchars($submission['email']); ?></p>
                <p class="text-gray-400">Date: <?php echo date('M j, Y H:i', strtotime($submission['started_at'])); ?></p>
            </div>

            <?php foreach ($answers as $ans): ?>
                <div class="bg-gray-800 p-6 rounded-lg shadow-lg border-l-4 border-indigo-500">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="inline-block bg-indigo-900 text-indigo-300 text-xs px-2 py-1 rounded mb-2">Part <?php echo $ans['part_type']; ?></span>
                            <h3 class="text-lg font-semibold">Question <?php echo $ans['sequence']; ?></h3>
                            <?php
                                // Attempt to parse content if it's JSON, otherwise display as is
                                $content = json_decode($ans['content'], true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($content)) {
                                     // Handle C1 or complex structure display roughly
                                     if (isset($content['topic'])) echo "<p class='text-gray-300 mt-1'>Topic: " . htmlspecialchars($content['topic']) . "</p>";
                                     elseif (is_array($content)) echo "<p class='text-gray-300 mt-1'>" . htmlspecialchars(implode(" ", $content)) . "</p>";
                                } else {
                                    echo "<p class='text-gray-300 mt-1'>" . htmlspecialchars($ans['content']) . "</p>";
                                }
                            ?>
                        </div>
                    </div>
                    <audio controls class="w-full mt-2">
                        <!-- Path adjustment: uploads are relative to root, we are in src/pages/admin -->
                        <source src="../../../<?php echo htmlspecialchars($ans['audio_path']); ?>" type="audio/webm">
                        Your browser does not support the audio element.
                    </audio>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Right Column: Grading Form -->
        <div class="lg:w-1/3">
            <div class="bg-gray-800 p-6 rounded-lg shadow-lg sticky top-6">
                <h3 class="text-xl font-bold mb-6 text-neon">Assessment</h3>
                <form method="POST">
                    <div class="space-y-4">
                        <?php
                            $criteria = ['Range', 'Accuracy', 'Fluency', 'Coherence'];
                            foreach ($criteria as $c):
                                $slug = strtolower($c);
                                $val = $existingGrade ? $existingGrade[$slug . '_score'] : 0;
                        ?>
                        <div>
                            <label class="block text-gray-300 mb-1 flex justify-between">
                                <span><?php echo $c; ?></span>
                                <span class="text-neon font-bold" id="val-<?php echo $slug; ?>"><?php echo $val; ?></span>
                            </label>
                            <input type="range" name="<?php echo $slug; ?>" min="0" max="6" value="<?php echo $val; ?>"
                                class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-neon"
                                oninput="document.getElementById('val-<?php echo $slug; ?>').innerText = this.value">
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <div>
                            <label class="block text-gray-300 mb-2">Comments</label>
                            <textarea name="comments" rows="4" class="w-full p-3 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-neon"><?php echo $existingGrade ? htmlspecialchars($existingGrade['comments']) : ''; ?></textarea>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full bg-neon hover:bg-neonHover text-white font-bold py-3 rounded transition duration-200">
                                <?php echo $existingGrade ? 'Update Grade' : 'Submit Grade'; ?>
                            </button>
                        </div>
                         <?php if ($existingGrade): ?>
                            <div class="text-center mt-2">
                                <span class="text-green-400 text-sm font-semibold">Test Graded</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

    </div>

</body>
</html>
