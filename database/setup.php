<?php
// database/setup.php
require_once __DIR__ . '/../src/includes/db.php';

echo "Setting up database...\n";

// Create Users Table
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT CHECK(role IN ('student', 'admin')) NOT NULL DEFAULT 'student',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Create Tests Table
$pdo->exec("CREATE TABLE IF NOT EXISTS tests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Create Test Parts/Questions Table
$pdo->exec("CREATE TABLE IF NOT EXISTS test_questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    test_id INTEGER NOT NULL,
    part_type TEXT NOT NULL, -- '1.1', '1.2', '2', '3'
    content TEXT, -- JSON or text content for the question/prompt
    media_url TEXT, -- Path to image if applicable
    sequence INTEGER NOT NULL,
    FOREIGN KEY(test_id) REFERENCES tests(id)
)");

// Create Submissions Table
$pdo->exec("CREATE TABLE IF NOT EXISTS submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    test_id INTEGER NOT NULL,
    status TEXT DEFAULT 'in_progress', -- 'in_progress', 'completed', 'graded'
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME,
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(test_id) REFERENCES tests(id)
)");

// Create Submission Answers (Audio) Table
$pdo->exec("CREATE TABLE IF NOT EXISTS submission_answers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    submission_id INTEGER NOT NULL,
    question_id INTEGER NOT NULL,
    audio_path TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(submission_id) REFERENCES submissions(id),
    FOREIGN KEY(question_id) REFERENCES test_questions(id)
)");

// Create Grades Table
$pdo->exec("CREATE TABLE IF NOT EXISTS grades (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    submission_id INTEGER NOT NULL,
    range_score INTEGER CHECK(range_score BETWEEN 0 AND 6),
    accuracy_score INTEGER CHECK(accuracy_score BETWEEN 0 AND 6),
    fluency_score INTEGER CHECK(fluency_score BETWEEN 0 AND 6),
    coherence_score INTEGER CHECK(coherence_score BETWEEN 0 AND 6),
    comments TEXT,
    graded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(submission_id) REFERENCES submissions(id)
)");

echo "Tables created.\n";

// Seed Data
// Check if admin exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
$stmt->execute(['admin@jules.com']);
if ($stmt->fetchColumn() == 0) {
    // Create Admin
    $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
    $stmt->execute(['admin@jules.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);

    // Create Student
    $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
    $stmt->execute(['student@jules.com', password_hash('student123', PASSWORD_DEFAULT), 'student']);

    echo "Users seeded.\n";
}

// Seed Test
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tests");
$stmt->execute();
if ($stmt->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO tests (title, description) VALUES ('CEFR Mock Exam 1', 'A standard mock exam covering A1-C1 levels.')");
    $testId = $pdo->lastInsertId();

    // Part 1.1 (A1-A2) - 3 Questions
    $questions1_1 = [
        "Tell me about your hometown.",
        "Who is your best friend and why?",
        "What are your hobbies?"
    ];
    foreach ($questions1_1 as $idx => $q) {
        $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, part_type, content, sequence) VALUES (?, '1.1', ?, ?)");
        $stmt->execute([$testId, $q, $idx + 1]);
    }

    // Part 1.2 (B1) - 3 Questions based on 2 photos
    // UPDATED: Seeding 3 separate questions for Part 1.2
    $questions1_2 = [
        "Compare these two pictures.",
        "Which mode of transport do you prefer?",
        "Why is it important to travel?"
    ];
    foreach ($questions1_2 as $idx => $q) {
        $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, part_type, content, media_url, sequence) VALUES (?, '1.2', ?, ?, ?)");
        $stmt->execute([$testId, $q, 'assets/images/transport.jpg', $idx + 1]);
    }


    // Part 2 (B2) - Abstract Monologue
    $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, part_type, content, media_url, sequence) VALUES (?, '2', ?, ?, ?)");
    $stmt->execute([$testId, 'Describe a time you had to make a critical decision.', 'assets/images/decisions.jpg', 1]);

    // Part 3 (C1) - Critical Analysis
    $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, part_type, content, sequence) VALUES (?, '3', ?, ?)");
    $content3 = json_encode([
        "topic" => "Gun Control",
        "for_prompts" => ["Self-defense is a right", "Deterrence against crime"],
        "against_prompts" => ["Higher accident rates", "Escalation of violence"]
    ]);
    $stmt->execute([$testId, $content3, 1]);

    echo "Test content seeded.\n";
}

echo "Database setup complete.\n";
?>
