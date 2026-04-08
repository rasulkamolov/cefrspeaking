<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$studentId = $_GET['student_id'] ?? null;
if (!$studentId) {
    header("Location: manage_users.php");
    exit;
}

// Fetch Student Info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

if (!$student) die("Student not found.");

// Fetch Submissions with Grades
$stmt = $pdo->prepare("
    SELECT s.*, t.title,
           g.range_score, g.accuracy_score, g.fluency_score, g.coherence_score,
           (IFNULL(g.range_score,0) + IFNULL(g.accuracy_score,0) + IFNULL(g.fluency_score,0) + IFNULL(g.coherence_score,0)) / 4.0 as overall_score
    FROM submissions s
    JOIN tests t ON s.test_id = t.id
    LEFT JOIN grades g ON s.id = g.submission_id
    WHERE s.user_id = ?
    ORDER BY s.started_at ASC
");
$stmt->execute([$studentId]);
$submissions = $stmt->fetchAll();

// Prepare Data for Chart
$dates = [];
$scores = [];
$totalScore = 0;
$gradedCount = 0;

foreach ($submissions as $sub) {
    if ($sub['status'] === 'graded') {
        $dates[] = date('M j', strtotime($sub['started_at']));
        $scores[] = number_format($sub['overall_score'], 1);
        $totalScore += $sub['overall_score'];
        $gradedCount++;
    }
}

$averageScore = $gradedCount > 0 ? $totalScore / $gradedCount : 0;

// Determine CEFR Level
function getCefrLevel($score) {
    if ($score < 1) return 'Pre-A1';
    if ($score < 2) return 'A1';
    if ($score < 3) return 'A2';
    if ($score < 4) return 'B1';
    if ($score < 5) return 'B2';
    if ($score < 6) return 'C1';
    return 'C2';
}

$currentLevel = getCefrLevel($averageScore);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford CEFR Speaking - Student Progress</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-background text-textMain font-sans h-screen-dvh flex flex-col">

    <nav class="bg-white shadow-sm border-b border-gray-200 p-4 flex-none z-20">
        <div class="container mx-auto flex justify-between items-center">
             <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold text-primary">Oxford CEFR Speaking</h1>
                <a href="manage_users.php" class="text-textMuted hover:text-primary">Students</a>
                <span class="text-gray-300">/</span>
                <span class="text-textMain font-medium"><?php echo htmlspecialchars($student['email']); ?></span>
            </div>
            <div>
                 <span class="mr-4 text-textMuted">Welcome, Admin</span>
                <a href="../../includes/logout_handler.php" class="text-textMuted hover:text-primary">Logout</a>
            </div>
        </div>
    </nav>

    <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
        <div class="container mx-auto max-w-6xl">

            <!-- Top Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Profile Card -->
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-primary flex items-center">
                    <div class="w-16 h-16 rounded-full bg-indigo-50 text-primary flex items-center justify-center font-bold text-2xl mr-4">
                        <?php echo strtoupper(substr($student['email'], 0, 1)); ?>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-textMain"><?php echo htmlspecialchars($student['email']); ?></h2>
                        <p class="text-textMuted text-sm">Joined <?php echo date('M Y', strtotime($student['created_at'])); ?></p>
                    </div>
                </div>

                <!-- Current Level -->
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-accent">
                    <h3 class="text-textMuted text-sm font-uppercase tracking-wider">Estimated Level</h3>
                    <div class="flex items-baseline mt-1">
                        <span class="text-4xl font-bold text-primary mr-2"><?php echo $currentLevel; ?></span>
                        <span class="text-textMuted text-sm">(Avg <?php echo number_format($averageScore, 1); ?>/6.0)</span>
                    </div>
                </div>

                <!-- Activity -->
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-success">
                    <h3 class="text-textMuted text-sm font-uppercase tracking-wider">Total Exams</h3>
                    <p class="text-4xl font-bold mt-1 text-textMain"><?php echo count($submissions); ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

                <!-- Progress Chart -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-bold mb-4 text-textMain">Performance Trend</h3>
                    <div class="h-64">
                        <canvas id="progressChart"></canvas>
                    </div>
                </div>

                <!-- Recent Activity List -->
                <div class="bg-white p-6 rounded-lg shadow overflow-hidden">
                    <h3 class="text-xl font-bold mb-4 text-textMain">Exam History</h3>
                    <div class="overflow-y-auto max-h-64 pr-2">
                        <?php if (count($submissions) > 0): ?>
                            <div class="space-y-3">
                                <?php foreach (array_reverse($submissions) as $sub): ?>
                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded hover:bg-gray-100 transition border border-gray-100">
                                        <div>
                                            <p class="font-medium text-textMain"><?php echo htmlspecialchars($sub['title']); ?></p>
                                            <p class="text-xs text-textMuted"><?php echo date('M j, Y H:i', strtotime($sub['started_at'])); ?></p>
                                        </div>
                                        <div class="text-right">
                                            <?php if ($sub['status'] === 'graded'): ?>
                                                <div class="font-bold text-primary"><?php echo number_format($sub['overall_score'], 1); ?></div>
                                                <a href="grade.php?submission_id=<?php echo $sub['id']; ?>" class="text-xs text-blue-500 hover:underline">View</a>
                                            <?php else: ?>
                                                <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded">Pending</span>
                                                <a href="grade.php?submission_id=<?php echo $sub['id']; ?>" class="block text-xs text-blue-500 hover:underline mt-1">Grade</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-textMuted italic">No exams taken yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
        const ctx = document.getElementById('progressChart').getContext('2d');
        const progressChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Overall Score (0-6)',
                    data: <?php echo json_encode($scores); ?>,
                    borderColor: '#8b5cf6', // Neon Violet
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#1e1b4b',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: 0,
                        max: 6,
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>

</body>
</html>
