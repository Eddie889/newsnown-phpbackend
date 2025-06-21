<?php
session_start();
header('Content-Type: application/json');

require __DIR__ . '/../config/db.php'; // Reuse your PDO connection

$data = json_decode(file_get_contents('php://input'), true);

$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Username and password are required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        echo json_encode(['user_id' => $user['id']]);
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode(['error' => 'Invalid username or password']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Login failed. Please try again.']);
}
?>