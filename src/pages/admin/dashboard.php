<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

// Fetch overall stats
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
$studentCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM submissions");
$submissionCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM submissions WHERE status = 'graded'");
$gradedCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM submissions WHERE status = 'in_progress' OR status = 'completed'");
$pendingCount = $submissionCount - $gradedCount;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford CEFR Speaking - Admin Dashboard</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-background text-textMain font-sans">

    <nav class="bg-white shadow-sm border-b border-gray-200 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold text-primary">Oxford CEFR Speaking <span class="text-sm text-textMuted font-normal">Command Center</span></h1>
            <div>
                <span class="mr-4 text-textMuted">Welcome, Admin</span>
                <a href="../../includes/logout_handler.php" class="text-textMuted hover:text-primary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <h2 class="text-3xl font-bold mb-8 text-textMain">Dashboard</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Stat Card -->
            <div class="bg-white p-6 rounded-lg shadow border-l-4 border-primary">
                <h3 class="text-textMuted text-sm font-uppercase tracking-wider">Total Students</h3>
                <p class="text-3xl font-bold mt-2 text-textMain"><?php echo $studentCount; ?></p>
            </div>
            <!-- Stat Card -->
            <div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-400">
                <h3 class="text-textMuted text-sm font-uppercase tracking-wider">Total Submissions</h3>
                <p class="text-3xl font-bold mt-2 text-textMain"><?php echo $submissionCount; ?></p>
            </div>
             <!-- Stat Card -->
             <div class="bg-white p-6 rounded-lg shadow border-l-4 border-yellow-500">
                <h3 class="text-textMuted text-sm font-uppercase tracking-wider">Pending Grading</h3>
                <p class="text-3xl font-bold mt-2 text-textMain"><?php echo $pendingCount; ?></p>
            </div>
             <!-- Stat Card -->
             <div class="bg-white p-6 rounded-lg shadow border-l-4 border-success">
                <h3 class="text-textMuted text-sm font-uppercase tracking-wider">Graded Tests</h3>
                <p class="text-3xl font-bold mt-2 text-textMain"><?php echo $gradedCount; ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-bold mb-4 text-primary">Quick Actions</h3>
                <div class="flex flex-col space-y-3">
                    <a href="submissions.php" class="block w-full py-3 px-4 bg-gray-50 hover:bg-gray-100 rounded text-center transition border border-gray-200 text-textMain font-medium">
                        View Submissions
                    </a>
                    <a href="manage_tests.php" class="block w-full py-3 px-4 bg-gray-50 hover:bg-gray-100 rounded text-center transition border border-gray-200 text-textMain font-medium">
                        Manage Tests
                    </a>
                     <a href="#" class="block w-full py-3 px-4 bg-gray-50 rounded text-center transition border border-gray-200 text-textMuted cursor-not-allowed">
                        Manage Users (Coming Soon)
                    </a>
                </div>
            </div>

             <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-bold mb-4 text-primary">System Status</h3>
                <p class="text-textMuted">Database connection: <span class="text-success font-semibold">Active</span></p>
                <p class="text-textMuted">Storage: <span class="text-success font-semibold">Writable</span></p>
                <p class="text-textMuted">PHP Version: <?php echo phpversion(); ?></p>
            </div>
        </div>

    </div>

</body>
</html>
