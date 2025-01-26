<?php
/*
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

session_start();
if (!isset($_SESSION['isloggedin'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$userid = $_SESSION['userid'];

include('settings.php');


$host = 'localhost';      

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$DBNAME;charset=utf8", $DBUSER, $DBPASS);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Handle connection error
    die("Connection failed: " . $e->getMessage());
}

// Get problem ID with validation
$problemid = isset($_GET['id']) ? (int)$_GET['id'] : 1;

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
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta name="Keywords" content="programming, contest, coding, judge" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
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
    $(document).ready(function() {
        dispTime();
        getLeaders();
        getDetails();
        setInterval("dispTime()", 1000);
        setInterval("getLeaders()", getLeaderInterval);
        setInterval("getDetails()", getLeaderInterval);

        $('.uploadform').ajaxForm({
            dataType: 'json',
            success: onSucess
        });

        $('#edit').click(onEdit);
    });

    function onSucess(data) {
        if (data.verdict == 0) {
            $('#status' + data.problemid).attr('class', 'accepted');
            $('#upload' + data.problemid).hide();
            $('#status' + data.problemid).html('<strong>Accepted</strong>');
            $('#status' + data.problemid).hide();
            $('#status' + data.problemid).fadeIn('slow');
            messagebox('Accepted');
            getDetails();
            getLeaders();
        } else if (data.verdict == 1) {
            $('#status' + data.problemid).attr('class', 'compile');
            $('#status' + data.problemid).html('<strong>Compile Error</strong>');
            $('#status' + data.problemid).hide();
            $('#status' + data.problemid).fadeIn('slow');
            messagebox('Compile Error');
        } else if (data.verdict == 2) {
            $('#status' + data.problemid).attr('class', 'wrong');
            $('#status' + data.problemid).html('<strong>Wrong Answer</strong>');
            $('#status' + data.problemid).hide();
            $('#status' + data.problemid).fadeIn('slow');
            messagebox('Wrong Answer');
        } else if (data.verdict == 3) {
            $('#status' + data.problemid).attr('class', 'time');
            $('#status' + data.problemid).html('<strong>Time Limit</strong>');
            $('#status' + data.problemid).hide();
            $('#status' + data.problemid).fadeIn('slow');
            messagebox('Time Limit Exceeded');
        } else if (data.verdict == 4) {
            $('#status' + data.problemid).attr('class', 'invalid');
            $('#status' + data.problemid).html('<strong>Invalid File</strong>');
            $('#status' + data.problemid).hide();
            $('#status' + data.problemid).fadeIn('slow');
            messagebox('Invalid File');
        } else if (data.verdict == 5) {
            $('#status' + data.problemid).attr('class', 'RTE');
            $('#status' + data.problemid).html('<strong>Runtime Error</strong>');
            $('#status' + data.problemid).hide();
            $('#status' + data.problemid).fadeIn('slow');
            messagebox('Runtime Error');
        }
    }

    function onEdit() {
        $('#editpanel').html('<button id="save">Save</button> <button id="cancel">Cancel</button>');
        $('#save').click(onSave);
        $('#cancel').click(onCancel);

        var problem = $('body').attr('id'); 
        problem = problem.replace('tab', '');

        $.get('admin/modifyproblem.php', { problemid: problem, mode: 'get' }, function(data) {
            $('#statementpanel').html('<textarea id="statement" style="width: 100%"></textarea>');
            $('#statement').text(data);
        });
    }

    function onCancel() {
        $('#editpanel').html('<button id="edit">Edit</button>');
        $('#edit').click(onEdit);

        var problem = $('body').attr('id');
        problem = problem.replace('tab', '');

        $.get('admin/modifyproblem.php', { problemid: problem, mode: 'get' }, function(data) {
            $('#statementpanel').html('<code class="statement"></code>');
            $('code.statement').html(data);
        });
    }

    function onSave() {
        $('#editpanel').html('<button id="edit">Edit</button>');
        $('#edit').click(onEdit);

        var st = $('#statement').val();
        var problem = $('body').attr('id');
        problem = problem.replace('tab', '');

        $.post('admin/modifyproblem.php', { problemid: problem, mode: 'put', statement: st }, function(data) {
            $('#statementpanel').html('<code class="statement"></code>');
            $('code.statement').html(data);
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
                    echo "<li class='tab{$prob['id']}'><a href='index.php?id={$prob['id']}'>Problem {$prob['id']}</a></li>\n";
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