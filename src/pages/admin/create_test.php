<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';

    if ($title) {
        $stmt = $pdo->prepare("INSERT INTO tests (title, description) VALUES (?, ?)");
        if ($stmt->execute([$title, $description])) {
            $newId = $pdo->lastInsertId();
            header("Location: edit_test.php?test_id=" . $newId);
            exit;
        } else {
            $error = "Failed to create test.";
        }
    } else {
        $error = "Title is required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford CEFR Speaking - Create Test</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-background text-textMain font-sans">

    <nav class="bg-white shadow-sm border-b border-gray-200 p-4">
        <div class="container mx-auto flex justify-between items-center">
             <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold text-primary">Oxford CEFR Speaking</h1>
                <a href="dashboard.php" class="text-textMuted hover:text-primary">Dashboard</a>
                <span class="text-gray-300">/</span>
                <span class="text-textMain font-medium">Create Test</span>
            </div>
            <div>
                 <span class="mr-4 text-textMuted">Welcome, Admin</span>
                <a href="../../includes/logout_handler.php" class="text-textMuted hover:text-primary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6 max-w-2xl">
        <h2 class="text-3xl font-bold mb-8 text-textMain">Create New Test</h2>

        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-danger text-danger p-4 mb-6" role="alert">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-white p-8 rounded-lg shadow">
            <div class="mb-6">
                <label class="block text-textMuted font-medium mb-2" for="title">Test Title</label>
                <input class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition" type="text" name="title" id="title" placeholder="e.g., CEFR Mock Exam 2" required>
            </div>

            <div class="mb-6">
                <label class="block text-textMuted font-medium mb-2" for="description">Description</label>
                <textarea class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition" name="description" id="description" rows="3" placeholder="Optional description..."></textarea>
            </div>

            <button class="w-full bg-primary hover:bg-primaryHover text-white font-bold py-3 rounded transition duration-200" type="submit">
                Create & Start Adding Questions
            </button>
        </form>
    </div>

</body>
</html>
