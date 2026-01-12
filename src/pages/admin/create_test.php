<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // This is a simplified creation logic. In a real app, you'd likely have a dynamic JS form builder.
    // Here we will just create a new test entry and let the admin know it was created.
    // Adding questions would require a much more complex UI which might be out of scope for this step's simplicity
    // but I will add the Test record creation.

    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';

    if ($title) {
        $stmt = $pdo->prepare("INSERT INTO tests (title, description) VALUES (?, ?)");
        if ($stmt->execute([$title, $description])) {
            $success = "Test '$title' created successfully. (Question management would go here in a full version)";
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
    <title>Jules - Create Test</title>
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
                <span class="text-white">Create Test</span>
            </div>
            <div>
                 <span class="mr-4">Welcome, Admin</span>
                <a href="../../includes/logout_handler.php" class="text-gray-400 hover:text-white">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6 max-w-2xl">
        <h2 class="text-3xl font-bold mb-8">Create New Test</h2>

        <?php if ($success): ?>
            <div class="bg-green-500 text-white p-4 rounded mb-6">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-500 text-white p-4 rounded mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-gray-800 p-8 rounded-lg shadow-lg">
            <div class="mb-6">
                <label class="block text-gray-300 mb-2" for="title">Test Title</label>
                <input class="w-full p-3 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-neon" type="text" name="title" id="title" placeholder="e.g., CEFR Mock Exam 2" required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-300 mb-2" for="description">Description</label>
                <textarea class="w-full p-3 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-neon" name="description" id="description" rows="3" placeholder="Optional description..."></textarea>
            </div>

            <div class="bg-gray-700 p-4 rounded mb-6 text-sm text-gray-400">
                <p class="mb-2"><strong>Note:</strong> In this version, questions must be added directly to the database or via a future advanced editor.</p>
                <p>Use the provided SQLite seeding script or tools to populate questions for this test ID.</p>
            </div>

            <button class="w-full bg-neon hover:bg-neonHover text-white font-bold py-3 rounded transition duration-200" type="submit">
                Create Test Shell
            </button>
        </form>
    </div>

</body>
</html>
