<?php
// src/includes/db.php

// 1. Try to load local configuration if it exists
$configFile = __DIR__ . '/config.php';
$configExists = file_exists($configFile);

if ($configExists) {
    require_once $configFile;
}

// 2. Fallback to Environment Variables or Defaults
$host = $db_host ?? getenv('DB_HOST') ?: '127.0.0.1';
$db   = $db_name ?? getenv('DB_NAME') ?: 'jules_db';
$user = $db_user ?? getenv('DB_USER') ?: 'jules';
$pass = $db_pass ?? getenv('DB_PASS') ?: 'password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {

    // Check if we are likely in an API request
    $isApi = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;

    if ($isApi) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed.']);
    } else {
        // User facing error
        http_response_code(500);
        echo "<div style='font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; color: #1e293b; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0;'>";
        echo "<div style='background: white; padding: 40px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); max-width: 500px; text-align: center; border: 1px solid #e2e8f0;'>";

        echo "<div style='background: #fee2e2; color: #ef4444; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto;'><svg width='32' height='32' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'></path></svg></div>";

        echo "<h1 style='font-size: 24px; font-weight: bold; margin-bottom: 10px;'>Connection Failed</h1>";
        echo "<p style='color: #64748b; margin-bottom: 20px; line-height: 1.5;'>The application could not connect to the database.</p>";

        if (!$configExists) {
            echo "<div style='background: #fff7ed; border-left: 4px solid #f97316; padding: 15px; text-align: left; font-size: 14px; margin-bottom: 20px;'>";
            echo "<strong style='color: #c2410c;'>Action Required:</strong><br>";
            echo "1. Rename <code style='background: #fff; padding: 2px 4px; border-radius: 4px;'>src/includes/config.php.sample</code> to <code style='background: #fff; padding: 2px 4px; border-radius: 4px;'>config.php</code>.<br>";
            echo "2. Open it and update the database credentials.";
            echo "</div>";
        } else {
             echo "<div style='background: #f0fdf4; border-left: 4px solid #22c55e; padding: 15px; text-align: left; font-size: 14px; margin-bottom: 20px;'>";
             echo "Configuration file found, but settings appear incorrect.";
             echo "</div>";
        }

        echo "<a href='/test_db.php' style='display: inline-block; background: #3b82f6; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 500; transition: background 0.2s;'>Run Diagnostic Tool</a>";

        // Debug info (hidden by default unless inspected)
        echo "<!-- " . htmlspecialchars($e->getMessage()) . " -->";

        echo "</div></div>";
    }

    // Stop execution
    exit;
}
?>
