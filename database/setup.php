<?php
// database/setup.php
require_once __DIR__ . '/../src/includes/db.php';

echo "Setting up MySQL database...\n";

// Drop tables if they exist to ensure fresh start (Optional, but good for dev)
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("DROP TABLE IF EXISTS users");
$pdo->exec("DROP TABLE IF EXISTS tests");
$pdo->exec("DROP TABLE IF EXISTS test_questions");
$pdo->exec("DROP TABLE IF EXISTS submissions");
$pdo->exec("DROP TABLE IF EXISTS submission_answers");
$pdo->exec("DROP TABLE IF EXISTS grades");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

// Create Users Table
$pdo->exec("CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Create Tests Table
$pdo->exec("CREATE TABLE tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Create Test Parts/Questions Table
$pdo->exec("CREATE TABLE test_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_id INT NOT NULL,
    part_type VARCHAR(50) NOT NULL, -- '1.1', '1.2', '2', '3'
    content TEXT,
    media_url TEXT,
    media_url_2 TEXT,
    sequence INT NOT NULL,
    FOREIGN KEY(test_id) REFERENCES tests(id) ON DELETE CASCADE
)");

// Create Submissions Table
$pdo->exec("CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    test_id INT NOT NULL,
    status VARCHAR(50) DEFAULT 'in_progress',
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(test_id) REFERENCES tests(id) ON DELETE CASCADE
)");

// Create Submission Answers (Audio) Table
$pdo->exec("CREATE TABLE submission_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    question_id INT NOT NULL,
    audio_path TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    FOREIGN KEY(question_id) REFERENCES test_questions(id) ON DELETE CASCADE
)");

// Create Grades Table
$pdo->exec("CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    range_score INT CHECK(range_score BETWEEN 0 AND 6),
    accuracy_score INT CHECK(accuracy_score BETWEEN 0 AND 6),
    fluency_score INT CHECK(fluency_score BETWEEN 0 AND 6),
    coherence_score INT CHECK(coherence_score BETWEEN 0 AND 6),
    comments TEXT,
    graded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(submission_id) REFERENCES submissions(id) ON DELETE CASCADE
)");

echo "Tables created.\n";

// Seed Data
// Create Admin
$stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
$stmt->execute(['admin@jules.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);

// Create Student
$stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
$stmt->execute(['student@jules.com', password_hash('student123', PASSWORD_DEFAULT), 'student']);

echo "Users seeded.\n";

// Seed Test
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
$questions1_2 = [
    "Compare these two pictures.",
    "Which mode of transport do you prefer?",
    "Why is it important to travel?"
];
foreach ($questions1_2 as $idx => $q) {
    // Using Picsum for the initial seed too
    $url1 = "https://picsum.photos/seed/setup1/600/400";
    $url2 = "https://picsum.photos/seed/setup2/600/400";
    $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, part_type, content, media_url, media_url_2, sequence) VALUES (?, '1.2', ?, ?, ?, ?)");
    $stmt->execute([$testId, $q, $url1, $url2, $idx + 1]);
}

// Part 2 (B2) - Abstract Monologue
$stmt = $pdo->prepare("INSERT INTO test_questions (test_id, part_type, content, media_url, sequence) VALUES (?, '2', ?, ?, ?)");
$urlPart2 = "https://picsum.photos/seed/setup3/600/400";
$content2 = json_encode([
    "topic" => "Critical Decisions",
    "points" => ["What the decision was", "Why it was critical", "How you felt", "The outcome"]
]);
$stmt->execute([$testId, $content2, $urlPart2, 1]);

// Part 3 (C1) - Critical Analysis
$stmt = $pdo->prepare("INSERT INTO test_questions (test_id, part_type, content, sequence) VALUES (?, '3', ?, ?)");
$content3 = json_encode([
    "topic" => "Gun Control",
    "for_prompts" => ["Self-defense is a right", "Deterrence against crime"],
    "against_prompts" => ["Higher accident rates", "Escalation of violence"]
]);
$stmt->execute([$testId, $content3, 1]);

echo "Test content seeded.\n";
echo "Database setup complete.\n";
?>
