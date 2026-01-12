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
                <span class="text-white">Submissions</span>
            </div>
            <div>
                 <span class="mr-4">Welcome, Admin</span>
                <a href="../../includes/logout_handler.php" class="text-gray-400 hover:text-white">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <h2 class="text-3xl font-bold mb-8">Submissions</h2>

        <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-700 text-gray-300 uppercase text-sm font-semibold">
                        <tr>
                            <th class="p-4">ID</th>
                            <th class="p-4">Student</th>
                            <th class="p-4">Test</th>
                            <th class="p-4">Date</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php if (count($submissions) > 0): ?>
                            <?php foreach ($submissions as $sub): ?>
                                <tr class="hover:bg-gray-750 transition">
                                    <td class="p-4 text-gray-400">#<?php echo $sub['id']; ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars($sub['email']); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars($sub['title']); ?></td>
                                    <td class="p-4"><?php echo date('M j, Y H:i', strtotime($sub['started_at'])); ?></td>
                                    <td class="p-4">
                                        <?php if ($sub['status'] === 'graded'): ?>
                                            <span class="inline-block px-2 py-1 text-xs font-bold rounded bg-green-900 text-green-300">Graded</span>
                                        <?php elseif ($sub['status'] === 'completed'): ?>
                                            <span class="inline-block px-2 py-1 text-xs font-bold rounded bg-yellow-900 text-yellow-300">Needs Grading</span>
                                        <?php else: ?>
                                            <span class="inline-block px-2 py-1 text-xs font-bold rounded bg-blue-900 text-blue-300">In Progress</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4">
                                        <?php if ($sub['status'] === 'completed' || $sub['status'] === 'graded'): ?>
                                            <a href="grade.php?submission_id=<?php echo $sub['id']; ?>" class="text-neon hover:text-neonHover font-semibold">
                                                <?php echo $sub['status'] === 'graded' ? 'View/Edit' : 'Grade'; ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-500 cursor-not-allowed">Waiting</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-400">No submissions found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
