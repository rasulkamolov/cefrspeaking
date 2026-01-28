<?php
// database/setup_sqlite.php
require_once __DIR__ . '/../src/includes/db.php';

echo "Setting up SQLite database at $dbPath...\n";

// Drop tables if they exist
$pdo->exec("DROP TABLE IF EXISTS grades");
$pdo->exec("DROP TABLE IF EXISTS submission_answers");
$pdo->exec("DROP TABLE IF EXISTS submissions");
$pdo->exec("DROP TABLE IF EXISTS test_questions");
$pdo->exec("DROP TABLE IF EXISTS tests");
$pdo->exec("DROP TABLE IF EXISTS users");

// Create Users Table
$pdo->exec("CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'student', -- 'student', 'admin'
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Create Tests Table
$pdo->exec("CREATE TABLE tests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Create Test Questions Table
$pdo->exec("CREATE TABLE test_questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    test_id INTEGER NOT NULL,
    part_type TEXT NOT NULL, -- '1.1', '1.2', '2', '3'
    content TEXT,
    media_url TEXT,
    media_url_2 TEXT,
    sequence INTEGER NOT NULL,
    FOREIGN KEY(test_id) REFERENCES tests(id) ON DELETE CASCADE
)");

// Create Submissions Table
$pdo->exec("CREATE TABLE submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    test_id INTEGER NOT NULL,
    status TEXT DEFAULT 'in_progress', -- 'in_progress', 'completed', 'graded'
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(test_id) REFERENCES tests(id) ON DELETE CASCADE
)");

// Create Submission Answers (Audio) Table
$pdo->exec("CREATE TABLE submission_answers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    submission_id INTEGER NOT NULL,
    question_id INTEGER NOT NULL,
    audio_path TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    FOREIGN KEY(question_id) REFERENCES test_questions(id) ON DELETE CASCADE
)");

// Create Grades Table
$pdo->exec("CREATE TABLE grades (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    submission_id INTEGER NOT NULL,
    range_score INTEGER CHECK(range_score BETWEEN 0 AND 6),
    accuracy_score INTEGER CHECK(accuracy_score BETWEEN 0 AND 6),
    fluency_score INTEGER CHECK(fluency_score BETWEEN 0 AND 6),
    coherence_score INTEGER CHECK(coherence_score BETWEEN 0 AND 6),
    comments TEXT,
    graded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(submission_id) REFERENCES submissions(id) ON DELETE CASCADE
)");

echo "Tables created.\n";

// Seed Initial Admin and Student
$stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
// Admin: admin@jules.com / admin123
$stmt->execute(['admin@jules.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
// Student: student@jules.com / student123
$stmt->execute(['student@jules.com', password_hash('student123', PASSWORD_DEFAULT), 'student']);

echo "Initial users seeded.\n";
echo "Database setup complete.\n";
?>
