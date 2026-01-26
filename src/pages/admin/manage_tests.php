<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$stmt = $pdo->query("SELECT * FROM tests ORDER BY created_at DESC");
$tests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford CEFR Speaking - Manage Tests</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-background text-textMain font-sans h-screen-dvh flex flex-col overflow-hidden">

    <!-- Fixed Header -->
    <nav class="bg-white shadow-sm border-b border-gray-200 p-4 flex-none z-20">
        <div class="container mx-auto flex justify-between items-center">
             <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold text-primary">Oxford CEFR Speaking</h1>
                <a href="dashboard.php" class="text-textMuted hover:text-primary">Dashboard</a>
                <span class="text-gray-300">/</span>
                <span class="text-textMain font-medium">Manage Tests</span>
            </div>
            <div>
                 <span class="mr-4 text-textMuted">Welcome, Admin</span>
                <a href="../../includes/logout_handler.php" class="text-textMuted hover:text-primary">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Scrollable Content -->
    <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
        <div class="container mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-textMain">All Tests</h2>
                <a href="create_test.php" class="bg-primary hover:bg-primaryHover text-white font-bold py-2 px-6 rounded shadow transition">
                    + Create New Test
                </a>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-textMuted uppercase text-xs font-semibold tracking-wider">
                            <tr>
                                <th class="p-4 border-b">ID</th>
                                <th class="p-4 border-b">Title</th>
                                <th class="p-4 border-b">Description</th>
                                <th class="p-4 border-b">Created</th>
                                <th class="p-4 border-b text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (count($tests) > 0): ?>
                                <?php foreach ($tests as $test): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="p-4 text-textMuted">#<?php echo $test['id']; ?></td>
                                        <td class="p-4 font-medium text-textMain"><?php echo htmlspecialchars($test['title']); ?></td>
                                        <td class="p-4 text-textMuted"><?php echo htmlspecialchars($test['description']); ?></td>
                                        <td class="p-4 text-textMuted text-sm"><?php echo date('M j, Y', strtotime($test['created_at'])); ?></td>
                                        <td class="p-4 text-right space-x-2 whitespace-nowrap">
                                            <a href="edit_test.php?test_id=<?php echo $test['id']; ?>" class="text-primary hover:text-primaryHover font-semibold text-sm border border-transparent hover:border-primary px-2 py-1 rounded transition">Edit</a>
                                            <a href="delete_test.php?test_id=<?php echo $test['id']; ?>" class="text-danger hover:text-red-700 font-semibold text-sm border border-transparent hover:border-danger px-2 py-1 rounded transition" onclick="return confirm('Are you sure? This will delete all associated questions and submissions.')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-textMuted">No tests found. Create one to get started.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
