<?php
// database/seed_50_sqlite.php
require_once __DIR__ . '/setup_sqlite.php';

echo "Starting seed of 50 tests with semantic images...\n";

// Categories and keywords for LoremFlickr
$topics = [
    ['title' => 'Travel & Transport', 'keyword' => 'travel', 'q1' => 'Describe your last holiday.', 'q1_2' => 'Which form of transport is better?', 'q2' => 'A Memorable Journey', 'q3' => 'Impact of Tourism'],
    ['title' => 'Technology', 'keyword' => 'technology', 'q1' => 'How often do you use your phone?', 'q1_2' => 'Digital vs Analog life.', 'q2' => 'A Useful Invention', 'q3' => 'AI in Society'],
    ['title' => 'Education', 'keyword' => 'education', 'q1' => 'Did you enjoy school?', 'q1_2' => 'Online learning vs Classroom.', 'q2' => 'A Favorite Teacher', 'q3' => 'Cost of Higher Education'],
    ['title' => 'Environment', 'keyword' => 'nature', 'q1' => 'Do you recycle?', 'q1_2' => 'City life vs Countryside.', 'q2' => 'A Natural Disaster', 'q3' => 'Climate Change Action'],
    ['title' => 'Food & Health', 'keyword' => 'food', 'q1' => 'What is your favorite dish?', 'q1_2' => 'Fast food vs Home cooking.', 'q2' => 'A Special Meal', 'q3' => 'Government role in Public Health'],
    ['title' => 'Sports & Hobbies', 'keyword' => 'sports', 'q1' => 'Do you play any sports?', 'q1_2' => 'Team sports vs Individual sports.', 'q2' => 'A Sporting Event', 'q3' => 'Salaries of Athletes'],
    ['title' => 'Work & Career', 'keyword' => 'business', 'q1' => 'What is your dream job?', 'q1_2' => 'Remote work vs Office.', 'q2' => 'A Difficult Project', 'q3' => 'Work-Life Balance'],
    ['title' => 'Art & Culture', 'keyword' => 'art', 'q1' => 'Do you visit museums?', 'q1_2' => 'Modern art vs Classical art.', 'q2' => 'A Cultural Festival', 'q3' => 'Funding for Arts'],
    ['title' => 'Family & Friends', 'keyword' => 'people', 'q1' => 'Who are you closest to?', 'q1_2' => 'Large family vs Small family.', 'q2' => 'A Childhood Memory', 'q3' => 'Aging Population'],
    ['title' => 'Shopping', 'keyword' => 'fashion', 'q1' => 'Do you like shopping?', 'q1_2' => 'Online shopping vs Malls.', 'q2' => 'A Bad Purchase', 'q3' => 'Consumerism'],
];

// We need 50 tests, so we will loop 5 times through the 10 topics
for ($i = 0; $i < 50; $i++) {
    $topicIndex = $i % count($topics);
    $t = $topics[$topicIndex];
    $testNum = $i + 1;
    $title = "Mock Exam #$testNum: " . $t['title'];

    // Insert Test
    $stmt = $pdo->prepare("INSERT INTO tests (title, description) VALUES (?, ?)");
    $stmt->execute([$title, "Full mock exam focusing on " . $t['title']]);
    $testId = $pdo->lastInsertId();

    // Part 1.1 (3 Questions)
    $q1s = [$t['q1'], "Is this popular in your country?", "How has this changed recently?"];
    foreach ($q1s as $idx => $q) {
        $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, part_type, content, sequence) VALUES (?, '1.1', ?, ?)");
        $stmt->execute([$testId, $q, $idx + 1]);
    }

    // Part 1.2 (2 Images, 3 Questions)
    // Use LoremFlickr with keyword. Add random param to ensure they are different images if possible, though loremflickr randomizes by default.
    $url1 = "https://loremflickr.com/600/400/" . $t['keyword'] . "?lock=" . ($i * 10 + 1);
    $url2 = "https://loremflickr.com/600/400/" . $t['keyword'] . "?lock=" . ($i * 10 + 2);

    $q1_2s = [$t['q1_2'], "What are the advantages of the first picture?", "Which one would you choose?"];
    foreach ($q1_2s as $idx => $q) {
        $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, part_type, content, media_url, media_url_2, sequence) VALUES (?, '1.2', ?, ?, ?, ?)");
        $stmt->execute([$testId, $q, $url1, $url2, $idx + 1]);
    }

    // Part 2 (1 Image, Bullet Points)
    $urlPart2 = "https://loremflickr.com/600/400/" . $t['keyword'] . "?lock=" . ($i * 10 + 3);
    $content2 = json_encode([
        "topic" => $t['q2'],
        "points" => ["What happened", "When it happened", "Who was there", "Why it was significant"]
    ]);
    $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, part_type, content, media_url, sequence) VALUES (?, '2', ?, ?, ?)");
    $stmt->execute([$testId, $content2, $urlPart2, 1]);

    // Part 3 (Debate)
    $content3 = json_encode([
        "topic" => $t['q3'],
        "for_prompts" => ["Economic benefits", "Social progress", "Individual freedom"],
        "against_prompts" => ["Environmental cost", "Loss of tradition", "Inequality"]
    ]);
    $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, part_type, content, sequence) VALUES (?, '3', ?, ?)");
    $stmt->execute([$testId, $content3, 1]);

    if (($i + 1) % 10 === 0) {
        echo "Generated " . ($i + 1) . " tests...\n";
    }
}

echo "Seeding complete. 50 tests created.\n";
?>
