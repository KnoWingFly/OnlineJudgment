<?php
session_start();
if (!isset($_SESSION['isloggedin']) || $_SESSION['admin'] != true) {
    header("Location: ../login.php");
    exit(0);
}

// Enable error reporting
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

include('../settings.php');

// Test directory permissions
$problemsDir = "../problems/";
if (!file_exists($problemsDir)) {
    if (!mkdir($problemsDir, 0777, true)) {
        die("Cannot create problems directory. Check permissions.");
    }
}

if (!is_writable($problemsDir)) {
    die("Cannot write to problems directory. Check permissions.");
}

// Debug logging function
function debug_log($message)
{
    error_log("[Debug] " . $message);
}

// Function to safely handle file uploads
function saveUploadedFile($file, $problemId, $filename)
{
    debug_log("Starting file upload for problem $problemId: $filename");

    $targetDir = "../problems/" . $problemId . "/";

    // Create directory if it doesn't exist with proper permissions
    if (!file_exists($targetDir)) {
        debug_log("Creating directory: $targetDir");
        if (!mkdir($targetDir, 0777, true)) {
            debug_log("Failed to create directory");
            return false;
        }
        chmod($targetDir, 0777); // Ensure directory is writable
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        debug_log("Upload error: " . $file['error']);
        return false;
    }

    $targetPath = $targetDir . $filename;
    debug_log("Moving file to: $targetPath");

    if (!move_uploaded_file($file["tmp_name"], $targetPath)) {
        debug_log("Failed to move uploaded file");
        return false;
    }

    chmod($targetPath, 0666);
    debug_log("File upload successful");
    return true;
}

// Function to compile and test problem
function compileAndTest($problemId)
{
    $dir = realpath("../problems/" . $problemId . "/");
    $output = array();
    $returnVar = 0;

    debug_log("Starting compilation and testing for problem $problemId");
    debug_log("Working directory: $dir");

    // Convert Windows path to WSL path
    $wslDir = trim(shell_exec('wsl wslpath "' . $dir . '"'));
    debug_log("WSL directory path: $wslDir");

    // Ensure all required files exist
    if (!file_exists($dir . "/generator.cpp") || !file_exists($dir . "/solution.cpp")) {
        debug_log("Required files missing");
        return false;
    }

    // Clear previous files and create new ones
    $filesToHandle = ['in', 'out', 'generator', 'solution'];
    foreach ($filesToHandle as $file) {
        if (file_exists($dir . "/$file")) {
            unlink($dir . "/$file");
        }
        if ($file === 'in' || $file === 'out') {
            touch($dir . "/$file");
            chmod($dir . "/$file", 0666);
        }
    }

    // Compile generator
    $compileGeneratorCmd = 'wsl -e bash -c "cd \'' . $wslDir . '\' && g++ -std=c++11 generator.cpp -o generator"';
    debug_log("Executing generator compilation: $compileGeneratorCmd");
    exec($compileGeneratorCmd . " 2>&1", $output, $returnVar);
    if ($returnVar !== 0) {
        debug_log("Generator compilation failed: " . implode("\n", $output));
        return false;
    }

    // Run generator
    $runGeneratorCmd = 'wsl -e bash -c "cd \'' . $wslDir . '\' && chmod +x generator && ./generator > in"';
    debug_log("Executing generator: $runGeneratorCmd");
    exec($runGeneratorCmd . " 2>&1", $output, $returnVar);
    if ($returnVar !== 0) {
        debug_log("Generator execution failed: " . implode("\n", $output));
        return false;
    }

    // Verify generator created input
    if (!file_exists($dir . "/in") || filesize($dir . "/in") === 0) {
        debug_log("Input file is missing or empty after generator execution");
        return false;
    }

    // Compile solution with C++11 support
    $compileSolutionCmd = 'wsl -e bash -c "cd \'' . $wslDir . '\' && g++ -std=c++11 solution.cpp -o solution"';
    debug_log("Executing solution compilation: $compileSolutionCmd");
    exec($compileSolutionCmd . " 2>&1", $output, $returnVar);
    if ($returnVar !== 0) {
        debug_log("Solution compilation failed: " . implode("\n", $output));
        return false;
    }

    // Set proper permissions
    exec('wsl -e bash -c "cd \'' . $wslDir . '\' && chmod 666 in out && chmod +x solution"');

    // Run solution in WSL with explicit file handling
    $runSolutionCmd = 'wsl -e bash -c "cd \'' . $wslDir . '\' && ./solution"';
    debug_log("Executing solution: $runSolutionCmd");
    exec($runSolutionCmd . " 2>&1", $output, $returnVar);

    // Log solution execution output for debugging
    debug_log("Solution execution output: " . implode("\n", $output));

    if ($returnVar !== 0) {
        debug_log("Solution execution failed with return code: $returnVar");
        return false;
    }

    // Verify output file exists and has content
    if (!file_exists($dir . "/out")) {
        debug_log("Output file does not exist after solution execution");
        return false;
    }

    $outSize = filesize($dir . "/out");
    if ($outSize === 0) {
        debug_log("Output file is empty after solution execution");
        return false;
    }

    debug_log("Compilation and testing completed successfully");
    debug_log("Output file size: $outSize bytes");
    return true;
}

// Function to generate problem statement HTML
function generateStatement($title, $timeLimit, $inputFormat, $outputFormat, $sampleInput, $sampleOutput, $description, $constraints)
{
    return <<<EOT
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
    <meta charset="utf-8">
</head>
<body bgcolor="white">
    <font color="#0000FF"><h1>{$title}</h1></font>
    <h3>Time Limit: {$timeLimit}s</h3>

    <p align="justify">{$description}</p>

    <font color="#0000FF"><h2>Input Format</h2></font>
    <p align="justify">{$inputFormat}</p>

    <font color="#0000FF"><h2>Output Format</h2></font>
    <p align="justify">{$outputFormat}</p>

    <p align="justify">
    Constraints:<br/>
    {$constraints}
    </p>

    <font color="#0000FF"><h2>Sample Input</h2></font>
    <pre>{$sampleInput}</pre>

    <font color="#0000FF"><h2>Sample Output</h2></font>
    <pre>{$sampleOutput}</pre>

    <hr>
    <font size="4">
    <center>Programming Contest</center>
    </font>
</body>
</html>
EOT;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    debug_log("Form submitted");
    try {
        if (!isset($_POST['action']) || $_POST['action'] != 'add') {
            throw new Exception("Invalid action");
        }

        // Validate required fields
        $required_fields = [
            'title',
            'timeLimit',
            'memoryLimit',
            'points',
            'description',
            'inputFormat',
            'outputFormat',
            'constraints',
            'sampleInput',
            'sampleOutput'
        ];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Validate file uploads
        if (!isset($_FILES['generator']) || !isset($_FILES['solution'])) {
            throw new Exception("Missing uploaded files");
        }

        debug_log("Connecting to database");
        $cn = mysqli_connect('localhost', $DBUSER, $DBPASS, $DBNAME);
        if (!$cn) {
            throw new Exception("Database connection failed: " . mysqli_connect_error());
        }

        mysqli_begin_transaction($cn);

        // Prepare data
        $title = mysqli_real_escape_string($cn, $_POST['title']);
        $timeLimit = floatval($_POST['timeLimit']);
        $memoryLimit = intval($_POST['memoryLimit']);
        $points = intval($_POST['points']);

        debug_log("Generating statement HTML");
        $statementHtml = generateStatement(
            $title,
            $timeLimit,
            mysqli_real_escape_string($cn, $_POST['inputFormat']),
            mysqli_real_escape_string($cn, $_POST['outputFormat']),
            mysqli_real_escape_string($cn, $_POST['sampleInput']),
            mysqli_real_escape_string($cn, $_POST['sampleOutput']),
            mysqli_real_escape_string($cn, $_POST['description']),
            mysqli_real_escape_string($cn, $_POST['constraints'])
        );

        // Modified query to include points
        debug_log("Inserting problem into database");
        $query = "INSERT INTO problems (title, time_limit, memory_limit, points, created_at) 
             VALUES (?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($cn, $query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($cn));
        }

        mysqli_stmt_bind_param($stmt, "sdii", $title, $timeLimit, $memoryLimit, $points);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Database insert failed: " . mysqli_error($cn));
        }

        $problemId = mysqli_insert_id($cn);
        if (!$problemId) {
            throw new Exception("Failed to get inserted problem ID");
        }
        debug_log("Problem ID: $problemId");

        // Save files
        $problemDir = "../problems/" . $problemId . "/";

        // Create problem directory if it doesn't exist
        if (!file_exists($problemDir)) {
            debug_log("Creating problem directory: $problemDir");
            if (!mkdir($problemDir, 0777, true)) {
                throw new Exception("Failed to create problem directory");
            }
            chmod($problemDir, 0777); // Ensure directory is writable
        }

        // Save statement HTML with error checking
        debug_log("Saving statement HTML");
        if (file_put_contents($problemDir . "statement.html", $statementHtml) === false) {
            throw new Exception("Failed to save statement HTML: " . error_get_last()['message']);
        }
        chmod($problemDir . "statement.html", 0666);

        debug_log("Saving statement HTML");
        if (!file_put_contents($problemDir . "statement.html", $statementHtml)) {
            throw new Exception("Failed to save statement HTML");
        }

        debug_log("Saving uploaded files");
        if (!saveUploadedFile($_FILES["generator"], $problemId, "generator.cpp")) {
            throw new Exception("Failed to save generator file");
        }

        if (!saveUploadedFile($_FILES["solution"], $problemId, "solution.cpp")) {
            throw new Exception("Failed to save solution file");
        }

        debug_log("Running compile and test");
        if (!compileAndTest($problemId)) {
            throw new Exception("Compilation or testing failed");
        }

        mysqli_commit($cn);
        $message = "Problem added successfully";
        debug_log("Problem added successfully");

    } catch (Exception $e) {
        debug_log("Error occurred: " . $e->getMessage());
        if (isset($cn)) {
            mysqli_rollback($cn);
        }
        $message = "Error: " . htmlspecialchars($e->getMessage());
    } finally {
        if (isset($cn)) {
            mysqli_close($cn);
        }
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta name="Keywords" content="programming, contest, coding, judge" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="Distribution" content="Global" />
    <meta name="Robots" content="index,follow" />

    <link rel="stylesheet" href="../images/Envision.css" type="text/css" />
    <link rel="stylesheet" href="../images/Tabs.css" type="text/css" />
    <title>Programming Contest - Manage Problems</title>

    <script type="text/javascript" src="../jquery-1.3.1.js"></script>
    <script type="text/javascript" src="../jquery.timers-1.1.2.js"></script>
    <?php include('../timer.php'); ?>

    <style type="text/css">
        .admin-section {
            background: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }

        .admin-section h2 {
            color: #88ac0b;
            font-size: 18px;
            margin-top: 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #e0e0e0;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group input[type="file"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #f9f9f9;
        }

        .form-group textarea {
            height: 150px;
            font-family: monospace;
            resize: vertical;
        }

        .form-group input[type="submit"] {
            padding: 8px 20px;
            background: #88ac0b;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-group input[type="submit"]:hover {
            background: #7a9d0a;
        }

        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            background: #f0f9eb;
            border: 1px solid #88ac0b;
            color: #67a70c;
        }
    </style>
</head>

<body class="menu7">
    <div id="wrap">
        <?php include('../header.php'); ?>

        <div id="menu">
            <ul>
                <?php if (!isset($_SESSION['isloggedin']))
                    print '<li id="menu1"><a href="../login.php">Login</a></li>'; ?>
                <?php if (isset($_SESSION['isloggedin']))
                    print '<li id="menu2"><a href="../index.php">Problems</a></li>'; ?>
                <?php if (isset($_SESSION['isloggedin']))
                    print '<li id="menu3"><a href="../submissions.php">Submissions</a></li>'; ?>
                <?php if (isset($_SESSION['isloggedin']))
                    print '<li id="menu4"><a href="../scoreboard.php">Scoreboard</a></li>'; ?>
                <li id="menu5"><a href="../faq.php">FAQ</a></li>
                <?php if (isset($_SESSION['isloggedin']))
                    print '<li id="menu6"><a href="../chat.php">Chat</a></li>'; ?>
                <?php if (isset($_SESSION['admin']))
                    print '<li id="menu7"><a href="admin.php">Admin</a></li>'; ?>
                <?php if (isset($_SESSION['isloggedin']))
                    print '<li id="menu8"><a href="../personal.php">Personal</a></li>'; ?>
                <?php if (isset($_SESSION['isloggedin']))
                    print '<li id="menu9"><a href="../logout.php">Logout</a></li>'; ?>
            </ul>
        </div>

        <div id="content-wrap">
            <div id="main">
                <?php if (isset($message)): ?>
                    <div class="message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <div class="admin-section">
                    <h2>Add New Problem</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add" />

                        <div class="form-group">
                            <label>Problem Title:</label>
                            <input type="text" name="title" required />
                        </div>

                        <div class="form-group">
                            <label>Time Limit (seconds):</label>
                            <input type="number" name="timeLimit" step="0.1" required />
                        </div>

                        <div class="form-group">
                            <label>Memory Limit (MB):</label>
                            <input type="number" name="memoryLimit" required />
                        </div>

                        <div class="form-group">
                            <label>Points:</label>
                            <input type="number" name="points" required />
                        </div>

                        <div class="form-group">
                            <label>Problem Description:</label>
                            <textarea name="description" required placeholder="Describe the problem here..."></textarea>
                        </div>

                        <div class="form-group">
                            <label>Input Format:</label>
                            <textarea name="inputFormat" required placeholder="Describe the input format..."></textarea>
                        </div>

                        <div class="form-group">
                            <label>Output Format:</label>
                            <textarea name="outputFormat" required
                                placeholder="Describe the output format..."></textarea>
                        </div>

                        <div class="form-group">
                            <label>Constraints:</label>
                            <textarea name="constraints" required placeholder="List all constraints..."></textarea>
                        </div>

                        <div class="form-group">
                            <label>Sample Input:</label>
                            <textarea name="sampleInput" required></textarea>
                        </div>

                        <div class="form-group">
                            <label>Sample Output:</label>
                            <textarea name="sampleOutput" required></textarea>
                        </div>

                        <div class="form-group">
                            <label>Generator (C++):</label>
                            <input type="file" name="generator" accept=".cpp" required />
                        </div>

                        <div class="form-group">
                            <label>Solution (C++):</label>
                            <input type="file" name="solution" accept=".cpp" required />
                        </div>

                        <div class="form-group">
                            <input type="submit" value="Add Problem" />
                        </div>
                    </form>
                </div>
            </div>

            <div id="sidebar">
                <h3>Admin Menu</h3>
                <ul class="sidemenu">
                    <li><a href="problems.php">Manage Problems</a></li>
                    <li><a href="users.php">Manage Users</a></li>
                    <li><a href="setting.php">Contest Settings</a></li>
                </ul>

                <?php include('../sidebar.php'); ?>
            </div>
        </div>

        <div id="footer">
            <?php include('../footer.php'); ?>
        </div>
    </div>
</body>

</html>