<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "User not authenticated"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Accept 'search' or 'topic' as valid inputs
$topic = trim($_GET['search'] ?? ($_GET['topic'] ?? ''));

require_once __DIR__ . '/../config/config.php';

// Try fallback to user preferences if topic is still empty
if (!$topic) {
    $stmt = $conn->prepare("SELECT value FROM user_preferences WHERE user_id = ? AND type = 'topic'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $topics = [];
    while ($row = $result->fetch_assoc()) {
        $topics[] = $row['value'];
    }
    $stmt->close();

    if (!empty($topics)) {
        $topic = $topics[array_rand($topics)];
    }
}

// FINAL fallback if all else fails
if (!$topic) {
    $topic = 'technology'; // default fail-safe topic
}

// Fetch news from NewsAPI
$apiKey = "8f3fef04217b4d2a985c362dd110c6ca";
$newsUrl = "https://newsapi.org/v2/everything?q=" . urlencode($topic) . "&apiKey=" . $apiKey;

// Use cURL to fetch from NewsAPI
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $newsUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to fetch news"]);
    curl_close($ch);
    $conn->close();
    exit;
}

curl_close($ch);
$newsData = json_decode($response, true);

if (!isset($newsData['articles'])) {
    http_response_code(502);
    echo json_encode(["error" => "Invalid response from news API"]);
    $conn->close();
    exit;
}

// Simple sentiment analysis
function analyzeSentiment($text) {
    $positive = ['good', 'great', 'excellent', 'positive', 'happy', 'gain', 'success'];
    $negative = ['bad', 'poor', 'terrible', 'negative', 'sad', 'loss', 'fail'];

    $text = strtolower($text);
    $score = 0;

    foreach ($positive as $word) {
        if (strpos($text, $word) !== false) $score++;
    }
    foreach ($negative as $word) {
        if (strpos($text, $word) !== false) $score--;
    }

    return $score > 0 ? 'Positive' : ($score < 0 ? 'Negative' : 'Neutral');
}

// Format response
$articles = [];

foreach ($newsData['articles'] as $article) {
    $title = $article['title'] ?? '';
    $desc = $article['description'] ?? '';
    $image = $article['urlToImage'] ?? '';
    $url = $article['url'] ?? '';

    if ($title || $desc) {
        $sentiment = analyzeSentiment($title . ' ' . $desc);
        $articles[] = [
            'title' => $title,
            'description' => $desc,
            'image_url' => $image,
            'url' => $url,
            'sentiment' => $sentiment
        ];
    }
}

$conn->close();
echo json_encode($articles);
exit;
?>
