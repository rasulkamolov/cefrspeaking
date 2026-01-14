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
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-background text-textMain font-sans pb-20">

    <nav class="bg-white shadow-sm border-b border-gray-200 p-4 sticky top-0 z-20">
        <div class="container mx-auto flex justify-between items-center">
             <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold text-primary">Jules</h1>
                <a href="submissions.php" class="text-textMuted hover:text-primary">Submissions</a>
                <span class="text-gray-300">/</span>
                <span class="text-textMain font-medium">Grade #<?php echo $submissionId; ?></span>
            </div>
            <div>
                 <span class="mr-4 text-textMuted">Welcome, Admin</span>
                <a href="../../includes/logout_handler.php" class="text-textMuted hover:text-primary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6 flex flex-col lg:flex-row gap-8">

        <!-- Left Column: Audio & Questions -->
        <div class="lg:w-2/3 space-y-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-2xl font-bold mb-2 text-textMain"><?php echo htmlspecialchars($submission['title']); ?></h2>
                <div class="flex gap-4 text-sm text-textMuted">
                    <p>Student: <span class="text-textMain font-medium"><?php echo htmlspecialchars($submission['email']); ?></span></p>
                    <p>Date: <span class="text-textMain font-medium"><?php echo date('M j, Y H:i', strtotime($submission['started_at'])); ?></span></p>
                </div>
            </div>

            <?php foreach ($answers as $ans): ?>
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-primary">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="inline-block bg-blue-50 text-primary text-xs font-bold px-2 py-1 rounded mb-2">Part <?php echo $ans['part_type']; ?></span>
                            <h3 class="text-lg font-bold text-textMain">Question <?php echo $ans['sequence']; ?></h3>
                            <div class="text-textMuted mt-1 text-sm">
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
                    </div>
                    <div class="bg-gray-50 rounded p-2">
                        <audio controls class="w-full">
                            <source src="../../../<?php echo htmlspecialchars($ans['audio_path']); ?>" type="audio/webm">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Right Column: Grading Form -->
        <div class="lg:w-1/3">
            <div class="bg-white p-6 rounded-lg shadow sticky top-24 border border-gray-100">
                <h3 class="text-xl font-bold mb-6 text-primary border-b border-gray-100 pb-2">Assessment</h3>
                <form method="POST">
                    <div class="space-y-6">
                        <?php
                            $criteria = ['Range', 'Accuracy', 'Fluency', 'Coherence'];
                            foreach ($criteria as $c):
                                $slug = strtolower($c);
                                $val = $existingGrade ? $existingGrade[$slug . '_score'] : 0;
                        ?>
                        <div>
                            <label class="flex justify-between text-textMuted font-medium mb-2">
                                <span><?php echo $c; ?></span>
                                <span class="bg-blue-100 text-primary px-2 rounded text-sm font-bold" id="val-<?php echo $slug; ?>"><?php echo $val; ?></span>
                            </label>
                            <input type="range" name="<?php echo $slug; ?>" min="0" max="6" value="<?php echo $val; ?>"
                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-primary"
                                oninput="document.getElementById('val-<?php echo $slug; ?>').innerText = this.value">
                            <div class="flex justify-between text-xs text-gray-400 mt-1">
                                <span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <div>
                            <label class="block text-textMuted font-medium mb-2">Feedback Comments</label>
                            <textarea name="comments" rows="4" class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary text-textMain text-sm" placeholder="Provide constructive feedback..."><?php echo $existingGrade ? htmlspecialchars($existingGrade['comments']) : ''; ?></textarea>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full bg-primary hover:bg-primaryHover text-white font-bold py-3 rounded shadow transition duration-200">
                                <?php echo $existingGrade ? 'Update Grade' : 'Submit Grade'; ?>
                            </button>
                        </div>
                         <?php if ($existingGrade): ?>
                            <div class="text-center mt-2">
                                <span class="text-success text-sm font-bold flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Test Graded
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

    </div>

</body>
</html>
