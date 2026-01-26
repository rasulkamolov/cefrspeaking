<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

// Fetch Users with Stats
// We need: ID, Email, Created At, Count(Submissions), Avg(Score)
// Note: Scores are in 'grades' table linked to 'submissions'
$stmt = $pdo->prepare("
    SELECT
        u.id,
        u.email,
        u.created_at,
        COUNT(s.id) as submission_count,
        MAX(s.started_at) as last_active,
        AVG((g.range_score + g.accuracy_score + g.fluency_score + g.coherence_score) / 4.0) as avg_score
    FROM users u
    LEFT JOIN submissions s ON u.id = s.user_id
    LEFT JOIN grades g ON s.id = g.submission_id
    WHERE u.role = 'student'
    GROUP BY u.id
    ORDER BY last_active DESC
");
$stmt->execute();
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford CEFR Speaking - Manage Users</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-background text-textMain font-sans h-screen-dvh flex flex-col">

    <!-- Fixed Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 p-4 flex-none z-20">
        <div class="container mx-auto flex justify-between items-center">
             <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold text-primary">Oxford CEFR Speaking</h1>
                <a href="dashboard.php" class="text-textMuted hover:text-primary">Dashboard</a>
                <span class="text-gray-300">/</span>
                <span class="text-textMain font-medium">Manage Users</span>
            </div>
            <div>
                 <span class="mr-4 text-textMuted">Welcome, Admin</span>
                <a href="../../includes/logout_handler.php" class="text-textMuted hover:text-primary">Logout</a>
            </div>
        </div>
    </header>

    <!-- Scrollable Content -->
    <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
        <div class="container mx-auto max-w-6xl">
            <h2 class="text-3xl font-bold mb-8 text-textMain">Registered Students</h2>

            <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-textMuted uppercase text-xs font-semibold tracking-wider">
                            <tr>
                                <th class="p-4 border-b">Student</th>
                                <th class="p-4 border-b">Joined</th>
                                <th class="p-4 border-b">Tests Taken</th>
                                <th class="p-4 border-b">Last Active</th>
                                <th class="p-4 border-b">Avg. Score</th>
                                <th class="p-4 border-b text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user):
                                    $avg = $user['avg_score'] ? number_format($user['avg_score'], 1) : '-';
                                    $scoreColor = 'text-gray-500';
                                    if ($avg >= 5) $scoreColor = 'text-green-600';
                                    elseif ($avg >= 3) $scoreColor = 'text-blue-600';
                                    elseif ($avg > 0) $scoreColor = 'text-yellow-600';
                                ?>
                                    <tr class="hover:bg-gray-50 transition group">
                                        <td class="p-4">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-primary flex items-center justify-center font-bold text-xs mr-3">
                                                    <?php echo strtoupper(substr($user['email'], 0, 1)); ?>
                                                </div>
                                                <span class="font-medium text-textMain"><?php echo htmlspecialchars($user['email']); ?></span>
                                            </div>
                                        </td>
                                        <td class="p-4 text-textMuted text-sm"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td class="p-4">
                                            <span class="inline-block px-2 py-1 bg-gray-100 rounded text-xs font-bold text-gray-700">
                                                <?php echo $user['submission_count']; ?>
                                            </span>
                                        </td>
                                        <td class="p-4 text-textMuted text-sm">
                                            <?php echo $user['last_active'] ? date('M j, H:i', strtotime($user['last_active'])) : 'Never'; ?>
                                        </td>
                                        <td class="p-4 font-bold <?php echo $scoreColor; ?>">
                                            <?php echo $avg; ?>
                                        </td>
                                        <td class="p-4 text-right">
                                            <a href="student_details.php?student_id=<?php echo $user['id']; ?>" class="text-primary hover:text-primaryHover font-medium text-sm border border-primary px-3 py-1 rounded hover:bg-blue-50 transition">
                                                View Progress
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-textMuted">No students found.</td>
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
