<?php
/*
 * @copyright (c) 2008 Nicolo John Davis
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['isloggedin'])) {
    header('Location: login.php');
    exit;
}

// Get user details from session
$username = $_SESSION['username'];
$userid = $_SESSION['userid'];

// Include settings
include('settings.php');


$host = 'localhost';

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$DBNAME;charset=utf8", $DBUSER, $DBPASS);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection error
    die("Connection failed: " . $e->getMessage());
}

// Determine the current problem ID
$problemid = isset($_GET['id']) ? (int) $_GET['id'] : 1;

// Fetch total number of problems and validate problem ID
$stmt = $pdo->query("SELECT COUNT(*) as total_problems FROM problems");
$total_problems = $stmt->fetchColumn();

$problemid = ($problemid > 0 && $problemid <= $total_problems) ? $problemid : 1;

// Fetch the current problem details
$stmt = $pdo->prepare("SELECT * FROM problems WHERE id = ?");
$stmt->execute([$problemid]);
$problem = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all problems for the navigation
$stmt = $pdo->query("SELECT id, title FROM problems ORDER BY id");
$all_problems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to extract specific section from HTML
function extractSection($html, $startText, $endTexts = [])
{
    // Convert HTML to DOM
    $dom = new DOMDocument();
    @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    // Find font elements with specific formatting
    $fontElements = $dom->getElementsByTagName('font');

    // Find the element with the start text
    $targetElement = null;
    foreach ($fontElements as $fontElement) {
        if (
            stripos($fontElement->textContent, $startText) !== false &&
            $fontElement->getAttribute('color') == '#0000FF' &&
            $fontElement->getElementsByTagName('h2')->length > 0
        ) {
            $targetElement = $fontElement;
            break;
        }
    }

    if (!$targetElement)
        return '';

    // Find the paragraph after the target element
    $paragraphText = '';
    $nextSibling = $targetElement->nextSibling;
    while ($nextSibling) {
        // Look for paragraph
        if ($nextSibling->nodeName === 'p') {
            $paragraphText = trim($nextSibling->textContent);
            break;
        }
        $nextSibling = $nextSibling->nextSibling;
    }

    // Find the next section header
    $endSection = null;
    foreach ($fontElements as $fontElement) {
        // Check if this is a section header for one of the end texts
        if ($fontElement->getAttribute('color') == '#0000FF') {
            $h2Elements = $fontElement->getElementsByTagName('h2');
            if ($h2Elements->length > 0) {
                $headerText = trim($h2Elements->item(0)->textContent);
                foreach ($endTexts as $endText) {
                    if (stripos($headerText, $endText) !== false) {
                        $endSection = $fontElement;
                        break 2;
                    }
                }
            }
        }
    }

    return $paragraphText;
}

// Handle AJAX requests for problem details
if (isset($_GET['mode']) && $_GET['mode'] == 'getFullDetails') {
    $statementFile = "$PROBLEMDIR/$problemid/statement.html";

    if (file_exists($statementFile)) {
        $statementContent = file_get_contents($statementFile);

        $response = [
            'title' => $problem['title'],
            'points' => $problem['points'],
            'time_limit' => $problem['time_limit'],
            'description' => extractSection($statementContent, 'Problem Description', ['Input Format']),
            'inputFormat' => extractSection($statementContent, 'Input Format', ['Output Format']),
            'outputFormat' => extractSection($statementContent, 'Output Format', ['Constraints']),
            'constraints' => extractSection($statementContent, 'Constraints', ['Sample Input']),
            'sampleInput' => extractSection($statementContent, 'Sample Input', ['Sample Output']),
            'sampleOutput' => extractSection($statementContent, 'Sample Output', [])
        ];

        echo json_encode($response);
        exit;
    } else {
        echo json_encode(['error' => 'Statement file not found']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta name="Keywords" content="programming, contest, coding, judge" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="Distribution" content="Global" />
    <meta name="Robots" content="index,follow" />

    <link rel="stylesheet" href="images/Envision.css" type="text/css" />
    <link rel="stylesheet" href="images/Tabs.css" type="text/css" />

    <title>Programming Contest - Problem <?php echo $problemid; ?></title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"
        integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn"
        crossorigin="anonymous"></script>
    <script type="text/javascript" src="jquery.timers-1.1.2.js"></script>
    <link href="../src/output.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;700&family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background: #000000;
            color: #cbd5e1;
        }

        .code-font {
            font-family: 'IBM Plex Mono', monospace;
        }

        .problem-content h1 {
            @apply text-3xl font-bold mb-6 text-blue-400;
        }

        .problem-content h2 {
            @apply text-xl font-semibold mt-8 mb-4 text-blue-300 border-b border-blue-400/30 pb-2;
        }

        .problem-content p {
            @apply mb-5 text-gray-300 leading-relaxed;
        }

        .problem-content pre {
            @apply bg-[#0A0A0A] text-gray-300 p-4 rounded-lg mb-6 border border-[#1A1A1A] code-font text-sm;
        }

        .problem-content img {
            @apply max-w-full h-auto mx-auto my-6 rounded-lg border border-[#1A1A1A];
        }

        .uploadform label {
            @apply text-gray-300 mb-2 block;
        }

        .uploadform select,
        .uploadform input[type="file"] {
            @apply bg-[#1A1A1A] border border-[#2A2A2A] text-gray-300 rounded-lg p-2 w-full;
        }

        .uploadform button {
            @apply bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors;
        }
    </style>
    <?php include('timer.php'); ?>

    <script type="text/javascript">

        function switchTestCase(caseIndex, button) {
            // Get all test cases data
            const testCasesData = JSON.parse(document.getElementById('test-cases-data').dataset.cases);
            const testCase = testCasesData[caseIndex];

            // Update tab styling
            document.querySelectorAll('.test-case-tab').forEach(tab => {
                tab.classList.remove('bg-slate-800', 'text-white');
                tab.classList.add('text-gray-400');
            });
            button.classList.add('bg-slate-800', 'text-white');
            button.classList.remove('text-gray-400');

            // Update content
            document.getElementById('case-input').textContent = testCase.input;
            document.getElementById('case-expected').textContent = testCase.expected;
            document.getElementById('case-actual').textContent = testCase.actual;

            // Update status panel
            const statusPanel = document.getElementById('case-status');
            statusPanel.className = `mt-4 py-3 px-4 rounded-lg ${testCase.isCorrect ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400'}`;
            statusPanel.innerHTML = `
        <div class="flex items-center gap-2">
            <span class="text-xl">${testCase.isCorrect ? '✓' : '×'}</span>
            <span class="font-medium">
                ${testCase.isCorrect ? 'Correct Answer' : 'Wrong Answer'}
            </span>
        </div>
    `;
        }
        
        $(document).ready(function () {
            $('.uploadform').on('submit', function (e) {
                e.preventDefault(); // Prevent default form submission behavior

                const formData = new FormData(this);
                const formAction = $(this).attr('action');
                const formMethod = $(this).attr('method') || 'POST';
                const problemid = $(this).data('problemid');

                $.ajax({
                    url: formAction,
                    type: formMethod,
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        console.log(`Submission started for Problem ID: ${problemid}`);
                        $('#submission-result-' + problemid).html('<div class="loading">Processing submission...</div>');
                    },
                    success: function (responseText) {
                        try {
                            const response = typeof responseText === 'string' ? JSON.parse(responseText) : responseText;

                            if (!response.problemid) {
                                console.error('Problem ID is missing from the response.');
                                $('#submission-result-' + problemid).html('<div class="error">Invalid response from the server.</div>');
                                return;
                            }

                            const resultHtml = generateSubmissionResultHtml(response);
                            $('#submission-result-' + response.problemid).html(resultHtml);
                        } catch (e) {
                            console.error('Error parsing response:', e, responseText);
                            $('#submission-result-' + problemid).html(
                                `<div class="error">Error processing response: ${e.message}</div>`
                            );
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX error:', error);
                        $('#submission-result-' + problemid).html(
                            `<div class="error">Submission failed: ${error}</div>`
                        );
                    }
                });
            });

            function generateSubmissionResultHtml(response) {
                const verdictClass = response.verdict === 0 ? 'accepted' : 'wrong';
                const verdictText = getVerdictText(response.verdict);

                // Parse test cases from input and outputs
                const testCases = parseTestCases(response.output);

                return `
    <div class="submission-result space-y-6">
        <!-- Verdict Header -->
        <div class="verdict-header ${verdictClass}">
            <h2 class="text-xl font-semibold mb-4 pb-2 border-b ${verdictClass === 'accepted' ? 'border-emerald-400/30 text-emerald-400' : 'border-rose-400/30 text-rose-400'}">
                ${verdictText}
            </h2>
        </div>

        <!-- Test Cases Navigator -->
        <div class="test-cases-container">
            <!-- Test Case Tabs -->
            <div class="flex space-x-2 mb-4 overflow-x-auto scrollbar-thin scrollbar-thumb-gray-600">
                ${testCases.map((testCase, index) => `
                    <button 
                        class="test-case-tab px-4 py-2 rounded-lg text-sm font-medium min-w-[100px] transition-colors
                        ${index === 0 ? 'bg-slate-800 text-white' : 'text-gray-400 hover:bg-slate-800/50'}"
                        data-case="${index}"
                        onclick="switchTestCase(${index}, this)">
                        Case ${index + 1}
                        <span class="ml-2 ${testCase.isCorrect ? 'text-emerald-400' : 'text-rose-400'}">
                            ${testCase.isCorrect ? '✓' : '×'}
                        </span>
                    </button>
                `).join('')}
            </div>

            <!-- Result Panels -->
            <div class="space-y-4">
                <!-- Input Panel -->
                <div class="space-y-2">
                    <h3 class="text-sm font-semibold text-blue-400 uppercase tracking-wider">Input</h3>
                    <div class="bg-[#0A0A0A] p-4 rounded-lg border border-[#1A1A1A]">
                        <pre id="case-input" class="code-font text-sm text-gray-300 whitespace-pre-wrap">${escapeHtml(testCases[0].input)}</pre>
                    </div>
                </div>

                <!-- Expected Output Panel -->
                <div class="space-y-2">
                    <h3 class="text-sm font-semibold text-blue-400 uppercase tracking-wider">Expected Output</h3>
                    <div class="bg-[#0A0A0A] p-4 rounded-lg border border-[#1A1A1A]">
                        <pre id="case-expected" class="code-font text-sm text-gray-300 whitespace-pre-wrap">${escapeHtml(testCases[0].expected)}</pre>
                    </div>
                </div>

                <!-- Your Output Panel -->
                <div class="space-y-2">
                    <h3 class="text-sm font-semibold text-blue-400 uppercase tracking-wider">Your Output</h3>
                    <div class="bg-[#0A0A0A] p-4 rounded-lg border border-[#1A1A1A]">
                        <pre id="case-actual" class="code-font text-sm text-gray-300 whitespace-pre-wrap">${escapeHtml(testCases[0].actual)}</pre>
                    </div>
                </div>

                <!-- Status Panel -->
                <div id="case-status" class="mt-4 py-3 px-4 rounded-lg ${testCases[0].isCorrect ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400'}">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">${testCases[0].isCorrect ? '✓' : '×'}</span>
                        <span class="font-medium">
                            ${testCases[0].isCorrect ? 'Correct Answer' : 'Wrong Answer'}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden Data Storage -->
        <div id="test-cases-data" class="hidden" data-cases='${JSON.stringify(testCases)}'></div>

        <!-- Execution Info -->
        <div class="execution-info text-sm text-gray-400 mt-4 flex items-center gap-4">
            <span>⏱ ${parseFloat(response.execution_time || 0).toFixed(3)}s</span>
            ${response.memory_usage ? `<span>💾 ${response.memory_usage}MB</span>` : ''}
        </div>
    </div>`;
            }

            function parseTestCases(output) {
                if (!output) return [];

                const testCases = [];
                const lines = output.input.split('\n');
                let currentCase = null;
                let caseLines = [];

                // Parse input into separate test cases
                for (let line of lines) {
                    if (line.startsWith('=== Case')) {
                        if (currentCase !== null) {
                            testCases.push({
                                input: caseLines.join('\n'),
                                caseNumber: currentCase
                            });
                            caseLines = [];
                        }
                        currentCase = parseInt(line.match(/Case (\d+)/)[1]);
                    } else if (currentCase !== null) {
                        caseLines.push(line);
                    }
                }
                if (caseLines.length > 0 && currentCase !== null) {
                    testCases.push({
                        input: caseLines.join('\n'),
                        caseNumber: currentCase
                    });
                }

                // Parse expected and actual outputs
                const expectedLines = output.expected.split('\n');
                const actualLines = output.actual.split('\n');

                let currentExpected = [];
                let currentActual = [];

                for (let i = 0; i < testCases.length; i++) {
                    currentExpected = [];
                    currentActual = [];

                    // Find the corresponding output sections
                    let startIdx = expectedLines.findIndex(line =>
                        line.includes(`=== Case ${testCases[i].caseNumber} ===`)
                    );
                    let endIdx = expectedLines.findIndex((line, idx) =>
                        idx > startIdx && line.includes('=== Case')
                    );
                    if (endIdx === -1) endIdx = expectedLines.length;

                    // Extract expected output
                    for (let j = startIdx + 1; j < endIdx; j++) {
                        if (expectedLines[j].trim()) {
                            currentExpected.push(expectedLines[j]);
                        }
                    }

                    // Find actual output section
                    startIdx = actualLines.findIndex(line =>
                        line.includes(`=== Case ${testCases[i].caseNumber} ===`)
                    );
                    endIdx = actualLines.findIndex((line, idx) =>
                        idx > startIdx && line.includes('=== Case')
                    );
                    if (endIdx === -1) endIdx = actualLines.length;

                    // Extract actual output
                    for (let j = startIdx + 1; j < endIdx; j++) {
                        if (actualLines[j].trim()) {
                            currentActual.push(actualLines[j]);
                        }
                    }

                    // Add outputs to test case
                    testCases[i].expected = currentExpected.join('\n');
                    testCases[i].actual = currentActual.join('\n');
                    testCases[i].isCorrect = currentExpected.join('\n') === currentActual.join('\n');
                }

                return testCases;
            }

            function escapeHtml(unsafe) {
                if (!unsafe) return '';
                return unsafe
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function getVerdictText(verdict) {
                switch (verdict) {
                    case 0: return 'Accepted';
                    case 1: return 'Compilation Error';
                    case 2: return 'Wrong Answer';
                    case 3: return 'Time Limit Exceeded';
                    case 5: return 'Runtime Error';
                    default: return 'Unknown Verdict';
                }
            }

            // Example of edit functionality
            $(document).ready(function () {
                // Function to handle edit button click
                function onEdit() {
                    const problemid = $('body').attr('id').replace('tab', '');

                    // Fetch current problem details for editing
                    $.ajax({
                        url: 'admin/modifyproblem.php',
                        type: 'GET',
                        data: {
                            problemid: problemid,
                            mode: 'getFullDetails'
                        },
                        dataType: 'json',
                        success: function (data) {
                            // Generate edit form
                            const editForm = `
                        <div class="problem-edit-form">
                            <div class="form-group">
                                <label>Problem Title:</label>
                                <input type="text" id="edit-title" value="${data.title || ''}" />
                            </div>
                            <div class="form-group">
                                <label>Time Limit (seconds):</label>
                                <input type="number" id="edit-time-limit" step="0.1" value="${data.time_limit || ''}" />
                            </div>
                            <div class="form-group">
                                <label>Problem Description:</label>
                                <textarea id="edit-description" style="width: 100%; height: 150px;">${data.description || ''}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Input Format:</label>
                                <textarea id="edit-input-format" style="width: 100%; height: 100px;">${data.inputFormat || ''}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Output Format:</label>
                                <textarea id="edit-output-format" style="width: 100%; height: 100px;">${data.outputFormat || ''}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Constraints:</label>
                                <textarea id="edit-constraints" style="width: 100%; height: 100px;">${data.constraints || ''}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Sample Input:</label>
                                <textarea id="edit-sample-input" style="width: 100%; height: 100px;">${data.sampleInput || ''}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Sample Output:</label>
                                <textarea id="edit-sample-output" style="width: 100%; height: 100px;">${data.sampleOutput || ''}</textarea>
                            </div>
                            <div class="form-group">
                                <button id="save-changes" class="btn-primary">Save Changes</button>
                                <button id="cancel-edit" class="btn-secondary">Cancel</button>
                            </div>
                        </div>`;

                            // Store original content
                            const originalContent = $('#statementpanel').html();

                            // Replace content with edit form
                            $('#statementpanel').html(editForm);

                            // Bind save action
                            $('#save-changes').click(function () {
                                const updatedData = {
                                    mode: 'updateProblem',
                                    problemid: problemid,
                                    title: $('#edit-title').val(),
                                    timeLimit: $('#edit-time-limit').val(),
                                    description: $('#edit-description').val(),
                                    inputFormat: $('#edit-input-format').val(),
                                    outputFormat: $('#edit-output-format').val(),
                                    constraints: $('#edit-constraints').val(),
                                    sampleInput: $('#edit-sample-input').val(),
                                    sampleOutput: $('#edit-sample-output').val()
                                };

                                $.ajax({
                                    url: 'admin/modifyproblem.php',
                                    type: 'POST',
                                    data: updatedData,
                                    success: function (response) {
                                        try {
                                            const result = typeof response === 'string' ? JSON.parse(response) : response;
                                            if (result.success) {
                                                location.reload();
                                            } else {
                                                alert('Failed to save changes: ' + (result.message || 'Unknown error'));
                                            }
                                        } catch (e) {
                                            console.error('Error parsing response:', e, response);
                                            alert('Error processing response from server');
                                        }
                                    },
                                    error: function (xhr, status, error) {
                                        console.error('AJAX error:', error);
                                        alert('Failed to save changes: ' + error);
                                    }
                                });
                            });

                            // Bind cancel action
                            $('#cancel-edit').click(function () {
                                $('#statementpanel').html(originalContent);
                            });
                        },
                        error: function (xhr, status, error) {
                            console.error('Error fetching problem details:', error);
                            alert('Failed to fetch problem details: ' + error);
                        }
                    });
                }

                // Bind edit button click event
                $('#edit').click(onEdit);
            });
        });
    </script>
</head>

<body class="text-gray-200">
    <div class="min-h-screen flex flex-col">
        <?php include('Layout/header.php'); ?>
        <?php include('Layout/menu.php'); ?>

        <main class="flex-1 container mx-auto px-4 py-8">
            <?php if ($time < $startTime): ?>
                <div class="glass-pane bg-red-900/30 border border-red-600/50 p-6 rounded-2xl mb-8">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 bg-red-600/20 rounded-lg flex items-center justify-center">
                            🔒
                        </div>
                        <h2 class="text-xl font-semibold">Contest Starts in 2:34:16</h2>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid lg:grid-cols-5 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-3">
                        <!-- Problem Navigation -->
                        <div class="glass-pane p-2 rounded-xl mb-8">
                            <div class="flex overflow-x-auto scrollbar-hide space-x-2">
                                <?php foreach ($all_problems as $prob): ?>
                                    <a href="index.php?id=<?= $prob['id'] ?>"
                                        class="<?= $prob['id'] == $problemid ? 'bg-slate-800/60 border border-slate-600/50 text-white' : 'hover:bg-slate-800/30 text-slate-400' ?> 
                                        problem-tab px-5 py-2.5 rounded-lg flex items-center gap-2 text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        Problem <?= $prob['id'] ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Problem Container -->
                        <div class="glass-pane p-8 rounded-2xl border border-slate-700/50 relative">

                            <!-- Problem Statement -->
                            <div id="statementpanel" class="space-y-8 text-gray-300">
                                <?php
                                $statementFile = "$PROBLEMDIR/$problemid/statement.html";
                                if (file_exists($statementFile)) {
                                    $content = file_get_contents($statementFile);

                                    $content = str_replace(
                                        [
                                            '<body bgcolor="white">',
                                            '</body>',
                                            '<font color="#0000FF">',
                                            '</font>',
                                            '<h1>',
                                            '</h1>',
                                            '<h2>',
                                            '</h2>',
                                            '<h3>',
                                            '</h3>',
                                            '<pre>',
                                            '</pre>',
                                            '<p>'
                                        ],
                                        [
                                            '',
                                            '',
                                            '<div class="text-blue-400 font-semibold">',
                                            '</div>',
                                            '<h1 class="text-3xl font-bold text-blue-400 mb-6">',
                                            '</h1>',
                                            '<h2 class="text-xl font-semibold mb-4 pb-2 border-b border-blue-400/30">',
                                            '</h2>',
                                            '<h3 class="text-lg font-medium mb-4 text-blue-300">',
                                            '</h3>',
                                            '<pre class="bg-[#0A0A0A] text-gray-300 p-4 rounded-lg mb-6 border border-[#1A1A1A] code-font text-sm">',
                                            '</pre>',
                                            '<p class="text-gray-300 leading-relaxed mb-5">'
                                        ],
                                        $content
                                    );

                                    echo $content;
                                } else {
                                    echo '<div class="glass-pane bg-red-900/30 border border-red-600/50 p-6 rounded-2xl">';
                                    echo '<div class="flex items-center gap-3 text-red-400">';
                                    echo '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                                    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>';
                                    echo '</svg>';
                                    echo '<span class="font-medium">Problem statement unavailable</span>';
                                    echo '</div></div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-2">
                        <!-- Sticky Container -->
                        <div class="space-y-6 sticky top-6">
                            <!-- Submission Form -->
                            <div class="glass-pane p-6 rounded-2xl border border-slate-700/50">
                                <h3 class="text-xl font-semibold text-blue-400 mb-4">Submit Solution</h3>
                                <div class="bg-[#0A0A0A] p-4 rounded-xl border border-[#1A1A1A]">
                                    <?php include('uploadform.php'); ?>
                                </div>
                                <div id="submission-result-<?= $problemid ?>" class="mt-4"></div>
                            </div>

                            <!-- Contest Status -->
                            <div class="glass-pane p-6 rounded-2xl border border-slate-700/50">
                                <div class="space-y-6">
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="h-8 w-8 bg-blue-600/20 rounded-lg flex items-center justify-center">
                                            📈
                                        </div>
                                        <h3 class="text-xl font-semibold text-blue-400">Contest Dashboard</h3>
                                    </div>

                                    <!-- Progress -->
                                    <div class="space-y-4">
                                        <?php
                                        // Get solved problems count
                                        $solvedQuery = $pdo->prepare("
                SELECT COUNT(DISTINCT problemid) as solved 
                FROM submissions 
                WHERE userid = ? AND status = 0
            ");
                                        $solvedQuery->execute([$userid]);
                                        $solved = $solvedQuery->fetch(PDO::FETCH_ASSOC)['solved'];

                                        // Get total problems count
                                        $totalProblems = $pdo->query("SELECT COUNT(*) FROM problems")->fetchColumn();
                                        ?>

                                        <div class="bg-slate-800/30 p-4 rounded-xl">
                                            <div class="flex items-center justify-between mb-3">
                                                <span class="text-sm text-gray-400">Problems Solved</span>
                                                <span
                                                    class="text-blue-400 font-medium"><?= $solved ?>/<?= $totalProblems ?></span>
                                            </div>
                                            <div class="h-2 bg-slate-700/50 rounded-full overflow-hidden">
                                                <div class="h-full bg-gradient-to-r from-blue-500 to-purple-500"
                                                    style="width: <?= ($solved / $totalProblems) * 100 ?>%"></div>
                                            </div>
                                        </div>

                                        <!-- Timer -->
                                        <div class="bg-slate-800/30 p-4 rounded-xl">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span class="text-sm text-gray-400">Time Remaining</span>
                                                </div>
                                                <span id="time" class="code-font text-emerald-400"></span>
                                            </div>
                                        </div>

                                        <?php
                                        // Get user rank
                                        $rankQuery = $pdo->prepare("
                SELECT userid, COUNT(DISTINCT problemid) as solved, MIN(time) as earliest
                FROM submissions 
                WHERE status = 0
                GROUP BY userid
                ORDER BY solved DESC, earliest ASC
            ");
                                        $rankQuery->execute();
                                        $rankings = $rankQuery->fetchAll(PDO::FETCH_ASSOC);

                                        $userRank = 1;
                                        foreach ($rankings as $index => $row) {
                                            if ($row['userid'] == $userid) {
                                                $userRank = $index + 1;
                                                break;
                                            }
                                        }
                                        ?>

                                        <!-- Rank -->
                                        <div class="bg-slate-800/30 p-4 rounded-xl">
                                            <div class="flex items-center justify-between">
                                                <div class="text-sm text-gray-400">Current Rank</div>
                                                <div class="text-xl font-bold text-purple-400">#<?= $userRank ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
        </main>

        <footer class="border-t border-slate-700/50 mt-12 py-6">
            <?php include('Layout/footer.php'); ?>
        </footer>
    </div>
</body>

</html>