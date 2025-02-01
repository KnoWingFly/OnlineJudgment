<?php
session_start();
if (!isset($_SESSION['isloggedin']) || $_SESSION['admin'] != true) {
    header("Location: ../login.php");
    exit(0);
}

ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

include('../settings.php');

$problemsDir = "../problems/";
if (!file_exists($problemsDir)) {
    if (!mkdir($problemsDir, 0777, true)) {
        die("Cannot create problems directory. Check permissions.");
    }
}

if (!is_writable($problemsDir)) {
    die("Cannot write to problems directory. Check permissions.");
}

function debug_log($message)
{
    error_log("[Debug] " . $message);
}

function saveUploadedFile($file, $problemId, $filename, $subdir = '')
{
    $targetDir = "../problems/" . $problemId . "/" . ($subdir ? $subdir . "/" : "");

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
        chmod($targetDir, 0777);
    }

    if ($file['error'] == UPLOAD_ERR_NO_FILE)
        return true;
    if ($file['error'] !== UPLOAD_ERR_OK)
        return false;

    $targetPath = $targetDir . $filename;
    if (!move_uploaded_file($file["tmp_name"], $targetPath))
        return false;

    chmod($targetPath, 0666);
    return true;
}

function generateStatement($title, $timeLimit, $inputFormat, $outputFormat, $sampleInput, $sampleOutput, $description, $constraints, $imagePath = null)
{
    $imageSection = '';

    if ($imagePath) {
        $fullImagePath = "../problems/" . $imagePath;
        if (file_exists($fullImagePath)) {
            $imageSection = <<<EOT
<div class="mb-8">
    <div class="text-blue-400">
        <h2 class="text-xl font-semibold mb-4 pb-2 border-b border-blue-400/30">Problem Illustration</h2>
    </div>
    <div class="mt-4">
        <img src="../problems/{$imagePath}" alt="Problem Illustration" class="rounded-lg border border-[#1A1A1A] mx-auto">
    </div>
</div>
EOT;
        }
    }

    // Process regular text fields
    $processText = function ($text) {
        // First escape HTML
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        // Replace literal "\r\n" with <br>
        $text = str_replace("\\r\\n", "<br>", $text);
        // Replace actual line breaks with <br>
        $text = str_replace(["\r\n", "\n", "\r"], "<br>", $text);
        return $text;
    };

    // Process sample input/output
    $processSample = function ($text) {
        // Escape HTML
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        // Replace literal "\r\n" with newlines
        $text = str_replace("\\r\\n", "\n", $text);
        // Replace actual line breaks with newlines
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        return $text;
    };

    // Apply processing to all fields
    $title = htmlspecialchars(trim($title), ENT_QUOTES, 'UTF-8');
    $timeLimit = htmlspecialchars(trim($timeLimit), ENT_QUOTES, 'UTF-8');
    $description = $processText($description);
    $inputFormat = $processText($inputFormat);
    $outputFormat = $processText($outputFormat);
    $constraints = $processText($constraints);
    $sampleInput = $processSample($sampleInput);
    $sampleOutput = $processSample($sampleOutput);

    return <<<EOT
<div class="problem-content space-y-8">
    <div class="text-blue-400">
        <h1 class="text-3xl font-bold mb-6">{$title}</h1>
    </div>
    
    <div class="bg-emerald-900/20 text-emerald-400 px-4 py-2 rounded-xl code-font text-sm inline-flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Time Limit: {$timeLimit}s
    </div>

    {$imageSection}

    <div class="space-y-6">
        <div class="text-blue-400">
            <h2 class="text-xl font-semibold mb-4 pb-2 border-b border-blue-400/30">Description</h2>
        </div>
        <p class="text-gray-300 leading-relaxed">{$description}</p>
    </div>

    <div class="space-y-6">
        <div class="text-blue-400">
            <h2 class="text-xl font-semibold mb-4 pb-2 border-b border-blue-400/30">Input Format</h2>
        </div>
        <p class="text-gray-300 leading-relaxed">{$inputFormat}</p>
    </div>

    <div class="space-y-6">
        <div class="text-blue-400">
            <h2 class="text-xl font-semibold mb-4 pb-2 border-b border-blue-400/30">Output Format</h2>
        </div>
        <p class="text-gray-300 leading-relaxed">{$outputFormat}</p>
    </div>

    <div class="space-y-6">
        <div class="text-blue-400">
            <h2 class="text-xl font-semibold mb-4 pb-2 border-b border-blue-400/30">Constraints</h2>
        </div>
        <p class="text-gray-300 leading-relaxed">{$constraints}</p>
    </div>

    <div class="space-y-6">
        <div class="text-blue-400">
            <h2 class="text-xl font-semibold mb-4 pb-2 border-b border-blue-400/30">Sample Input</h2>
        </div>
        <div class="relative">
            <div class="bg-[#0A0A0A] text-gray-300 p-4 rounded-lg border border-[#1A1A1A] code-font text-sm">
                <pre class="max-h-64 overflow-y-auto overflow-x-auto whitespace-pre">{$sampleInput}</pre>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="text-blue-400">
            <h2 class="text-xl font-semibold mb-4 pb-2 border-b border-blue-400/30">Sample Output</h2>
        </div>
        <div class="relative">
            <div class="bg-[#0A0A0A] text-gray-300 p-4 rounded-lg border border-[#1A1A1A] code-font text-sm">
                <pre class="max-h-64 overflow-y-auto overflow-x-auto whitespace-pre">{$sampleOutput}</pre>
            </div>
        </div>
    </div>
</div>
EOT;
}

function compileAndTest($problemId)
{
    $dir = realpath("../problems/" . $problemId . "/");
    $output = array();
    $returnVar = 0;

    debug_log("Starting compilation and testing for problem $problemId");
    debug_log("Working directory: $dir");

    $wslDir = trim(shell_exec('wsl wslpath "' . $dir . '"'));
    debug_log("WSL directory path: $wslDir");

    if (!file_exists($dir . "/generator.cpp") || !file_exists($dir . "/solution.cpp")) {
        debug_log("Required files missing");
        return false;
    }

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

    $compileGeneratorCmd = 'wsl -e bash -c "cd \'' . $wslDir . '\' && g++ -std=c++11 generator.cpp -o generator"';
    debug_log("Executing generator compilation: $compileGeneratorCmd");
    exec($compileGeneratorCmd . " 2>&1", $output, $returnVar);
    if ($returnVar !== 0) {
        debug_log("Generator compilation failed: " . implode("\n", $output));
        return false;
    }

    $runGeneratorCmd = 'wsl -e bash -c "cd \'' . $wslDir . '\' && chmod +x generator && ./generator > in"';
    debug_log("Executing generator: $runGeneratorCmd");
    exec($runGeneratorCmd . " 2>&1", $output, $returnVar);
    if ($returnVar !== 0) {
        debug_log("Generator execution failed: " . implode("\n", $output));
        return false;
    }

    if (!file_exists($dir . "/in") || filesize($dir . "/in") === 0) {
        debug_log("Input file is missing or empty after generator execution");
        return false;
    }

    $compileSolutionCmd = 'wsl -e bash -c "cd \'' . $wslDir . '\' && g++ -std=c++11 solution.cpp -o solution"';
    debug_log("Executing solution compilation: $compileSolutionCmd");
    exec($compileSolutionCmd . " 2>&1", $output, $returnVar);
    if ($returnVar !== 0) {
        debug_log("Solution compilation failed: " . implode("\n", $output));
        return false;
    }

    exec('wsl -e bash -c "cd \'' . $wslDir . '\' && chmod 666 in out && chmod +x solution"');

    $runSolutionCmd = 'wsl -e bash -c "cd \'' . $wslDir . '\' && ./solution"';
    debug_log("Executing solution: $runSolutionCmd");
    exec($runSolutionCmd . " 2>&1", $output, $returnVar);

    debug_log("Solution execution output: " . implode("\n", $output));

    if ($returnVar !== 0) {
        debug_log("Solution execution failed with return code: $returnVar");
        return false;
    }

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    debug_log("Form submitted");
    try {
        $cn = mysqli_connect('localhost', $DBUSER, $DBPASS, $DBNAME);
        if (!$cn)
            throw new Exception("Database connection failed");

        // Start transaction
        mysqli_begin_transaction($cn);

        // Get next problem ID first
        $result = mysqli_query($cn, "SELECT MAX(id) AS max_id FROM problems");
        $row = mysqli_fetch_assoc($result);
        $newProblemId = isset($row['max_id']) ? $row['max_id'] + 1 : 1;

        // Insert with manual ID
        $title = mysqli_real_escape_string($cn, $_POST['title']);
        $timeLimit = floatval($_POST['timeLimit']);
        $query = "INSERT INTO problems (id, title, time_limit, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($cn, $query);
        mysqli_stmt_bind_param($stmt, "isd", $newProblemId, $title, $timeLimit);
        if (!mysqli_stmt_execute($stmt))
            throw new Exception("Insert failed: " . mysqli_error($cn));

        // Create problem directory
        $problemDir = "../problems/" . $newProblemId . "/";
        if (!mkdir($problemDir, 0777, true))
            throw new Exception("Failed to create problem directory");
        chmod($problemDir, 0777);

        // Handle image upload
        $imagePath = null;
        if ($_FILES['problemImage']['error'] == UPLOAD_ERR_OK) {
            $imageDir = $problemDir . "Images/";
            mkdir($imageDir, 0777, true);
            $imageName = 'problem_image.' . pathinfo($_FILES['problemImage']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['problemImage']['tmp_name'], $imageDir . $imageName);
            $imagePath = $newProblemId . "/Images/" . $imageName;
        }

        // Generate and save problem statement
        $statementHtml = generateStatement(
            $title,
            $timeLimit,
            $_POST['inputFormat'],
            $_POST['outputFormat'],
            $_POST['sampleInput'],
            $_POST['sampleOutput'],
            $_POST['description'],
            $_POST['constraints'],
            $imagePath
        );
        file_put_contents($problemDir . "statement.html", $statementHtml);
        chmod($problemDir . "statement.html", 0666);

        // Save code files
        if (!saveUploadedFile($_FILES["generator"], $newProblemId, "generator.cpp")) {
            throw new Exception("Failed to save generator");
        }
        if (!saveUploadedFile($_FILES["solution"], $newProblemId, "solution.cpp")) {
            throw new Exception("Failed to save solution");
        }

        // Compile and test
        if (!compileAndTest($newProblemId)) {
            throw new Exception("Compilation/testing failed");
        }

        mysqli_commit($cn);
        $message = "Problem added successfully! ID: " . $newProblemId;
    } catch (Exception $e) {
        mysqli_rollback($cn);
        $message = "Error: " . $e->getMessage();
    } finally {
        if (isset($cn))
            mysqli_close($cn);
    }
}
?>

<script src="https://cdn.tailwindcss.com">
    </scrip >

        <style>
            @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;700&family=Inter:wght@400;500;600;700&display=swap');

            body {
                font - family: 'Inter', sans-serif;
            background: #000000;
            color: #FFFFFF;
        }

            .code-font {
                font - family: 'IBM Plex Mono', monospace;
        }
        </style>

    <?php include('../timer.php'); ?>
    </head >

        <body class="bg-black text-gray-200">
            <!-- Header and Menu -->
            <?php include('../Layout/header.php'); ?>
            <?php include('../Layout/Menu.php'); ?>

            <div class="min-h-screen flex">
                <!-- Sidebar -->
                <div class="w-64 bg-[#1A1A1A] min-h-screen">
                    <div class="sticky top-0 p-6">
                        <h3 class="text-xl font-bold text-[#0736FF] mb-4">Admin Menu</h3>
                        <nav class="space-y-2">
                            <a href="problems.php"
                                class="flex items-center px-4 py-3 text-gray-300 hover:bg-[#0A0A0A] rounded-lg transition-colors bg-[#0A0A0A]">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Manage Problems
                            </a>
                            <a href="delete-problems.php"
                                class="flex items-center px-4 py-3 text-gray-300 hover:bg-[#0A0A0A] rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete Problems
                            </a>
                            <a href="users.php"
                                class="flex items-center px-4 py-3 text-gray-300 hover:bg-[#0A0A0A] rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                Manage Users
                            </a>
                            <a href="setting.php"
                                class="flex items-center px-4 py-3 text-gray-300 hover:bg-[#0A0A0A] rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Contest Settings
                            </a>
                        </nav>

                        <?php include('../sidebar.php'); ?>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 p-8">
                    <div class="max-w-4xl mx-auto">
                        <?php if (isset($message)): ?>
                            <div
                                class="mb-6 p-4 rounded-lg <?= strpos($message, 'Error') !== false ? 'bg-red-900/50 text-red-300' : 'bg-emerald-900/20 text-emerald-400' ?>">
                                <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>

                        <div class="bg-[#1A1A1A] p-8 rounded-xl border border-[#2A2A2A]">
                            <h1 class="text-3xl font-bold text-[#0736FF] mb-8">Add New Problem</h1>

                            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                                <input type="hidden" name="action" value="add" />

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-gray-400 mb-2">Problem Title</label>
                                        <input type="text" name="title" required
                                            class="w-full bg-[#0A0A0A] border border-[#2A2A2A] text-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#0736FF] focus:border-transparent outline-none">
                                    </div>

                                    <div>
                                        <label class="block text-gray-400 mb-2">Time Limit (seconds)</label>
                                        <input type="number" name="timeLimit" step="0.1" required
                                            class="w-full bg-[#0A0A0A] border border-[#2A2A2A] text-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#0736FF] focus:border-transparent outline-none">
                                    </div>

                                    <div>
                                        <label class="block text-gray-400 mb-2">Problem Description</label>
                                        <textarea name="description" required rows="4"
                                            class="w-full bg-[#0A0A0A] border border-[#2A2A2A] text-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#0736FF] focus:border-transparent outline-none code-font"></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-gray-400 mb-2">Input Format</label>
                                        <textarea name="inputFormat" required rows="4"
                                            class="w-full bg-[#0A0A0A] border border-[#2A2A2A] text-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#0736FF] focus:border-transparent outline-none code-font"></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-gray-400 mb-2">Output Format</label>
                                        <textarea name="outputFormat" required rows="4"
                                            class="w-full bg-[#0A0A0A] border border-[#2A2A2A] text-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#0736FF] focus:border-transparent outline-none code-font"></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-gray-400 mb-2">Constraints</label>
                                        <textarea name="constraints" required rows="4"
                                            class="w-full bg-[#0A0A0A] border border-[#2A2A2A] text-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#0736FF] focus:border-transparent outline-none code-font"></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-gray-400 mb-2">Sample Input</label>
                                        <textarea name="sampleInput" required rows="4"
                                            class="w-full bg-[#0A0A0A] border border-[#2A2A2A] text-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#0736FF] focus:border-transparent outline-none code-font"></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-gray-400 mb-2">Sample Output</label>
                                        <textarea name="sampleOutput" required rows="4"
                                            class="w-full bg-[#0A0A0A] border border-[#2A2A2A] text-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#0736FF] focus:border-transparent outline-none code-font"></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-gray-400 mb-2">Generator (C++)</label>
                                        <input type="file" name="generator" accept=".cpp" required
                                            class="w-full file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#0736FF] file:text-white hover:file:bg-[#062DBF]">
                                    </div>

                                    <div>
                                        <label class="block text-gray-400 mb-2">Solution (C++)</label>
                                        <input type="file" name="solution" accept=".cpp" required
                                            class="w-full file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#0736FF] file:text-white hover:file:bg-[#062DBF]">
                                    </div>

                                    <div>
                                        <label class="block text-gray-400 mb-2">Problem Illustration Image
                                            (Optional)</label>
                                        <input type="file" name="problemImage" accept="image/*"
                                            class="w-full file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#0736FF] file:text-white hover:file:bg-[#062DBF]">
                                    </div>

                                    <button type="submit"
                                        class="w-full bg-[#0736FF] text-white px-6 py-4 rounded-lg hover:bg-[#062DBF] transition-colors font-semibold">
                                        Add Problem
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="border-t border-[#2A2A2A] py-6">
                <?php include('../Layout/footer.php'); ?>
            </footer>
        </body>

    </html >