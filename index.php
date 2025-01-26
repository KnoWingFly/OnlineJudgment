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

    <script type="text/javascript" src="jquery-1.3.1.js"></script>
    <script type="text/javascript" src="jquery.form.js"></script>
    <script type="text/javascript" src="jquery.timers-1.1.2.js"></script>
    <?php include('timer.php'); ?>

    <script type="text/javascript">
        var originalStatementHtml = '';

        $(document).ready(function () {
            // Store original statement HTML when page loads
            originalStatementHtml = $('code.statement').html();

            // Contest-related functions
            dispTime();
            getLeaders();
            getDetails();

            // Periodic updates
            setInterval("dispTime()", 1000);
            setInterval("getLeaders()", getLeaderInterval);
            setInterval("getDetails()", getLeaderInterval);

            // Ajax form submission for code uploads
            $('.uploadform').ajaxForm({
                dataType: 'json',
                success: onSucess
            });

            // Edit button handler
            $('#edit').click(onEdit);
        });

        function onSucess(data) {
            // Handle different verdicts from code submission
            var verdictMessages = {
                0: { class: 'accepted', message: 'Accepted' },
                1: { class: 'compile', message: 'Compile Error' },
                2: { class: 'wrong', message: 'Wrong Answer' },
                3: { class: 'time', message: 'Time Limit Exceeded' },
                4: { class: 'invalid', message: 'Invalid File' },
                5: { class: 'RTE', message: 'Runtime Error' }
            };

            var verdict = verdictMessages[data.verdict] || { class: 'unknown', message: 'Unknown Verdict' };

            $('#status' + data.problemid)
                .attr('class', verdict.class)
                .html('<strong>' + verdict.message + '</strong>')
                .hide()
                .fadeIn('slow');

            messagebox(verdict.message);

            if (data.verdict === 0) {
                $('#upload' + data.problemid).hide();
                getDetails();
                getLeaders();
            }
        }

        function onEdit() {
            var problem = $('body').attr('id').replace('tab', '');

            // Fetch current problem details for editing
            $.ajax({
                url: 'admin/modifyproblem.php',
                type: 'GET',
                data: {
                    problemid: problem,
                    mode: 'getFullDetails'
                },
                dataType: 'json',
                success: function (data) {
                    // Create edit form
                    var editForm = `
                <div class="problem-edit-form">
                    <div class="form-group">
                        <label>Problem Title:</label>
                        <input type="text" id="edit-title" value="${data.title || ''}" />
                    </div>
                    
                    <div class="form-group">
                        <label>Points:</label>
                        <input type="number" id="edit-points" min="0" value="${data.points || ''}" />
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
                        <button id="save-changes">Save Changes</button>
                        <button id="cancel-edit">Cancel</button>
                    </div>
                </div>
                `;

                    // Replace the statement panel with the edit form
                    $('#statementpanel').html(editForm);

                    // Bind save and cancel button events
                    $('#save-changes').click(function () {
                        saveChanges(problem);
                    });

                    $('#cancel-edit').click(function () {
                        // Revert back to original statement
                        $('#statementpanel').html('<code class="statement">' + originalStatementHtml + '</code>');
                    });
                },
                error: function () {
                    alert('Failed to fetch problem details');
                }
            });
        }

        function saveChanges(problemId) {
            var data = {
                problemid: problemId,
                mode: 'updateProblem',
                title: $('#edit-title').val(),
                points: $('#edit-points').val(),
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
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        // Refresh the page to show updated content
                        location.reload();
                    } else {
                        alert('Failed to update problem: ' + response.message);
                    }
                },
                error: function () {
                    alert('Failed to save changes');
                }
            });
        }
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
                        include('uploadform.php');
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