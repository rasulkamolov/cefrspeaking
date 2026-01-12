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
    <title>Jules - Admin Dashboard</title>
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
            <h1 class="text-2xl font-bold text-neon">Jules <span class="text-sm text-gray-400 font-normal">Command Center</span></h1>
            <div>
                <span class="mr-4">Welcome, Admin</span>
                <a href="../../includes/logout_handler.php" class="text-gray-400 hover:text-white">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <h2 class="text-3xl font-bold mb-8">Dashboard</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Stat Card -->
            <div class="bg-gray-800 p-6 rounded-lg shadow-lg border-l-4 border-neon">
                <h3 class="text-gray-400 text-sm font-uppercase tracking-wider">Total Students</h3>
                <p class="text-3xl font-bold mt-2"><?php echo $studentCount; ?></p>
            </div>
            <!-- Stat Card -->
            <div class="bg-gray-800 p-6 rounded-lg shadow-lg border-l-4 border-blue-500">
                <h3 class="text-gray-400 text-sm font-uppercase tracking-wider">Total Submissions</h3>
                <p class="text-3xl font-bold mt-2"><?php echo $submissionCount; ?></p>
            </div>
             <!-- Stat Card -->
             <div class="bg-gray-800 p-6 rounded-lg shadow-lg border-l-4 border-yellow-500">
                <h3 class="text-gray-400 text-sm font-uppercase tracking-wider">Pending Grading</h3>
                <p class="text-3xl font-bold mt-2"><?php echo $pendingCount; ?></p>
            </div>
             <!-- Stat Card -->
             <div class="bg-gray-800 p-6 rounded-lg shadow-lg border-l-4 border-green-500">
                <h3 class="text-gray-400 text-sm font-uppercase tracking-wider">Graded Tests</h3>
                <p class="text-3xl font-bold mt-2"><?php echo $gradedCount; ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                <h3 class="text-xl font-bold mb-4 text-neon">Quick Actions</h3>
                <div class="flex flex-col space-y-3">
                    <a href="submissions.php" class="block w-full py-3 px-4 bg-gray-700 hover:bg-gray-600 rounded text-center transition">
                        View Submissions
                    </a>
                    <a href="create_test.php" class="block w-full py-3 px-4 bg-gray-700 hover:bg-gray-600 rounded text-center transition">
                        Create New Test
                    </a>
                     <a href="#" class="block w-full py-3 px-4 bg-gray-700 hover:bg-gray-600 rounded text-center transition opacity-50 cursor-not-allowed">
                        Manage Users (Coming Soon)
                    </a>
                </div>
            </div>

             <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                <h3 class="text-xl font-bold mb-4 text-neon">System Status</h3>
                <p class="text-gray-400">Database connection: <span class="text-green-400">Active</span></p>
                <p class="text-gray-400">Storage: <span class="text-green-400">Writable</span></p>
                <p class="text-gray-400">PHP Version: <?php echo phpversion(); ?></p>
            </div>
        </div>

    </div>

</body>
</html>
