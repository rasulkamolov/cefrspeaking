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
    <title>Oxford CEFR - Profile</title>
    <?php require_once __DIR__ . '/../../includes/theme.php'; ?>
</head>
<body class="bg-background text-textMain font-sans pb-24">

    <!-- Top Header -->
    <header class="bg-primary text-white pt-safe sticky top-0 z-20 shadow-md">
        <div class="px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold tracking-tight">Profile</h1>
             <div class="w-8"></div> <!-- Spacer -->
        </div>
    </header>

    <main class="container mx-auto px-4 mt-6 max-w-lg">

        <!-- User Info Card -->
        <div class="bg-surface p-8 rounded-3xl shadow-sm border border-gray-100 text-center mb-6">
            <div class="w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                <svg class="w-12 h-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>

            <h2 class="text-2xl font-bold text-textMain mb-1 tracking-tight"><?php echo htmlspecialchars($user['email']); ?></h2>
            <p class="text-textMuted text-xs uppercase tracking-wider font-bold mb-6"><?php echo htmlspecialchars($user['role']); ?></p>

            <div class="text-xs text-indigo-500 bg-indigo-50 inline-flex items-center px-4 py-2 rounded-full border border-indigo-100 font-medium">
                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Member since <?php echo date('F Y', strtotime($user['created_at'])); ?>
            </div>
        </div>

        <!-- Account Actions -->
        <h3 class="text-textMuted text-xs font-bold uppercase tracking-wider mb-3 ml-4">Settings</h3>
        <div class="bg-surface rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <a href="../../includes/logout_handler.php" class="flex items-center justify-between p-5 hover:bg-gray-50 transition border-b border-gray-100 last:border-0 group">
                <div class="flex items-center text-danger font-medium">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Sign Out
                </div>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-danger transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

    </main>

    <!-- Bottom Nav -->
    <div class="fixed bottom-0 w-full bg-surface/90 backdrop-blur-md border-t border-gray-200 flex justify-around items-center px-2 py-2 pb-safe z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
        <a href="dashboard.php" class="flex flex-col items-center p-2 text-textMuted hover:text-accent w-16 transition">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-[10px] font-medium">Home</span>
        </a>
        <a href="history.php" class="flex flex-col items-center p-2 text-textMuted hover:text-accent w-16 transition">
             <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-[10px] font-medium">History</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center p-2 text-primary w-16">
             <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span class="text-[10px] font-bold">Profile</span>
        </a>
    </div>

</body>
</html>
