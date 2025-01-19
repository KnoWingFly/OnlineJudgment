<?php
/*
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

session_start();
if (!isset($_SESSION['isloggedin']) || !isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ../login.php');
    exit;
}

include('../settings.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $cn = mysqli_connect('localhost', $DBUSER, $DBPASS, $DBNAME);
    if (!$cn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $allowed_settings = ['start_time', 'end_time', 'leader_interval', 'chat_interval'];
    $error = false;
    
    foreach ($allowed_settings as $key) {
        if (isset($_POST[$key])) {
            $safe_key = mysqli_real_escape_string($cn, $key);
            $value = $_POST[$key];
            
            if (in_array($key, ['start_time', 'end_time'])) {
                $datetime = str_replace('T', ' ', $value);
                if (!strtotime($datetime)) {
                    $error_message = "Invalid datetime format for $key";
                    $error = true;
                    break;
                }
                $safe_value = mysqli_real_escape_string($cn, $datetime);
            } 
            elseif (!is_numeric($value) || $value < 1000) {
                $error_message = "$key must be at least 1000 milliseconds";
                $error = true;
                break;
            }
            else {
                $safe_value = mysqli_real_escape_string($cn, $value);
            }

            mysqli_query($cn, "UPDATE system_settings SET setting_value = '$safe_value' 
                             WHERE setting_key = '$safe_key'");
        }
    }
    
    if (!$error) {
        $success_message = "Settings updated successfully!";
        generateSettingsFile($cn);
    }
    
    mysqli_close($cn);
}

function generateSettingsFile($cn) {
    global $DBUSER, $DBPASS, $DBNAME, $points, $CODEDIR, $PROBLEMDIR;
    
    $result = mysqli_query($cn, "SELECT setting_key, setting_value FROM system_settings");
    $settings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    $content = "<?php\n\n";
    $content .= "/*\n* @copyright (c) 2008 Nicolo John Davis\n";
    $content .= "* @license http://opensource.org/licenses/gpl-license.php GNU Public License\n*/\n\n";
    
    $content .= "\$DBUSER = '$DBUSER';\n";
    $content .= "\$DBPASS = '$DBPASS';\n";
    $content .= "\$DBNAME = '$DBNAME';\n\n";
    $content .= "\$points = array(" . implode(',', $points) . ");\n\n";
    $content .= "\$startTime = date_create('{$settings['start_time']}');\n";
    $content .= "\$endTime = date_create('{$settings['end_time']}');\n\n";
    $content .= "\$getLeaderInterval = {$settings['leader_interval']};\n";
    $content .= "\$getChatInterval = {$settings['chat_interval']};\n\n";
    $content .= "ini_set('display_errors', false);\n\n";
    $content .= "\$time = date_create();\n";
    $content .= "\$running = \$time >= \$startTime && \$time <= \$endTime;\n";
    $content .= "\$CODEDIR = '$CODEDIR';\n";
    $content .= "\$PROBLEMDIR = '$PROBLEMDIR';\n";
    $content .= "?>";
    
    file_put_contents('../settings.php', $content);
}

// Fetch current settings
$cn = mysqli_connect('localhost', $DBUSER, $DBPASS, $DBNAME);
$result = mysqli_query($cn, "SELECT setting_key, setting_value, setting_type FROM system_settings 
                           WHERE setting_key IN ('start_time', 'end_time', 'leader_interval', 'chat_interval') 
                           ORDER BY FIELD(setting_key, 'start_time', 'end_time', 'leader_interval', 'chat_interval')");
$settings = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_close($cn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="programming, contest, coding, judge">
    <link rel="stylesheet" href="../images/Envision.css">
    <link rel="stylesheet" href="../images/Tabs.css">
    <script type="text/javascript" src="../jquery-1.3.1.js"></script>
    <script type="text/javascript" src="../jquery.timers-1.1.2.js"></script>
    <?php include('../timer.php'); ?>
    <title>Programming Contest - Admin Settings</title>
    
    <style>
    .settings-form {
        background: #fff;
        padding: 20px;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .setting-group {
        margin-bottom: 20px;
    }
    .setting-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #666;
    }
    .setting-group input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 5px;
    }
    .setting-description {
        font-size: 0.9em;
        color: #888;
        margin-top: 3px;
    }
    .setting-warning {
        color: #d35400;
        font-weight: bold;
        margin-top: 5px;
        padding: 8px;
        background-color: #fef9e7;
        border-left: 3px solid #f39c12;
    }
    .submit-button {
        background: #88ac0b;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1em;
        margin-top: 20px;
    }
    .submit-button:hover {
        background: #7a9d0a;
    }
    .message {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    .success {
        background: #dff0d8;
        color: #3c763d;
        border: 1px solid #d6e9c6;
    }
    .error {
        background: #f2dede;
        color: #a94442;
        border: 1px solid #ebccd1;
    }
    .time-note {
        background: #f8f9f9;
        border: 1px solid #eee;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    .time-note h3 {
        margin-top: 0;
        color: #2c3e50;
    }
    .time-example {
        background: #ecf0f1;
        padding: 10px;
        margin-top: 10px;
        border-radius: 3px;
    }
    </style>

    <script type="text/javascript">
    $(document).ready(function() { 
        // Initial setup
        dispTime();
        
        // Set intervals for updates
        setInterval("dispTime()", 1000);
        setInterval("getLeaders()", getLeaderInterval);
        setInterval("getDetails()", getLeaderInterval);

        // Menu highlighting
        $("#menu7").addClass("current");
    });
    </script>
</head>

<body class="menu7">
<div id="wrap">
    <?php include('../header.php'); ?>
    
    <!-- menu -->    
    <div id="menu">
        <ul>
            <?php if(!isset($_SESSION['isloggedin'])) print '<li id="menu1"><a href="../login.php">Login</a></li>'; ?>
            <?php if(isset($_SESSION['isloggedin'])) print '<li id="menu2"><a href="../index.php">Problems</a></li>'; ?>
            <?php if(isset($_SESSION['isloggedin'])) print '<li id="menu3"><a href="../submissions.php">Submissions</a></li>'; ?>
            <?php if(isset($_SESSION['isloggedin'])) print '<li id="menu4"><a href="../scoreboard.php">Scoreboard</a></li>'; ?>
            <li id="menu5"><a href="../faq.php">FAQ</a></li>    
            <?php if(isset($_SESSION['isloggedin'])) print '<li id="menu6"><a href="../chat.php">Chat</a></li>'; ?>
            <?php if(isset($_SESSION['admin'])) print '<li id="menu7"><a href="admin.php">Admin</a></li>'; ?>
            <?php if(isset($_SESSION['isloggedin'])) print '<li id="menu8"><a href="../personal.php">Personal</a></li>'; ?>
            <?php if(isset($_SESSION['isloggedin'])) print '<li id="menu9"><a href="../logout.php">Logout</a></li>'; ?>
        </ul>
    </div>
    
    <div id="content-wrap">
        <div id="main">
            <?php if (isset($success_message)): ?>
                <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="time-note">
                <h3>⚠️ Important Time Setting Information</h3>
                <p>There is a 10-hour time offset in the system. When setting contest times:</p>
                <p><strong>Subtract 10 hours from your intended time.</strong></p>
                <div class="time-example">
                    Example: If you want the contest to start at 15:00 (3 PM), set the time to 05:00 (5 AM)
                </div>
            </div>

            <div class="settings-form">
                <h2>Contest Settings</h2>
                <form method="post">
                    <?php foreach ($settings as $setting): ?>
                        <div class="setting-group">
                            <label for="<?php echo htmlspecialchars($setting['setting_key']); ?>">
                                <?php echo ucwords(str_replace('_', ' ', $setting['setting_key'])); ?>
                            </label>
                            
                            <?php if ($setting['setting_type'] === 'datetime'): ?>
                                <input type="datetime-local" 
                                       id="<?php echo htmlspecialchars($setting['setting_key']); ?>"
                                       name="<?php echo htmlspecialchars($setting['setting_key']); ?>"
                                       value="<?php echo htmlspecialchars(str_replace(' ', 'T', $setting['setting_value'])); ?>"
                                       required>
                                <div class="setting-description">Format: YYYY-MM-DD HH:MM:SS</div>
                                <div class="setting-warning">Remember: Set time 10 hours earlier than intended contest time</div>
                            <?php else: ?>
                                <input type="number" 
                                       id="<?php echo htmlspecialchars($setting['setting_key']); ?>"
                                       name="<?php echo htmlspecialchars($setting['setting_key']); ?>"
                                       value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                                       min="1000"
                                       required>
                                <div class="setting-description">Interval in milliseconds</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <input type="submit" name="update_settings" value="Save Settings" class="submit-button">
                </form>
            </div>
        </div>
        
        <div id="sidebar">
            <h3>Admin Menu</h3>
            <ul class="sidemenu">
                <li><a href="problems.php">Manage Problems</a></li>
                <li><a href="users.php">Manage Users</a></li>
                <li><a href="settings.php">Contest Settings</a></li>
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