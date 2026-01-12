<?php
require_once __DIR__ . '/../includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login($email, $password)) {
        if (isAdmin()) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jules - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        midnight: '#1a1f3c', // Midnight Navy guess
                        neon: '#a855f7', // Neon Violet guess
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-midnight text-white flex items-center justify-center h-screen">
    <div class="w-full max-w-md p-8 bg-gray-800 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold mb-6 text-center text-neon">Jules</h1>
        <h2 class="text-xl mb-4 text-center">Login</h2>

        <?php if ($error): ?>
            <div class="bg-red-500 text-white p-2 rounded mb-4 text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-300 mb-2" for="email">Email</label>
                <input class="w-full p-3 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-neon" type="email" name="email" id="email" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-300 mb-2" for="password">Password</label>
                <input class="w-full p-3 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-neon" type="password" name="password" id="password" required>
            </div>
            <button class="w-full bg-neon hover:bg-purple-600 text-white font-bold py-3 rounded transition duration-200" type="submit">
                Login
            </button>
        </form>
        <div class="mt-4 text-center">
            <a href="register.php" class="text-gray-400 hover:text-white">Don't have an account? Register</a>
        </div>
    </div>
</body>
</html>
