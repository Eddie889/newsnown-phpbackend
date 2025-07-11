<?php
session_start();
header('Content-Type: application/json');

// Clear session variables
$_SESSION = [];

// Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

http_response_code(200);
echo json_encode(["message" => "Logged out successfully"]);
?>