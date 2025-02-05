<?php
// Start by clearing any output and setting error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['isloggedin'])) {
    header("Location: login.php");
    exit(0);
}

$username = $_SESSION['username'];
$userid = $_SESSION['userid'];

include('settings.php');

// Configure error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_log("Starting submission processing");

// Constants for verdicts
define('VERDICT', [
    'CORRECT' => 0,
    'COMPILE_ERROR' => 1,
    'WRONG' => 2,
    'TIME_EXCEEDED' => 3,
    'ILLEGAL_FILE' => 4,
    'RTE' => 5
]);

function executeCommand($scriptPath)
{
    chmod($scriptPath, 0755); // Ensure script is executable
    $cmd = "bash " . escapeshellarg($scriptPath);
    $output = [];
    $returncode = -1;
    exec($cmd, $output, $returncode);
    return [$output, $returncode];
}

try {
    $problemid = -1;
    foreach ($_FILES as $key => $value) {
        if (is_numeric($key)) {
            $problemid = (int) $key;
            break;
        }
    }

    if ($problemid === -1) {
        throw new Exception("No valid problem ID found in submission");
    }

    $db = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);
    if ($db->connect_error) {
        throw new Exception("Database connection failed: " . $db->connect_error);
    }

    // File Handling
    $baseDir = dirname(__FILE__);
    $userDir = "$baseDir/code/$username/";
    $uploadDir = "$userDir$problemid/";

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (isset($_FILES[$problemid]) && $_FILES[$problemid]["error"] === UPLOAD_ERR_OK) {
        $timestamp = date('Y-m-d_H-i-s');
        $originalName = basename($_FILES[$problemid]["name"]);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($ext, ['c', 'cpp', 'py', 'java', 'go'])) {
            throw new Exception("Invalid file type. Only .c, .cpp, .py, .java, and .go files are allowed.");
        }

        $filename = pathinfo($originalName, PATHINFO_FILENAME) . "_" . $timestamp . "." . $ext;
        $destFile = $uploadDir . $filename;
        
        if (!move_uploaded_file($_FILES[$problemid]["tmp_name"], $destFile)) {
            throw new Exception("Failed to move uploaded file");
        }

        chmod($destFile, 0644);

        // Create shell script to execute `onj`
        $scriptPath = "$baseDir/temp_script_" . uniqid() . ".sh";
        $shellScript = "#!/bin/bash\n";
        $shellScript .= "export PATH=/usr/local/go/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin\n";
        $shellScript .= "./onj " . escapeshellarg($destFile) . " " . $problemid . " 2>&1\n";
        
        file_put_contents($scriptPath, $shellScript);
        list($output, $returncode) = executeCommand($scriptPath);
        unlink($scriptPath);

        // Process output
        $judgeOutput = null;
        $executionTime = 0.0;
        if (!empty($output)) {
            $lastOutput = end($output);
            $decodedOutput = json_decode($lastOutput, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $judgeOutput = $decodedOutput;
                if (isset($judgeOutput['execution_time'])) {
                    $executionTime = number_format((float) $judgeOutput['execution_time'], 3, '.', '');
                }
            }
        }

        $currentTime = time();
        $stmt = $db->prepare("INSERT INTO submissions (userid, problemid, status, time, execution_time, filename) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiids", $userid, $problemid, $returncode, $currentTime, $executionTime, $filename);
        $stmt->execute();

        $response = [
            'verdict' => $returncode,
            'problemid' => $problemid,
            'execution_time' => $executionTime,
            'time' => $currentTime,
            'readable_time' => date('Y-m-d H:i:s', $currentTime),
            'output' => $judgeOutput
        ];

        echo json_encode($response);
        $db->close();
        exit;
    }
} catch (Exception $e) {
    error_log("Submission Error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'verdict' => VERDICT['RTE'],
        'problemid' => isset($problemid) ? $problemid : -1,
        'message' => 'System error occurred: ' . $e->getMessage()
    ]);
    exit;
}
?>