<?php
// test_db.php
// Run this file in your browser or CLI to test the database connection.

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function test_connection($host, $db, $user, $pass) {
    try {
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $pdo = new PDO($dsn, $user, $pass, $options);
        return ['success' => true, 'pdo' => $pdo];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage(), 'code' => $e->getCode()];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Diagnostics</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 p-6">

    <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="bg-blue-600 text-white p-6">
            <h1 class="text-2xl font-bold">Database Diagnostic Tool</h1>
            <p class="opacity-80">Oxford CEFR Speaking Platform</p>
        </div>

        <div class="p-6 space-y-6">

            <!-- Check Config File -->
            <div class="border rounded-lg p-4">
                <h2 class="font-bold text-lg mb-2 flex items-center">
                    <span class="w-6 h-6 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center mr-2 text-sm">1</span>
                    Configuration File
                </h2>
                <?php
                $configFile = __DIR__ . '/src/includes/config.php';
                if (file_exists($configFile)) {
                    echo '<div class="text-green-600 font-medium flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Found: src/includes/config.php
                    </div>';
                    require_once $configFile;
                } else {
                     echo '<div class="text-red-600 font-medium flex items-center mb-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Not Found
                    </div>
                    <p class="text-sm text-gray-600 bg-red-50 p-3 rounded">
                        Please rename <code>src/includes/config.php.sample</code> to <code>src/includes/config.php</code> and edit it.
                    </p>';
                }
                ?>
            </div>

            <!-- Connection Attempt -->
            <div class="border rounded-lg p-4">
                <h2 class="font-bold text-lg mb-2 flex items-center">
                    <span class="w-6 h-6 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center mr-2 text-sm">2</span>
                    Connection Attempt
                </h2>

                <?php
                $host = $db_host ?? getenv('DB_HOST') ?: '127.0.0.1';
                $db   = $db_name ?? getenv('DB_NAME') ?: 'jules_db';
                $user = $db_user ?? getenv('DB_USER') ?: 'jules';
                $pass = $db_pass ?? getenv('DB_PASS') ?: 'password';

                // Hide password for display
                $displayPass = str_repeat('*', strlen($pass));
                if (strlen($pass) == 0) $displayPass = "(empty)";

                echo "<div class='grid grid-cols-2 gap-2 text-sm text-gray-600 mb-4 bg-gray-50 p-3 rounded'>";
                echo "<div>Host: <span class='font-mono text-gray-900'>$host</span></div>";
                echo "<div>Database: <span class='font-mono text-gray-900'>$db</span></div>";
                echo "<div>User: <span class='font-mono text-gray-900'>$user</span></div>";
                echo "<div>Pass: <span class='font-mono text-gray-900'>$displayPass</span></div>";
                echo "</div>";

                $result = test_connection($host, $db, $user, $pass);

                if ($result['success']) {
                    echo '<div class="bg-green-100 text-green-800 p-4 rounded-lg flex items-start">
                        <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <h3 class="font-bold">Connection Successful!</h3>
                            <p class="text-sm">The application should be working.</p>
                        </div>
                    </div>';

                    // Version Check
                    $stmt = $result['pdo']->query("SELECT version()");
                    $version = $stmt->fetchColumn();
                    echo "<div class='mt-2 text-xs text-gray-500'>MySQL Version: $version</div>";

                } else {
                    echo '<div class="bg-red-100 text-red-800 p-4 rounded-lg flex items-start mb-4">
                        <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'></path></svg>
                        <div>
                            <h3 class="font-bold">Connection Failed</h3>
                            <p class="font-mono text-sm mt-1">' . htmlspecialchars($result['error']) . '</p>
                        </div>
                    </div>';

                    // Analysis
                    echo '<div class="text-sm text-gray-700 space-y-2">';
                    $msg = $result['error'];
                    if (strpos($msg, 'Access denied') !== false) {
                        echo "<p><strong>Diagnosis:</strong> Wrong Username or Password.</p>";
                        echo "<p><strong>Tip:</strong> If you are on shared hosting (cPanel), your username often includes a prefix (e.g., <code>username_jules</code>).</p>";
                    } elseif (strpos($msg, 'Unknown database') !== false) {
                        echo "<p><strong>Diagnosis:</strong> The database name <code>$db</code> does not exist.</p>";
                        echo "<p><strong>Tip:</strong> Create the database first via your hosting control panel.</p>";
                    } elseif (strpos($msg, 'Connection refused') !== false || strpos($msg, 'server requested authentication method unknown') !== false) {
                         echo "<p><strong>Diagnosis:</strong> Cannot reach the database server or protocol mismatch.</p>";
                         echo "<p><strong>Tip:</strong> Check if Host is <code>localhost</code> or something else.</p>";
                    }
                    echo '</div>';
                }
                ?>
            </div>

            <div class="text-center">
                <a href="/" class="text-blue-600 hover:underline text-sm font-medium">Return to Home</a>
            </div>

        </div>
    </div>

</body>
</html>
