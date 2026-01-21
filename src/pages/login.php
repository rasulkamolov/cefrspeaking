<?php
// src/pages/login.php
session_start();
require_once __DIR__ . '/../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] === 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: student/dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Oxford CEFR Speaking - Login</title>
    <?php require_once __DIR__ . '/../includes/theme.php'; ?>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen flex flex-col items-center justify-center p-6 pt-safe pb-safe">

    <!-- App Logo / Branding -->
    <div class="mb-10 text-center animate-fade-in-down">
        <div class="w-20 h-20 bg-primary rounded-2xl mx-auto shadow-xl flex items-center justify-center mb-4 rotate-3">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>
        </div>
        <h1 class="text-3xl font-bold text-textMain tracking-tight">Oxford CEFR</h1>
        <p class="text-textMuted text-sm font-medium mt-1">Speaking Exam Platform</p>
    </div>

    <!-- Login Card -->
    <div class="bg-surface w-full max-w-sm rounded-3xl shadow-2xl p-8 border border-gray-100">
        <h2 class="text-xl font-bold text-textMain mb-6">Welcome Back</h2>

        <?php if ($error): ?>
            <div class="bg-red-50 text-danger text-sm font-medium p-3 rounded-xl mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-5">
            <div>
                <label class="block text-textMuted text-xs font-bold uppercase tracking-wider mb-2 ml-1" for="email">Email Address</label>
                <div class="relative">
                    <span class="absolute left-4 top-3.5 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                    </span>
                    <input class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-textMain focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition text-sm font-medium" type="email" name="email" id="email" placeholder="student@example.com" required>
                </div>
            </div>

            <div>
                <label class="block text-textMuted text-xs font-bold uppercase tracking-wider mb-2 ml-1" for="password">Password</label>
                <div class="relative">
                     <span class="absolute left-4 top-3.5 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </span>
                    <input class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-textMain focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition text-sm font-medium" type="password" name="password" id="password" required>
                </div>
            </div>

            <button class="w-full bg-primary hover:bg-primaryHover text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-500/30 transition transform active-scale mt-4" type="submit">
                Sign In
            </button>
        </form>
    </div>

    <!-- Footer -->
    <div class="mt-8 text-center">
        <a href="register.php" class="text-textMuted text-sm font-medium hover:text-primary transition">Create an account</a>
    </div>

</body>
</html>
