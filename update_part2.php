<?php
$dbPath = __DIR__ . '/database/jules.db';
$pdo = new PDO("sqlite:$dbPath");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$content = json_encode([
    "topic" => "Describe a time you had to make a critical decision.",
    "points" => [
        "What the decision was",
        "Why it was critical",
        "How you felt about it",
        "What the outcome was"
    ]
]);

$stmt = $pdo->prepare("UPDATE test_questions SET content = ? WHERE part_type = '2'");
$stmt->execute([$content]);

echo "Updated Part 2 content to JSON with bullet points.\n";

// Verify
$stmt = $pdo->query("SELECT content FROM test_questions WHERE part_type = '2'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "New Content: " . $row['content'] . "\n";
?>
