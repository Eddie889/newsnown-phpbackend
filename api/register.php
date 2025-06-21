<?php
header('Content-Type: application/json');
require __DIR__ . '/../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

// Input validation
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Username and password are required.']);
    exit;
}

if (strlen($username) < 3 || strlen($username) > 20) {
    http_response_code(400);
    echo json_encode(['error' => 'Username must be between 3 and 20 characters.']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 6 characters long.']);
    exit;
}

// Check if user exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->fetch()) {
    http_response_code(409); // Conflict
    echo json_encode(['error' => 'Username already taken.']);
    exit;
}

// Register new user
$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");

if ($stmt->execute([$username, $hashed])) {
    http_response_code(200);
    echo json_encode(['message' => 'Registration successful. You can now login.']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Registration failed. Please try again.']);
}
?>
