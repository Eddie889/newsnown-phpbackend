<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'authenticated' => true,
        'user_id' => $_SESSION['user_id']
    ]);
} else {
    http_response_code(401); // Unauthorized
    echo json_encode([
        'authenticated' => false,
        'message' => 'User is not logged in.'
    ]);
}
?>