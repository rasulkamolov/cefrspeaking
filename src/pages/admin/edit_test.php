<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$testId = $_GET['test_id'] ?? null;
if (!$testId) {
    header("Location: manage_tests.php");
    exit;
}

// Update Test Details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_test'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $stmt = $pdo->prepare("UPDATE tests SET title = ?, description = ? WHERE id = ?");
    $stmt->execute([$title, $description, $testId]);
    $success = "Test updated successfully.";
}

// Fetch Test
$stmt = $pdo->prepare("SELECT * FROM tests WHERE id = ?");
$stmt->execute([$testId]);
$test = $stmt->fetch();

if (!$test) die("Test not found.");

// Fetch Questions
$stmt = $pdo->prepare("SELECT * FROM test_questions WHERE test_id = ? ORDER BY part_type, sequence");
$stmt->execute([$testId]);
$allQuestions = $stmt->fetchAll();

// Group Questions
$questionsByPart = ['1.1' => [], '1.2' => [], '2' => [], '3' => []];
foreach ($allQuestions as $q) {
    $questionsByPart[$q['part_type']][] = $q;
}

$partTitles = [
    '1.1' => 'Part 1.1: Personal Exchange (A1-A2)',
    '1.2' => 'Part 1.2: Justified Description (B1)',
    '2' => 'Part 2: Abstract Monologue (B2)',
    '3' => 'Part 3: Critical Analysis (C1)'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jules - Edit Test</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-background text-textMain font-sans pb-20">

    <nav class="bg-white shadow-sm border-b border-gray-200 p-4 sticky top-0 z-20">
        <div class="container mx-auto flex justify-between items-center">
             <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold text-primary">Jules</h1>
                <a href="manage_tests.php" class="text-textMuted hover:text-primary">All Tests</a>
                <span class="text-gray-300">/</span>
                <span class="text-textMain font-medium">Edit Test #<?php echo $testId; ?></span>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6 max-w-5xl">

        <?php if (isset($success)): ?>
            <div class="bg-green-100 border-l-4 border-success text-success p-4 mb-6">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Test Details Form -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <h2 class="text-xl font-bold mb-4 text-textMain">Test Details</h2>
            <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1">
                    <label class="block text-textMuted font-medium mb-2" for="title">Title</label>
                    <input class="w-full p-2 rounded border border-gray-300 focus:ring-2 focus:ring-primary" type="text" name="title" value="<?php echo htmlspecialchars($test['title']); ?>" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-textMuted font-medium mb-2" for="description">Description</label>
                    <div class="flex gap-2">
                        <input class="w-full p-2 rounded border border-gray-300 focus:ring-2 focus:ring-primary" type="text" name="description" value="<?php echo htmlspecialchars($test['description']); ?>">
                        <button type="submit" name="update_test" class="bg-secondary hover:bg-gray-700 text-white px-4 py-2 rounded transition">Save</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Questions by Part -->
        <div class="space-y-8">
            <?php foreach ($partTitles as $partType => $title): ?>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="bg-gray-50 p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-bold text-lg text-textMain"><?php echo $title; ?></h3>
                        <a href="edit_question.php?test_id=<?php echo $testId; ?>&part=<?php echo $partType; ?>" class="bg-white border border-primary text-primary hover:bg-blue-50 px-3 py-1 rounded text-sm font-semibold transition">
                            + Add Question
                        </a>
                    </div>

                    <div class="divide-y divide-gray-100">
                        <?php if (empty($questionsByPart[$partType])): ?>
                            <div class="p-6 text-center text-textMuted italic">No questions yet for this part.</div>
                        <?php else: ?>
                            <?php foreach ($questionsByPart[$partType] as $q): ?>
                                <div class="p-4 hover:bg-gray-50 transition flex justify-between items-center group">
                                    <div class="flex-grow">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="bg-gray-200 text-gray-600 text-xs px-2 py-0.5 rounded font-mono">Seq: <?php echo $q['sequence']; ?></span>
                                            <?php if ($q['media_url']): ?>
                                                <span class="bg-blue-100 text-blue-600 text-xs px-2 py-0.5 rounded flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                    Media
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-textMain font-medium truncate max-w-xl">
                                            <?php
                                                $content = $q['content'];
                                                $decoded = json_decode($content, true);
                                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                    echo htmlspecialchars($decoded['topic'] ?? json_encode($decoded));
                                                } else {
                                                    echo htmlspecialchars($content);
                                                }
                                            ?>
                                        </p>
                                    </div>
                                    <div class="flex space-x-2 opacity-100 md:opacity-0 group-hover:opacity-100 transition">
                                        <a href="edit_question.php?test_id=<?php echo $testId; ?>&question_id=<?php echo $q['id']; ?>" class="text-primary hover:text-primaryHover font-medium text-sm p-2">Edit</a>
                                        <a href="delete_question.php?test_id=<?php echo $testId; ?>&question_id=<?php echo $q['id']; ?>" class="text-danger hover:text-red-700 font-medium text-sm p-2" onclick="return confirm('Delete this question?')">Delete</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

</body>
</html>
