<?php
require_once __DIR__ . '/../includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    // Role selection could be hidden or strictly student for public registration
    // For this demo, defaults to student

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $result = register($email, $password);
        if ($result === true) {
            $success = "Registration successful! You can now login.";
        } else {
            $error = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford CEFR Speaking - Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        midnight: '#1a1f3c',
                        neon: '#a855f7',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-midnight text-white flex items-center justify-center h-screen">
    <div class="w-full max-w-md p-8 bg-gray-800 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold mb-6 text-center text-neon">Oxford CEFR Speaking</h1>
        <h2 class="text-xl mb-4 text-center">Register</h2>

        <?php if ($error): ?>
            <div class="bg-red-500 text-white p-2 rounded mb-4 text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-500 text-white p-2 rounded mb-4 text-center">
                <?php echo htmlspecialchars($success); ?>
                <div class="mt-2">
                    <a href="login.php" class="underline">Go to Login</a>
                </div>
            </div>
        <?php else: ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-300 mb-2" for="email">Email</label>
                <input class="w-full p-3 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-neon" type="email" name="email" id="email" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-300 mb-2" for="password">Password</label>
                <input class="w-full p-3 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-neon" type="password" name="password" id="password" required>
            </div>
             <div class="mb-6">
                <label class="block text-gray-300 mb-2" for="confirm_password">Confirm Password</label>
                <input class="w-full p-3 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-neon" type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button class="w-full bg-neon hover:bg-purple-600 text-white font-bold py-3 rounded transition duration-200" type="submit">
                Register
            </button>
        </form>
        <div class="mt-4 text-center">
            <a href="login.php" class="text-gray-400 hover:text-white">Already have an account? Login</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
