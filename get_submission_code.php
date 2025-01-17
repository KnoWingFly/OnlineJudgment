<?php
session_start();
if (!isset($_SESSION['isloggedin'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

include('settings.php');

$conn = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get submission ID from request
$submissionId = isset($_GET['submission_id']) ? (int)$_GET['submission_id'] : 0;

// Verify the submission belongs to the current user and get submission details
$stmt = $conn->prepare("SELECT s.id, s.problemid, s.time, s.filename, u.username 
                       FROM submissions s 
                       JOIN users u ON s.userid = u.id 
                       WHERE s.id = ? AND s.userid = ?");
$stmt->bind_param("ii", $submissionId, $_SESSION['userid']);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Get the file extension from the filename
    $filename = $row['filename'];
    $baseDir = dirname(__FILE__);
    $codePath = $baseDir . "/code/" . $row['username'] . "/" . $row['problemid'] . "/";
    
    // If filename is stored in database, use it directly
    if ($filename) {
        $submissionFile = $codePath . $filename;
        if (file_exists($submissionFile)) {
            $code = htmlspecialchars(file_get_contents($submissionFile));
            echo json_encode(['success' => true, 'code' => $code]);
            exit;
        }
    }
    
    // Fallback: Search for files with common extensions
    $extensions = [
        'cpp' => ['cpp', 'cc', 'cxx'],
        'c' => ['c'],
        'java' => ['java'],
        'python' => ['py'],
        'go' => ['go']
    ];
    
    $timestamp = date('Y-m-d_H-i-s', $row['time']);
    $found = false;
    
    // First try exact timestamp match
    foreach ($extensions as $lang => $exts) {
        foreach ($exts as $ext) {
            $exactFile = $codePath . "jawaban1_" . $timestamp . "." . $ext;
            if (file_exists($exactFile)) {
                $code = htmlspecialchars(file_get_contents($exactFile));
                echo json_encode(['success' => true, 'code' => $code]);
                $found = true;
                break 2;
            }
        }
    }
    
    // If exact match not found, look for closest file before submission time
    if (!$found) {
        $closestFile = null;
        $closestTime = 0;
        
        foreach ($extensions as $lang => $exts) {
            foreach ($exts as $ext) {
                $pattern = $codePath . "jawaban1*." . $ext;
                $files = glob($pattern);
                
                foreach ($files as $file) {
                    $fileTime = filemtime($file);
                    if ($fileTime <= $row['time'] && $fileTime > $closestTime) {
                        $closestFile = $file;
                        $closestTime = $fileTime;
                    }
                }
            }
        }
        
        if ($closestFile && file_exists($closestFile)) {
            $code = htmlspecialchars(file_get_contents($closestFile));
            echo json_encode(['success' => true, 'code' => $code]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Code file not found']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Submission not found or unauthorized']);
}

$conn->close();
?>