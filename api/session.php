<?php
session_start();
if (isset($_SESSION['user_id'])) {
    echo json_encode([
        "user_id" => $_SESSION['user_id'],
        "username" => $_SESSION['username']
    ]);
} else {
    echo json_encode(["error" => "Not logged in"]);
}
?>
