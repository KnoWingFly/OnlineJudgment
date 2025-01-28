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
    <?php include('timer.php'); ?>

    <script type="text/javascript">
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
                let resultHtml = `<div class="submission-result">`;

                // Add verdict and status
                const verdictClass = response.verdict === 0 ? 'accepted' : 'wrong';
                const verdictText = getVerdictText(response.verdict);
                resultHtml += `<div class="verdict ${verdictClass}">${verdictText}</div>`;

                // Check if output exists and add details
                const output = response.output || {};
                resultHtml += `
                <div class="result-section">
                    <h4>Test Input:</h4>
                    <pre>${escapeHtml(output.input || 'No input provided')}</pre>
                </div>
                <div class="result-section">
                    <h4>Expected Output:</h4>
                    <pre>${escapeHtml(output.expected || 'No expected output provided')}</pre>
                </div>
                <div class="result-section">
                    <h4>Your Output:</h4>
                    <pre>${escapeHtml(output.actual || 'No output provided')}</pre>
                </div>`;

                // Display execution time
                resultHtml += `
                <div class="execution-info">
                    Execution Time: ${parseFloat(response.execution_time || 0).toFixed(3)} seconds
                </div>
            </div>`;

                return resultHtml;
            }

            function getVerdictText(verdict) {
                switch (verdict) {
                    case 0:
                        return 'Accepted';
                    case 1:
                        return 'Compilation Error';
                    case 2:
                        return 'Wrong Answer';
                    case 3:
                        return 'Time Limit Exceeded';
                    case 5:
                        return 'Runtime Error';
                    default:
                        return 'Unknown Verdict';
                }
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

<body class="menu2" id="tab<?php echo $problemid; ?>">
    <div id="wrap">
        <?php include('Layout/header.php'); ?>
        <?php include('Layout/menu.php'); ?>

        <div id="content-wrap">
            <div id="main">
                <div class="messagebox" style="display: none"></div>

                <?php
                $time = date_create();

                if ($time < $startTime) {
                    echo '<h2>The contest has not yet begun</h2>';
                } else {
                    // Build problem tabs dynamically
                    echo '<ul id="tabnav">';
                    foreach ($all_problems as $prob) {
                        $activeClass = ($prob['id'] == $problemid) ? ' class="active"' : '';
                        echo "<li{$activeClass}><a href='index.php?id={$prob['id']}'>Problem {$prob['id']}</a></li>\n";
                    }
                    echo '</ul>';

                    echo "<h2>Problem $problemid: " . htmlspecialchars($problem['title']) . "</h2>";

                    if ($running) {
                        echo '<div id="submission-section">';
                        include('uploadform.php');

                        echo '<div id="submission-result-' . $problemid . '" class="submission-result"></div>';
                        echo '</div>';
                    }


                    echo "<p id='statementpanel'><code class='statement'>";
                    $statementFile = "$PROBLEMDIR/$problemid/statement.html";
                    if (file_exists($statementFile)) {
                        readfile($statementFile);
                    } else {
                        echo "Problem is not available at this time";
                    }
                    echo "</code></p>";

                    // Show edit button only for admin
                    if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
                        echo "<p id='editpanel' style='float: right'><button id='edit'>Edit</button></p>";
                    }
                }
                ?>
            </div>

            <div id="sidebar">
                <?php
                if ($time >= $startTime) {
                    echo "<h3>Point Value</h3>";
                    echo "<ul class='sidemenu'>";
                    echo "<li><strong>" . htmlspecialchars($problem['points']) . "</strong></li>";
                    echo "</ul>";
                }
                include('sidebar.php');
                ?>
            </div>
        </div>

        <div id="footer">
            <?php include('Layout/footer.php'); ?>
        </div>
    </div>
</body>

</html>