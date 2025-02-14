<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['isloggedin'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$userid = $_SESSION['userid'];

include('settings.php');

// Configure error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_log("Starting submission processing");

// Constants for verdicts
const VERDICT = [
    'CORRECT' => 0,
    'COMPILE_ERROR' => 1,
    'WRONG' => 2,
    'TIME_EXCEEDED' => 3,
    'ILLEGAL_FILE' => 4,
    'RTE' => 5
];

// Ensure a valid problem ID exists
$problemid = -1;
foreach ($_FILES as $key => $value) {
    if (is_numeric($key)) {
        $problemid = (int) filter_var($key, FILTER_SANITIZE_NUMBER_INT);
        break;
    }
}

if ($problemid === -1) {
    exit(json_encode(['verdict' => VERDICT['RTE'], 'message' => 'Invalid problem ID']));
}

try {
    // Establish database connection
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);

    // Prevent duplicate submissions (limit 5s interval)
    $timeThreshold = time() - 5;
    $stmt = $db->prepare("SELECT id FROM submissions WHERE userid = ? AND problemid = ? AND time > ?");
    $stmt->bind_param("iii", $userid, $problemid, $timeThreshold);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        exit(json_encode(['verdict' => VERDICT['RTE'], 'message' => 'Wait 5 seconds between submissions']));
    }

    // Define directories
    $baseDir = __DIR__;
    $userDir = "$baseDir/code/$username/";
    $uploadDir = "$userDir$problemid/";

    // Ensure required directories exist
    foreach ([$baseDir . "/code", $userDir, $uploadDir] as $dir) {
        if (!file_exists($dir) && !mkdir($dir, 0755, true)) {
            error_log("Failed to create directory: $dir");
            throw new Exception("Failed to create directory: $dir");
        }
    }

    // Process uploaded file
    if (isset($_FILES[$problemid]) && $_FILES[$problemid]["error"] === UPLOAD_ERR_OK) {
        $timestamp = date('Y-m-d_H-i-s');
        $originalName = basename($_FILES[$problemid]["name"]);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($ext, ['c', 'cpp', 'py', 'java', 'go'])) {
            throw new Exception("Invalid file type. Allowed: .c, .cpp, .py, .java, .go");
        }

        $filename = ($ext === 'java') ? "Main.java" : pathinfo($originalName, PATHINFO_FILENAME) . "_$timestamp.$ext";
        $destFile = "$uploadDir$filename";

        if (!move_uploaded_file($_FILES[$problemid]["tmp_name"], $destFile)) {
            throw new Exception("File upload failed.");
        }
        chmod($destFile, 0644);

        // Prepare and execute shell script
        $scriptPath = "$baseDir/temp_script_" . uniqid() . ".sh";
        $shellScript = "#!/bin/bash\n";
        $shellScript .= "export PATH=/usr/local/go/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin\n";
        $shellScript .= "./onj " . escapeshellarg($destFile) . " " . $problemid . " 2>&1\n";
        file_put_contents($scriptPath, $shellScript);
        chmod($scriptPath, 0755);

        // Execute script and process output
        $cmd = "bash " . escapeshellarg($scriptPath);
        $output = [];
        $returncode = -1;
        exec($cmd, $output, $returncode);
        unlink($scriptPath);

        $executionTime = 0.0;
        $judgeOutput = null;

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

        // Store submission record
        $currentTime = time();
        $stmt = $db->prepare("INSERT INTO submissions (userid, problemid, status, time, execution_time, filename) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiids", $userid, $problemid, $returncode, $currentTime, $executionTime, $filename);
        $stmt->execute();

        // Update score if correct
        $scoreUpdated = false;
        if ($returncode === VERDICT['CORRECT']) {
            $db->begin_transaction();
            $stmt = $db->prepare("SELECT id FROM submissions WHERE userid = ? AND problemid = ? AND status = 0");
            $stmt->bind_param("ii", $userid, $problemid);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                $stmt = $db->prepare("UPDATE users SET score = (SELECT COUNT(DISTINCT problemid) FROM submissions WHERE userid = ? AND status = 0) WHERE id = ?");
                $stmt->bind_param("ii", $userid, $userid);
                $stmt->execute();
                $scoreUpdated = true;
            }
            $db->commit();
        }

        // Send response
        echo json_encode([
            'verdict' => $returncode,
            'problemid' => $problemid,
            'execution_time' => $executionTime,
            'time' => $currentTime,
            'readable_time' => date('Y-m-d H:i:s', $currentTime),
            'score_updated' => $scoreUpdated,
            'output' => $judgeOutput
        ]);
    }
} catch (Exception $e) {
    error_log("Submission Error: " . $e->getMessage());
    echo json_encode([
        'verdict' => VERDICT['RTE'],
        'problemid' => $problemid,
        'message' => 'System error: ' . $e->getMessage()
    ]);
}
?>