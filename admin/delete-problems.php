<?php
session_start();
if (!isset($_SESSION['isloggedin']) || $_SESSION['admin'] != true) {
    header("Location: ../login.php");
    exit(0);
}

include('../settings.php');

// Handle problem deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $problemId = intval($_POST['problem_id']);
    
    // Connect to database
    $cn = mysqli_connect('localhost', $DBUSER, $DBPASS, $DBNAME);
    if (!$cn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Start transaction
    mysqli_begin_transaction($cn);

    try {
        // Get the directory path of the problem to be deleted
        $problemDir = "../problems/" . $problemId;

        // Get all problems with ID greater than the one being deleted
        $getHigherIds = "SELECT id FROM problems WHERE id > ? ORDER BY id";
        $stmt = mysqli_prepare($cn, $getHigherIds);
        mysqli_stmt_bind_param($stmt, "i", $problemId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $higherIds = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $higherIds[] = $row['id'];
        }

        // Delete the problem from database
        $deleteQuery = "DELETE FROM problems WHERE id = ?";
        $stmt = mysqli_prepare($cn, $deleteQuery);
        mysqli_stmt_bind_param($stmt, "i", $problemId);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to delete problem from database");
        }

        // Update the IDs of remaining problems
        foreach ($higherIds as $oldId) {
            $newId = $oldId - 1;
            
            // Rename the directory first (from highest to lowest to avoid conflicts)
            $oldDir = "../problems/" . $oldId;
            $newDir = "../problems/" . $newId;
            
            if (file_exists($oldDir) && !rename($oldDir, $newDir)) {
                throw new Exception("Failed to rename directory from $oldId to $newId");
            }
            
            // Update the ID in database
            $updateQuery = "UPDATE problems SET id = ? WHERE id = ?";
            $stmt = mysqli_prepare($cn, $updateQuery);
            mysqli_stmt_bind_param($stmt, "ii", $newId, $oldId);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to update problem ID from $oldId to $newId");
            }
        }

        // Now delete the original problem directory if it still exists
        if (file_exists($problemDir)) {
            // Recursive directory removal function
            function deleteDirectory($dir) {
                if (!file_exists($dir)) return true;
                if (!is_dir($dir)) return unlink($dir);
                
                foreach (scandir($dir) as $item) {
                    if ($item == '.' || $item == '..') continue;
                    
                    $path = $dir . DIRECTORY_SEPARATOR . $item;
                    if (is_dir($path)) {
                        deleteDirectory($path);
                    } else {
                        unlink($path);
                    }
                }
                
                return rmdir($dir);
            }

            if (!deleteDirectory($problemDir)) {
                throw new Exception("Failed to delete problem directory");
            }
        }

        // Commit transaction
        mysqli_commit($cn);
        $message = "Problem deleted successfully and IDs reordered";
    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($cn);
        $message = "Error: " . $e->getMessage();
    }

    // Close connection
    mysqli_close($cn);
}

// Fetch problems
$cn = mysqli_connect('localhost', $DBUSER, $DBPASS, $DBNAME);
if (!$cn) {
    die("Connection failed: " . mysqli_connect_error());
}

$query = "SELECT id, title, time_limit, created_at FROM problems ORDER BY id";
$result = mysqli_query($cn, $query);
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
    <title>Programming Contest - Delete Problems</title>

    <script type="text/javascript" src="../jquery-1.3.1.js"></script>
    <script type="text/javascript" src="../jquery.timers-1.1.2.js"></script>
    <?php include('../timer.php'); ?>

    <style type="text/css">
        .problems-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .problems-table th, .problems-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .problems-table th {
            background-color: #f2f2f2;
            color: #333;
        }
        .problems-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .delete-btn {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }
        .delete-btn:hover {
            background-color: #ff3333;
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

    <script type="text/javascript">
    function confirmDelete(problemId, problemTitle) {
        if (confirm('Are you sure you want to delete the problem "' + problemTitle + '"? This action cannot be undone.')) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '';

            var actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';
            form.appendChild(actionInput);

            var problemInput = document.createElement('input');
            problemInput.type = 'hidden';
            problemInput.name = 'problem_id';
            problemInput.value = problemId;
            form.appendChild(problemInput);

            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
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
                    <h2>Delete Problems</h2>
                    
                    <table class="problems-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Time Limit</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['time_limit']); ?> s</td>
                                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                    <td>
                                        <button 
                                            class="delete-btn" 
                                            onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['title'])); ?>')">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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

<?php 
// Close database connection
mysqli_close($cn);
?>