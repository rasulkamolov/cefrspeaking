<?php
// database/seed_100.php
require_once __DIR__ . '/../src/includes/db.php';

echo "Generating 100 Mock Tests with Real Images...\n";

// Curated list of high-quality, stable image URLs (Unsplash/Pexels via reliable sources or direct links if possible)
// Using Picsum Photos as a reliable source for "Real" random photos that aren't just text.
// We append a random seed to get different images.
$base_image_url = "https://picsum.photos/seed/";

// Topics Arrays (Kept from previous version)
$topics_part1 = [
    "Hometown", "Family", "Work", "Studies", "Hobbies", "Travel", "Music", "Food",
    "Sports", "Books", "Movies", "Weather", "Technology", "Internet", "Social Media",
    "Friends", "Weekends", "Holidays", "Shopping", "Transport", "Health", "Fitness",
    "Pets", "Childhood", "Dreams", "Future Plans", "Learning English", "Daily Routine"
];

$questions_part1 = [
    "Tell me about your [topic].",
    "Do you prefer X or Y regarding [topic]?",
    "How often do you engage in [topic]?",
    "What do you like most about [topic]?",
    "Is [topic] popular in your country?",
    "Did you enjoy [topic] as a child?",
    "How has [topic] changed recently?",
    "What are your future plans regarding [topic]?"
];

$topics_part1_2 = [
    "City vs Countryside", "Car vs Public Transport", "Online Learning vs Classroom",
    "Working from Home vs Office", "Cooking at Home vs Eating Out", "Summer vs Winter",
    "Team Sports vs Individual Sports", "Paper Books vs E-Books", "Movies at Home vs Cinema",
    "Card Payments vs Cash"
];

$topics_part2 = [
    "A memorable holiday", "A difficult decision", "A person you admire", "A goal you achieved",
    "A useful website", "A historical place", "A gift you received", "A skill you learned",
    "A mistake you made", "A celebration you attended", "A piece of technology", "A book you read"
];

$topics_part3 = [
    ["topic" => "Space Exploration", "for" => ["Scientific discovery", "Survival of humanity"], "against" => ["Too expensive", "Earth problems first"]],
    ["topic" => "Artificial Intelligence", "for" => ["Efficiency", "Medical advances"], "against" => ["Job loss", "Loss of privacy"]],
    ["topic" => "Social Media", "for" => ["Connectivity", "Information sharing"], "against" => ["Mental health issues", "Misinformation"]],
    ["topic" => "Globalization", "for" => ["Cultural exchange", "Economic growth"], "against" => ["Loss of identity", "Exploitation"]],
    ["topic" => "Remote Work", "for" => ["Flexibility", "Less commuting"], "against" => ["Isolation", "Communication barriers"]],
    ["topic" => "Tourism", "for" => ["Economic boost", "Cultural understanding"], "against" => ["Environmental damage", "Overcrowding"]],
    ["topic" => "Electric Cars", "for" => ["Eco-friendly", "Low running costs"], "against" => ["Battery disposal", "Range anxiety"]],
    ["topic" => "Universal Basic Income", "for" => ["Poverty reduction", "Security"], "against" => ["Cost", "Reduced motivation"]],
    ["topic" => "Online Privacy", "for" => ["Safety", "Freedom"], "against" => ["National security", "Crime prevention"]],
    ["topic" => "Genetic Engineering", "for" => ["Curing diseases", "Better crops"], "against" => ["Ethical concerns", "Unforeseen consequences"]]
];

for ($i = 1; $i <= 100; $i++) {
    $title = "Mock Exam #" . $i;
    $desc = "A complete practice test with real images.";

    // Create Test
    $stmt = $pdo->prepare("INSERT INTO tests (title, description) VALUES (?, ?)");
    $stmt->execute([$title, $desc]);
    $testId = $pdo->lastInsertId();

    // Part 1.1 (3 Questions)
    for ($j = 1; $j <= 3; $j++) {
        $topic = $topics_part1[array_rand($topics_part1)];
        $q_template = $questions_part1[array_rand($questions_part1)];
        $question = str_replace("[topic]", strtolower($topic), $q_template);

        $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, part_type, content, sequence) VALUES (?, '1.1', ?, ?)");
        $stmt->execute([$testId, $question, $j]);
    }

    // Part 1.2 (2 Images)
    $topic1_2 = $topics_part1_2[array_rand($topics_part1_2)];
    $prompt = "Compare these two pictures regarding " . $topic1_2;

    // Generate unique reliable image URLs
    // Picsum allows /seed/{seed}/width/height
    $seed1 = uniqid() . "1";
    $seed2 = uniqid() . "2";
    $url1 = $base_image_url . $seed1 . "/600/400";
    $url2 = $base_image_url . $seed2 . "/600/400";

    $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, part_type, content, media_url, media_url_2, sequence) VALUES (?, '1.2', ?, ?, ?, ?)");
    $stmt->execute([$testId, $prompt, $url1, $url2, 1]);

    // Part 2 (Monologue)
    $topic2 = $topics_part2[array_rand($topics_part2)];
    $content2 = json_encode([
        "topic" => $topic2,
        "points" => ["What it was", "When it happened", "Who was involved", "Why it was important"]
    ]);

    $seed3 = uniqid() . "3";
    $urlPart2 = $base_image_url . $seed3 . "/600/400";

    $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, part_type, content, media_url, sequence) VALUES (?, '2', ?, ?, ?)");
    $stmt->execute([$testId, $content2, $urlPart2, 1]);

    // Part 3 (Debate)
    $topic3Data = $topics_part3[array_rand($topics_part3)];
    $content3 = json_encode([
        "topic" => $topic3Data['topic'],
        "for_prompts" => $topic3Data['for'],
        "against_prompts" => $topic3Data['against']
    ]);

    $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, part_type, content, sequence) VALUES (?, '3', ?, ?)");
    $stmt->execute([$testId, $content3, 1]);
}

echo "Successfully generated 100 tests with real photo URLs.\n";
?>
