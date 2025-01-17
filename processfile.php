<?php
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

function convertToWSLPath($windowsPath)
{
    $path = preg_replace('/^[A-Z]:/i', '', $windowsPath);
    $path = str_replace('\\', '/', $path);
    return "/mnt/c" . $path;
}

function preventDuplicateSubmission($db, $userid, $problemid)
{
    $currentTime = time();
    $timeThreshold = $currentTime - 5;

    $stmt = $db->prepare("SELECT id FROM submissions WHERE userid = ? AND problemid = ? AND time > ?");
    $stmt->bind_param("iii", $userid, $problemid, $timeThreshold);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function updateScoreAndRank($db, $userid, $problemid)
{
    try {
        // Start transaction
        $db->begin_transaction();

        // Check if problem was already solved
        $stmt = $db->prepare("SELECT id FROM submissions WHERE userid = ? AND problemid = ? AND status = 0");
        $stmt->bind_param("ii", $userid, $problemid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $db->commit();
            return false; // Already solved
        }

        // Calculate total solved problems and update score
        $updateScore = "
            UPDATE users u 
            SET u.score = (
                SELECT COUNT(DISTINCT s.problemid) * 10 
                FROM submissions s 
                WHERE s.userid = ? AND s.status = 0
            ) + 10
            WHERE u.id = ?";

        $stmt = $db->prepare($updateScore);
        $stmt->bind_param("ii", $userid, $userid);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update score: " . $stmt->error);
        }

        // Reset rank counter
        if (!$db->query("SET @rank = 0")) {
            throw new Exception("Failed to initialize rank variable");
        }

        // Update all ranks
        $updateRanks = "
            UPDATE users u
            JOIN (
                SELECT id,
                    @rank := @rank + 1 as new_rank
                FROM users
                ORDER BY score DESC, id ASC
            ) r ON u.id = r.id
            SET u.ranks = r.new_rank";

        if (!$db->query($updateRanks)) {
            throw new Exception("Failed to update ranks");
        }

        $db->commit();
        return true;

    } catch (Exception $e) {
        $db->rollback();
        error_log("Score update error: " . $e->getMessage());
        throw $e;
    }
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

    // Connect using mysqli
    $db = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);
    if ($db->connect_error) {
        throw new Exception("Database connection failed: " . $db->connect_error);
    }

    if (preventDuplicateSubmission($db, $userid, $problemid)) {
        echo json_encode([
            'verdict' => 5,
            'problemid' => $problemid,
            'message' => 'Please wait 5 seconds between submissions'
        ]);
        exit;
    }

    // Get current directory and paths
    $baseDir = dirname(__FILE__);
    $wslBaseDir = convertToWSLPath($baseDir);
    $userDir = $wslBaseDir . "/code/$username/";
    $uploadDir = $userDir . $problemid . "/";

    // Create directories if they don't exist
    foreach ([$wslBaseDir . "/code", $userDir, $uploadDir] as $dir) {
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0755, true)) {
                error_log("Failed to create directory: $dir");
                throw new Exception("Failed to create directory: $dir");
            }
        }
    }

    // Validate and process uploaded file
    if (isset($_FILES[$problemid]) && $_FILES[$problemid]["error"] === UPLOAD_ERR_OK) {
        // Create timestamp for unique filename
        $timestamp = date('Y-m-d_H-i-s');
        $originalName = basename($_FILES[$problemid]["name"]);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Validate the file extension
        if (!in_array($ext, ['c', 'cpp', 'py', 'java', 'go'])) {
            throw new Exception("Invalid file type. Only .c, .cpp, .py, .java, and .go files are allowed.");
        }

        // Special handling for Java files
        if ($ext === 'java') {
            // Extract the class name from the file content
            $content = file_get_contents($_FILES[$problemid]["tmp_name"]);
            if (preg_match("/\bpublic\s+class\s+(\w+)\b/", $content, $matches)) {
                $className = $matches[1];
                $filename = $className . ".java";

                $archiveDir = $uploadDir . "archive/";
                if (!file_exists($archiveDir)) {
                    mkdir($archiveDir, 0755, true);
                }
                $archiveFile = $archiveDir . $className . "_" . $timestamp . ".java";

                if (!copy($_FILES[$problemid]["tmp_name"], $archiveFile)) {
                    error_log("Failed to save archive copy: $archiveFile");
                }
            } else {
                throw new Exception("Could not find valid public class declaration in Java file");
            }
        } else {
            $filename = pathinfo($originalName, PATHINFO_FILENAME) . "_" . $timestamp . "." . $ext;
        }

        $destFile = $uploadDir . $filename;
        $windowsDestFile = $baseDir . "/code/$username/$problemid/" . $filename;

        // Create the Windows directory structure if it doesn't exist
        $windowsUploadDir = $baseDir . "/code/$username/$problemid/";
        if (!file_exists($windowsUploadDir)) {
            mkdir($windowsUploadDir, 0755, true);
        }

        // Move uploaded file to destination
        if (!move_uploaded_file($_FILES[$problemid]["tmp_name"], $windowsDestFile)) {
            throw new Exception("Failed to move uploaded file");
        }

        // Set file permissions
        chmod($windowsDestFile, 0644);

        // Create a shell script to set up the environment and run the command
        $shellScript = "#!/bin/bash\n";
        $shellScript .= "export PATH=/usr/local/go/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin\n";
        $shellScript .= "./onj " . escapeshellarg($destFile) . " " . $problemid . " 2>&1\n";

        // Save the shell script
        $scriptPath = $baseDir . "/temp_script_" . uniqid() . ".sh";
        file_put_contents($scriptPath, $shellScript);
        chmod($scriptPath, 0755);

        // Convert script path to WSL format
        $wslScriptPath = convertToWSLPath($scriptPath);

        // Execute the shell script through WSL
        $cmd = "wsl bash " . escapeshellarg($wslScriptPath);

        // Execute command
        $output = [];
        $returncode = -1;
        exec($cmd, $output, $returncode);

        // Clean up the temporary script
        unlink($scriptPath);

        // Get execution time
        $executionTime = 0.0;
        if (!empty($output)) {
            foreach ($output as $line) {
                if (is_numeric($line)) {
                    $executionTime = number_format((float) $line, 3, '.', '');
                    break;
                }
            }
        }

        // Check if correct and update score
        $scoreUpdated = false;
        $newScore = 0;
        $newRank = 0;

        if ($returncode === 0) {
            $timeLimit = 1.0;  // Default 1 second time limit
            if ($executionTime <= $timeLimit) {
                $scoreUpdated = updateScoreAndRank($db, $userid, $problemid);

                // Get updated score and rank
                $stmt = $db->prepare("SELECT score, ranks FROM users WHERE id = ?");
                $stmt->bind_param("i", $userid);
                $stmt->execute();
                $result = $stmt->get_result();
                $userInfo = $result->fetch_assoc();
                $newScore = $userInfo['score'];
                $newRank = $userInfo['ranks'];
            }
        }

        // Verdict messages
        $verdictMessages = [
            0 => "Accepted",
            1 => "Compile Error",
            2 => "Wrong Answer",
            3 => "Time Limit Exceeded",
            4 => "Invalid File",
            5 => "Runtime Error",
            6 => "System Error"
        ];

        $currentTime = time();

        // Insert submission record
        $stmt = $db->prepare("INSERT INTO submissions (userid, problemid, status, time, execution_time, filename) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiids", $userid, $problemid, $returncode, $currentTime, $executionTime, $filename);

        if (!$stmt->execute()) {
            throw new Exception("Failed to insert submission: " . $stmt->error);
        }

        // Send response
        echo json_encode([
            'verdict' => $returncode,
            'verdict_message' => $verdictMessages[$returncode] ?? "Unknown Error",
            'problemid' => $problemid,
            'execution_time' => $executionTime,
            'time' => $currentTime,
            'readable_time' => date('Y-m-d H:i:s', $currentTime),
            'score_updated' => $scoreUpdated,
            'new_score' => $newScore,
            'new_rank' => $newRank,
            'output' => implode("\n", $output)
        ]);

        $db->close();
    } else {
        throw new Exception("No file uploaded or file upload error");
    }
} catch (Exception $e) {
    error_log("Submission Error: " . $e->getMessage());
    echo json_encode([
        'verdict' => 6,
        'verdict_message' => "System Error",
        'problemid' => isset($problemid) ? $problemid : -1,
        'message' => 'System error occurred: ' . $e->getMessage()
    ]);
}
?>