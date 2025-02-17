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

function generateStatement($title, $timeLimit, $inputFormat, $outputFormat, $sampleCases, $description, $constraints, $explanation, $imagePath = null) {
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

    // Process text fields
    $processText = function ($text) {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $text = str_replace("\\r\\n", "<br>", $text);
        $text = str_replace(["\r\n", "\n", "\r"], "<br>", $text);
        return $text;
    };

    // Apply processing
    $title = htmlspecialchars(trim($title), ENT_QUOTES, 'UTF-8');
    $timeLimit = htmlspecialchars(trim($timeLimit), ENT_QUOTES, 'UTF-8');
    $description = $processText($description);
    $inputFormat = $processText($inputFormat);
    $outputFormat = $processText($outputFormat);
    $constraints = $processText($constraints);
    $explanation = $processText($explanation);

    // Generate sample cases HTML
    $samplesHtml = '';
    foreach ($sampleCases as $index => $case) {
        $input = htmlspecialchars($case['input']);
        $output = htmlspecialchars($case['output']);
        $exampleNum = $index + 1;
        
        $samplesHtml .= <<<EOT
<div class="space-y-6 mb-8">
    <div class="text-blue-400">
        <h3 class="text-lg font-semibold mb-2">Example {$exampleNum}</h3>
    </div>
    <div class="space-y-4">
        <div>
            <div class="text-zinc-400 mb-2">Input:</div>
            <div class="bg-[#0A0A0A] text-gray-300 p-4 rounded-lg border border-[#1A1A1A] code-font text-sm">
                <pre class="whitespace-pre-wrap">{$input}</pre>
            </div>
        </div>
        <div>
            <div class="text-zinc-400 mb-2">Output:</div>
            <div class="bg-[#0A0A0A] text-gray-300 p-4 rounded-lg border border-[#1A1A1A] code-font text-sm">
                <pre class="whitespace-pre-wrap">{$output}</pre>
            </div>
        </div>
    </div>
</div>
EOT;
    }

    $explanationSection = '';
    if (!empty($explanation)) {
        $explanationSection = <<<EOT
<div class="space-y-6">
    <div class="text-blue-400">
        <h2 class="text-xl font-semibold mb-4 pb-2 border-b border-blue-400/30">Explanation</h2>
    </div>
    <p class="text-gray-300 leading-relaxed">{$explanation}</p>
</div>
EOT;
    }

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
            <h2 class="text-xl font-semibold mb-4 pb-2 border-b border-blue-400/30">Examples</h2>
        </div>
        {$samplesHtml}
    </div>

    {$explanationSection}
</div>
EOT;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    debug_log("Form submitted");
    try {
        $cn = mysqli_connect('localhost', $DBUSER, $DBPASS, $DBNAME);
        if (!$cn)
            throw new Exception("Database connection failed");

        mysqli_begin_transaction($cn);

        $result = mysqli_query($cn, "SELECT MAX(id) AS max_id FROM problems");
        $row = mysqli_fetch_assoc($result);
        $newProblemId = isset($row['max_id']) ? $row['max_id'] + 1 : 1;

        $title = mysqli_real_escape_string($cn, $_POST['title']);
        $timeLimit = floatval($_POST['timeLimit']);
        $query = "INSERT INTO problems (id, title, time_limit, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($cn, $query);
        mysqli_stmt_bind_param($stmt, "isd", $newProblemId, $title, $timeLimit);
        if (!mysqli_stmt_execute($stmt))
            throw new Exception("Insert failed: " . mysqli_error($cn));

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

        // Process sample cases
        $sampleCases = [];
        $caseCount = intval($_POST['caseCount']);
        for ($i = 1; $i <= $caseCount; $i++) {
            if (isset($_POST["input_$i"]) && isset($_POST["output_$i"])) {
                $sampleCases[] = [
                    'input' => $_POST["input_$i"],
                    'output' => $_POST["output_$i"]
                ];
            }
        }

        // Generate statement
        $statementHtml = generateStatement(
            $title,
            $timeLimit,
            $_POST['inputFormat'],
            $_POST['outputFormat'],
            $sampleCases,
            $_POST['description'],
            $_POST['constraints'],
            $_POST['explanation'],
            $imagePath
        );
        
        file_put_contents($problemDir . "statement.html", $statementHtml);
        chmod($problemDir . "statement.html", 0666);

        if (!saveUploadedFile($_FILES["generator"], $newProblemId, "generator.cpp")) {
            throw new Exception("Failed to save generator");
        }
        if (!saveUploadedFile($_FILES["solution"], $newProblemId, "solution.cpp")) {
            throw new Exception("Failed to save solution");
        }

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Problem - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background: #09090b;
        }

        .code-font {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
    <script>
        function addSampleCase() {
            const container = document.getElementById('sampleCases');
            const caseCount = container.getElementsByClassName('sample-case').length + 1;
            
            const newCase = document.createElement('div');
            newCase.className = 'sample-case mb-6 p-4 bg-zinc-800 rounded-lg border border-zinc-700';
            newCase.innerHTML = `
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-zinc-300">Example ${caseCount}</h3>
                    <button type="button" onclick="removeSampleCase(this)" class="text-red-400 hover:text-red-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-300 mb-2">Input</label>
                        <textarea name="input_${caseCount}" rows="4" required
                            class="w-full bg-zinc-900 border border-zinc-700 text-zinc-100 rounded-md px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none code-font"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-300 mb-2">Output</label>
                        <textarea name="output_${caseCount}" rows="4" required
                            class="w-full bg-zinc-900 border border-zinc-700 text-zinc-100 rounded-md px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none code-font"></textarea>
                    </div>
                </div>
            `;
            
            container.appendChild(newCase);
            updateCaseCount();
        }

        function removeSampleCase(button) {
            const container = document.getElementById('sampleCases');
            if (container.getElementsByClassName('sample-case').length > 1) {
                button.closest('.sample-case').remove();
                reorderCases();
                updateCaseCount();
            }
        }

        function reorderCases() {
            const cases = document.getElementsByClassName('sample-case');
            Array.from(cases).forEach((caseDiv, index) => {
                const number = index + 1;
                caseDiv.querySelector('h3').textContent = `Example ${number}`;
                caseDiv.querySelector('textarea[name^="input_"]').name = `input_${number}`;
                caseDiv.querySelector('textarea[name^="output_"]').name = `output_${number}`;
            });
        }

        function updateCaseCount() {
            const count = document.getElementsByClassName('sample-case').length;
            document.getElementById('caseCount').value = count;
        }
    </script>
    <?php include('../timer.php'); ?>
</head>
<body class="bg-zinc-900 text-zinc-100 min-h-screen">
    <!-- Header -->
    <?php include('../Layout/header.php'); ?>
    <?php include('../Layout/menu.php'); ?>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (isset($message)): ?>
            <div class="mb-6 p-4 rounded-lg <?= strpos($message, 'Error') !== false ? 'bg-red-900/50 text-red-300' : 'bg-emerald-900/20 text-emerald-400' ?>">
            <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="bg-zinc-800 p-8 rounded-xl border border-zinc-700 shadow-xl">
            <div class="mb-8 pb-6 border-b border-zinc-700">
                <h1 class="text-3xl font-bold text-blue-400">Add New Problem</h1>
                <p class="mt-2 text-zinc-400">Fill in the problem details and required files</p>

                <!-- Navigation Menu -->
                <div class="mt-6 flex flex-wrap gap-4">
                    <a href="problems.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Add Problems
                    </a>
                    <a href="delete-problems.php" class="inline-flex items-center px-4 py-2 bg-zinc-700 text-zinc-300 rounded-lg hover:bg-zinc-600 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete Problems
                    </a>
                    <a href="setting.php" class="inline-flex items-center px-4 py-2 bg-zinc-700 text-zinc-300 rounded-lg hover:bg-zinc-600 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Contest Settings
                    </a>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data" class="space-y-8">
                <!-- Basic Information -->
                <div class="bg-zinc-900 p-6 rounded-lg border border-zinc-700">
                    <h2 class="text-xl font-semibold text-blue-400 mb-4">Basic Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-zinc-300 mb-2">Problem Title</label>
                            <input type="text" name="title" required
                                class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 rounded-md px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-300 mb-2">Time Limit (seconds)</label>
                            <input type="number" name="timeLimit" step="0.1" required
                                class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 rounded-md px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                        </div>
                    </div>
                </div>

                <!-- Problem Content -->
                <div class="bg-zinc-900 p-6 rounded-lg border border-zinc-700">
                    <h2 class="text-xl font-semibold text-blue-400 mb-4">Problem Content</h2>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-zinc-300 mb-2">Description</label>
                            <textarea name="description" required rows="4"
                                class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 rounded-md px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none code-font"></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-zinc-300 mb-2">Input Format</label>
                                <textarea name="inputFormat" required rows="4"
                                    class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 rounded-md px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none code-font"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-300 mb-2">Output Format</label>
                                <textarea name="outputFormat" required rows="4"
                                    class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 rounded-md px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none code-font"></textarea>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-300 mb-2">Constraints</label>
                            <textarea name="constraints" required rows="4"
                                class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 rounded-md px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none code-font"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Sample Cases -->
                <div class="bg-zinc-900 p-6 rounded-lg border border-zinc-700">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-blue-400">Sample Cases</h2>
                        <button type="button" onclick="addSampleCase()"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Example
                        </button>
                    </div>
                    <input type="hidden" id="caseCount" name="caseCount" value="1">
                    <div id="sampleCases" class="space-y-4">
                        <!-- Initial sample case -->
                        <div class="sample-case mb-6 p-4 bg-zinc-800 rounded-lg border border-zinc-700">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-zinc-300">Example 1</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-zinc-300 mb-2">Input</label>
                                    <textarea name="input_1" rows="4" required
                                        class="w-full bg-zinc-900 border border-zinc-700 text-zinc-100 rounded-md px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none code-font"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-300 mb-2">Output</label>
                                    <textarea name="output_1" rows="4" required
                                        class="w-full bg-zinc-900 border border-zinc-700 text-zinc-100 rounded-md px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none code-font"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Explanation Section -->
                <div class="bg-zinc-900 p-6 rounded-lg border border-zinc-700">
                    <h2 class="text-xl font-semibold text-blue-400 mb-4">Explanation</h2>
                    <textarea name="explanation" rows="4"
                        class="w-full bg-zinc-800 border border-zinc-700 text-zinc-100 rounded-md px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                        placeholder="Explain the sample cases..."></textarea>
                </div>

                <!-- File Uploads -->
                <div class="bg-zinc-900 p-6 rounded-lg border border-zinc-700">
                    <h2 class="text-xl font-semibold text-blue-400 mb-4">File Uploads</h2>
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-zinc-300 mb-2">Generator (C++)</label>
                                <div class="flex items-center justify-center w-full bg-zinc-800 border-2 border-dashed border-zinc-700 rounded-lg p-6">
                                    <input type="file" name="generator" accept=".cpp" required
                                        class="block w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-300 mb-2">Solution (C++)</label>
                                <div class="flex items-center justify-center w-full bg-zinc-800 border-2 border-dashed border-zinc-700 rounded-lg p-6">
                                    <input type="file" name="solution" accept=".cpp" required
                                        class="block w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-300 mb-2">Problem Image (Optional)</label>
                            <div class="flex items-center justify-center w-full bg-zinc-800 border-2 border-dashed border-zinc-700 rounded-lg p-6">
                                <input type="file" name="problemImage" accept="image/*"
                                    class="block w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 text-white px-8 py-4 rounded-lg hover:bg-blue-700 transition-colors font-semibold text-lg">
                    Create Problem
                </button>
            </form>
        </div>
    </div>

    <footer class="border-t border-zinc-800 py-6 mt-12">
        <?php include('../Layout/footer.php'); ?>
    </footer>
</body>
</html>