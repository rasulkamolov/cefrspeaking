<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

// Fetch submissions with user details
$stmt = $pdo->prepare("
    SELECT s.id, u.email, t.title, s.status, s.started_at, s.completed_at
    FROM submissions s
    JOIN users u ON s.user_id = u.id
    JOIN tests t ON s.test_id = t.id
    ORDER BY s.started_at DESC
");
$stmt->execute();
$submissions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jules - Submissions</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-background text-textMain font-sans">

    <nav class="bg-white shadow-sm border-b border-gray-200 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold text-primary">Jules</h1>
                <a href="dashboard.php" class="text-textMuted hover:text-primary">Dashboard</a>
                <span class="text-gray-300">/</span>
                <span class="text-textMain font-medium">Submissions</span>
            </div>
            <div>
                 <span class="mr-4 text-textMuted">Welcome, Admin</span>
                <a href="../../includes/logout_handler.php" class="text-textMuted hover:text-primary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <h2 class="text-3xl font-bold mb-8 text-textMain">Student Submissions</h2>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-textMuted uppercase text-xs font-semibold tracking-wider">
                        <tr>
                            <th class="p-4 border-b">ID</th>
                            <th class="p-4 border-b">Student</th>
                            <th class="p-4 border-b">Test</th>
                            <th class="p-4 border-b">Date</th>
                            <th class="p-4 border-b">Status</th>
                            <th class="p-4 border-b">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (count($submissions) > 0): ?>
                            <?php foreach ($submissions as $sub): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 text-textMuted">#<?php echo $sub['id']; ?></td>
                                    <td class="p-4 font-medium text-textMain"><?php echo htmlspecialchars($sub['email']); ?></td>
                                    <td class="p-4 text-textMuted"><?php echo htmlspecialchars($sub['title']); ?></td>
                                    <td class="p-4 text-textMuted text-sm"><?php echo date('M j, Y H:i', strtotime($sub['started_at'])); ?></td>
                                    <td class="p-4">
                                        <?php if ($sub['status'] === 'graded'): ?>
                                            <span class="inline-block px-2 py-1 text-xs font-bold rounded bg-green-100 text-green-700">Graded</span>
                                        <?php elseif ($sub['status'] === 'completed'): ?>
                                            <span class="inline-block px-2 py-1 text-xs font-bold rounded bg-yellow-100 text-yellow-700">Needs Grading</span>
                                        <?php else: ?>
                                            <span class="inline-block px-2 py-1 text-xs font-bold rounded bg-blue-100 text-blue-700">In Progress</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4">
                                        <?php if ($sub['status'] === 'completed' || $sub['status'] === 'graded'): ?>
                                            <a href="grade.php?submission_id=<?php echo $sub['id']; ?>" class="text-primary hover:text-primaryHover font-semibold text-sm">
                                                <?php echo $sub['status'] === 'graded' ? 'View/Edit' : 'Grade Now'; ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-sm cursor-not-allowed">Waiting</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-textMuted">No submissions found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
