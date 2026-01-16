<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../../includes/db.php';

// Fetch user details
$stmt = $pdo->prepare("SELECT email, role, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford CEFR Speaking - Profile</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-background text-textMain font-sans pb-20">

    <nav class="bg-white shadow-sm p-4 sticky top-0 z-10">
        <h1 class="text-xl font-bold text-primary text-center">My Profile</h1>
    </nav>

    <div class="container mx-auto p-4 max-w-lg">

        <!-- User Info Card -->
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 text-center mb-6">
            <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>

            <h2 class="text-2xl font-bold text-textMain mb-1"><?php echo htmlspecialchars($user['email']); ?></h2>
            <p class="text-textMuted text-sm uppercase tracking-wider font-bold mb-4"><?php echo htmlspecialchars($user['role']); ?></p>

            <div class="text-xs text-textMuted bg-gray-50 inline-block px-3 py-1 rounded-full border border-gray-200">
                Member since <?php echo date('F Y', strtotime($user['created_at'])); ?>
            </div>
        </div>

        <!-- Account Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <a href="../../includes/logout_handler.php" class="flex items-center justify-between p-4 hover:bg-gray-50 transition border-b border-gray-100 last:border-0 text-danger">
                <span class="font-medium">Sign Out</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </a>
        </div>

    </div>

    <!-- Bottom Nav -->
    <div class="fixed bottom-0 w-full bg-white border-t border-gray-200 flex justify-around p-2 z-30 pb-safe">
        <a href="dashboard.php" class="flex flex-col items-center p-2 text-textMuted hover:text-primary transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-xs font-medium">Home</span>
        </a>
        <a href="history.php" class="flex flex-col items-center p-2 text-textMuted hover:text-primary transition">
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-xs font-medium">History</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center p-2 text-primary">
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span class="text-xs font-medium">Profile</span>
        </a>
    </div>

</body>
</html>
