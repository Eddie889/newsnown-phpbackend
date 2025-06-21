<?php
// index.php

header('Content-Type: application/json');
echo json_encode([
    'status' => 'News Aggregator API is running',
    'endpoints' => [
        '/api/login.php',
        '/api/register.php',
        '/api/save_preferences.php',
        '/api/get_news.php'
    ]
]);
