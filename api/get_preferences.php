<?php
session_start();
$user_id = $_SESSION['user_id']; // <- add this line at the top after session_start
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Not authenticated"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT type, value FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $topics = [];
    $sentiments = [];

    foreach ($rows as $row) {
        if ($row['type'] === 'topic') {
            $topics[] = $row['value'];
        } elseif ($row['type'] === 'sentiment') {
            $sentiments[] = $row['value'];
        }
    }

    echo json_encode([
        "topics" => $topics,
        "sentiments" => $sentiments
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to load preferences"]);
}
exit;
?>