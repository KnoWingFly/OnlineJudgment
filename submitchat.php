<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['isloggedin']) || !isset($_SESSION['userid'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$userid = $_SESSION['userid'];

require_once('settings.php');

try {
    // Create PDO connection
    $dsn = "mysql:host=localhost;dbname={$DBNAME};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $DBUSER, $DBPASS, $options);
    
    // Get and validate message
    $msg = trim($_POST['message'] ?? '');
    if (empty($msg)) {
        http_response_code(400);
        exit('Message cannot be empty');
    }
    
    // Prepare and execute insert
    $stmt = $pdo->prepare("INSERT INTO chat (userid, time, msg) VALUES (?, ?, ?)");
    $stmt->execute([$userid, time(), $msg]);
    
    http_response_code(200);
    echo 'Message sent successfully';
    
} catch (PDOException $e) {
    // Log error safely
    error_log("Database Error: " . $e->getMessage());
    http_response_code(500);
    exit('Failed to send message');
}