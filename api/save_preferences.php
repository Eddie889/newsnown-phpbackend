<?php
session_start();
header('Content-Type: application/json');

// Optional: Check if user is authenticated via session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = intval($data['user_id'] ?? 0);
$topics = $data['topics'] ?? [];
$sentiments = $data['sentiments'] ?? [];

// Input validation
if ($user_id !== $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['error' => 'Permission denied.']);
    exit;
}

if (!is_array($topics) || !is_array($sentiments)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input format']);
    exit;
}

// DB connection
$conn = new mysqli('localhost', 'root', '', 'news_aggregator');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Clear old preferences
$stmt = $conn->prepare("DELETE FROM user_preferences WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Prepare insert statement once
$insertStmt = $conn->prepare("INSERT INTO user_preferences (user_id, type, value) VALUES (?, ?, ?)");

// Insert new topics
foreach ($topics as $topic) {
    $topic = trim($topic);
    if ($topic !== '') {
        $type = 'topic';
        $insertStmt->bind_param("iss", $user_id, $type, $topic);
        $insertStmt->execute();
    }
}

// Insert new sentiments
foreach ($sentiments as $sentiment) {
    $sentiment = trim($sentiment);
    if ($sentiment !== '') {
        $type = 'sentiment';
        $insertStmt->bind_param("iss", $user_id, $type, $sentiment);
        $insertStmt->execute();
    }
}

$insertStmt->close();
$conn->close();

echo json_encode(['message' => 'Preferences saved successfully']);
exit;
?>