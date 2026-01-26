<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$testId = $_GET['test_id'] ?? null;
$questionId = $_GET['question_id'] ?? null;
$part = $_GET['part'] ?? '1.1';

if (!$testId) {
    header("Location: manage_tests.php");
    exit;
}

$question = null;
if ($questionId) {
    $stmt = $pdo->prepare("SELECT * FROM test_questions WHERE id = ?");
    $stmt->execute([$questionId]);
    $question = $stmt->fetch();
    if ($question) {
        $part = $question['part_type'];
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sequence = $_POST['sequence'] ?? 1;
    $content = '';
    $mediaUrl = $question['media_url'] ?? null;
    $mediaUrl2 = $question['media_url_2'] ?? null;

    // Handle Content based on Part
    if ($part === '3') {
        $topic = $_POST['topic'] ?? '';
        $fors = array_filter(array_map('trim', explode("\n", $_POST['fors'] ?? '')));
        $againsts = array_filter(array_map('trim', explode("\n", $_POST['againsts'] ?? '')));
        $content = json_encode([
            'topic' => $topic,
            'for_prompts' => array_values($fors),
            'against_prompts' => array_values($againsts)
        ]);
    } else {
        $content = $_POST['content'] ?? '';
    }

    // Handle File Upload 1
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'img_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $targetDir = __DIR__ . '/../../../uploads/images/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $filename)) {
            $mediaUrl = 'uploads/images/' . $filename;
        }
    }

    // Handle File Upload 2 (Part 1.2)
    if ($part === '1.2') {
        if (isset($_FILES['image2']) && $_FILES['image2']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image2']['name'], PATHINFO_EXTENSION);
            $filename = 'img_' . time() . '_' . rand(1000, 9999) . '_2.' . $ext;
            $targetDir = __DIR__ . '/../../../uploads/images/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            if (move_uploaded_file($_FILES['image2']['tmp_name'], $targetDir . $filename)) {
                $mediaUrl2 = 'uploads/images/' . $filename;
            }
        }
    }

    if ($questionId) {
        // Update
        $stmt = $pdo->prepare("UPDATE test_questions SET content = ?, media_url = ?, media_url_2 = ?, sequence = ? WHERE id = ?");
        $stmt->execute([$content, $mediaUrl, $mediaUrl2, $sequence, $questionId]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, part_type, content, media_url, media_url_2, sequence) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$testId, $part, $content, $mediaUrl, $mediaUrl2, $sequence]);
    }

    header("Location: edit_test.php?test_id=" . $testId . "&msg=saved");
    exit;
}

// Pre-fill values
$contentVal = $question ? $question['content'] : '';
$seqVal = $question ? $question['sequence'] : 1;
$mediaVal = $question ? $question['media_url'] : '';
$mediaVal2 = $question ? $question['media_url_2'] : '';

// For Part 3 Decoding
$p3Topic = '';
$p3Fors = '';
$p3Againsts = '';
if ($part === '3' && $contentVal) {
    $decoded = json_decode($contentVal, true);
    if ($decoded) {
        $p3Topic = $decoded['topic'] ?? '';
        $p3Fors = implode("\n", $decoded['for_prompts'] ?? []);
        $p3Againsts = implode("\n", $decoded['against_prompts'] ?? []);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford CEFR Speaking - Edit Question</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-background text-textMain font-sans h-screen-dvh flex flex-col overflow-hidden">

    <!-- Fixed Header -->
    <nav class="bg-white shadow-sm border-b border-gray-200 p-4 flex-none z-20">
        <div class="container mx-auto flex justify-between items-center">
             <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold text-primary">Oxford CEFR Speaking</h1>
                <a href="edit_test.php?test_id=<?php echo $testId; ?>" class="text-textMuted hover:text-primary">Back to Test</a>
                <span class="text-gray-300">/</span>
                <span class="text-textMain font-medium"><?php echo $questionId ? 'Edit' : 'Add'; ?> Question (Part <?php echo $part; ?>)</span>
            </div>
        </div>
    </nav>

    <!-- Scrollable Content -->
    <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
        <div class="container mx-auto max-w-3xl">

            <form method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded-lg shadow space-y-6 border border-gray-200">

                <!-- Sequence -->
                <div>
                    <label class="block text-textMuted font-medium mb-2" for="sequence">Sequence Order</label>
                    <input class="w-24 p-2 rounded border border-gray-300 focus:ring-2 focus:ring-primary focus:outline-none" type="number" name="sequence" value="<?php echo $seqVal; ?>" required>
                    <p class="text-xs text-textMuted mt-1">Order in which questions appear in this part.</p>
                </div>

                <?php if ($part === '3'): ?>
                    <!-- Part 3 Fields -->
                    <div>
                        <label class="block text-textMuted font-medium mb-2">Debate Topic</label>
                        <input class="w-full p-2 rounded border border-gray-300 focus:ring-2 focus:ring-primary focus:outline-none" type="text" name="topic" value="<?php echo htmlspecialchars($p3Topic); ?>" placeholder="e.g., Gun Control" required>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-green-600 font-bold mb-2">FOR Points (One per line)</label>
                            <textarea class="w-full p-2 rounded border border-gray-300 focus:ring-2 focus:ring-primary h-40 focus:outline-none" name="fors" placeholder="Argument 1&#10;Argument 2"><?php echo htmlspecialchars($p3Fors); ?></textarea>
                        </div>
                        <div>
                            <label class="block text-red-600 font-bold mb-2">AGAINST Points (One per line)</label>
                            <textarea class="w-full p-2 rounded border border-gray-300 focus:ring-2 focus:ring-primary h-40 focus:outline-none" name="againsts" placeholder="Argument 1&#10;Argument 2"><?php echo htmlspecialchars($p3Againsts); ?></textarea>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Standard Fields -->
                    <div>
                        <label class="block text-textMuted font-medium mb-2">Question / Prompt Text</label>
                        <textarea class="w-full p-2 rounded border border-gray-300 focus:ring-2 focus:ring-primary focus:outline-none" name="content" rows="3" required><?php echo htmlspecialchars($contentVal); ?></textarea>
                    </div>
                <?php endif; ?>

                <!-- Media Upload (Part 1.2, 2, or generic) -->
                <?php if ($part === '1.2' || $part === '2'): ?>
                    <div class="border-t border-gray-100 pt-6">
                        <label class="block text-textMuted font-medium mb-2">Image 1 <?php echo ($part==='1.2') ? '(Left)' : ''; ?></label>
                        <?php if ($mediaVal): ?>
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 mb-1">Current Image:</p>
                                <img src="../../../<?php echo htmlspecialchars($mediaVal); ?>" class="h-40 rounded border border-gray-200 object-contain bg-gray-50">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" accept="image/*" class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-primary
                            hover:file:bg-blue-100
                        "/>
                    </div>
                <?php endif; ?>

                <!-- Media Upload 2 (Part 1.2 Only) -->
                <?php if ($part === '1.2'): ?>
                    <div class="border-t border-gray-100 pt-6">
                        <label class="block text-textMuted font-medium mb-2">Image 2 (Right)</label>
                        <?php if ($mediaVal2): ?>
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 mb-1">Current Image 2:</p>
                                <img src="../../../<?php echo htmlspecialchars($mediaVal2); ?>" class="h-40 rounded border border-gray-200 object-contain bg-gray-50">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image2" accept="image/*" class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-primary
                            hover:file:bg-blue-100
                        "/>
                    </div>
                <?php endif; ?>

                <div class="pt-6">
                    <button class="w-full bg-primary hover:bg-primaryHover text-white font-bold py-3 rounded transition duration-200 shadow-lg shadow-indigo-500/20" type="submit">
                        <?php echo $questionId ? 'Update Question' : 'Add Question'; ?>
                    </button>
                </div>

            </form>

            <div class="h-10"></div> <!-- Bottom Spacer -->
        </div>
    </main>

</body>
</html>
