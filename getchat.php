<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['userid'])) {
    http_response_code(401);
    exit('Unauthorized');
}

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
    
    // Get last message ID from request
    $lastId = filter_input(INPUT_GET, 'lastId', FILTER_VALIDATE_INT) ?: 0;
    
    // Only fetch messages newer than the last ID
    $stmt = $pdo->prepare("
        SELECT C.id, U.username, C.msg 
        FROM users U 
        JOIN chat C ON U.id = C.userid 
        WHERE C.id > ?
        ORDER BY C.id ASC
    ");
    
    $stmt->execute([$lastId]);
    $messages = $stmt->fetchAll();
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'messages' => $messages,
        'lastId' => empty($messages) ? $lastId : end($messages)['id']
    ]);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
}