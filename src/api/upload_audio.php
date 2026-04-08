<?php
// src/api/upload_audio.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'No audio file uploaded or upload error']);
        exit;
    }

    $submissionId = $_POST['submission_id'] ?? null;
    $questionId = $_POST['question_id'] ?? null;

    if (!$submissionId || !$questionId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing submission or question ID']);
        exit;
    }

    // Verify ownership of submission
    $stmt = $pdo->prepare("SELECT user_id FROM submissions WHERE id = ?");
    $stmt->execute([$submissionId]);
    $userId = $stmt->fetchColumn();

    if ($userId != $_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Save File
    // Relative to src/api -> ../uploads/audio/
    $uploadDir = __DIR__ . '/../uploads/audio/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Determine extension from uploaded file name or mime type
    $ext = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);
    if (!$ext || !in_array($ext, ['webm', 'mp4', 'ogg', 'wav'])) {
        $ext = 'webm'; // Fallback
    }

    $fileName = 'sub_' . $submissionId . '_q_' . $questionId . '_' . time() . '.' . $ext;
    $filePath = $uploadDir . $fileName;

    // DB Path (relative to src root)
    $dbPath = 'uploads/audio/' . $fileName;

    if (move_uploaded_file($_FILES['audio']['tmp_name'], $filePath)) {
        // Save to DB
        $stmt = $pdo->prepare("INSERT INTO submission_answers (submission_id, question_id, audio_path) VALUES (?, ?, ?)");
        $stmt->execute([$submissionId, $questionId, $dbPath]);

        echo json_encode(['success' => true, 'path' => $dbPath]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save file']);
    }
}
?>
