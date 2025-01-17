<?php
// layout.php - Main layout template
session_start();
include_once('settings.php');

function checkAdminAccess() {
    if (!isset($_SESSION['isloggedin'])) {
        header('Location: login.php');
        exit;
    }
    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        die("Administrator access required");
    }
}

function renderLayout($content, $pageTitle = 'Programming Contest', $additionalStyles = '', $additionalScripts = '') {
    global $PROBLEMDIR;
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
    
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <script type="text/javascript" src="jquery-1.3.1.js"></script>
    <script type="text/javascript" src="jquery.timers-1.1.2.js"></script>
    <?php include('timer.php'); ?>
    
    <?php if ($additionalStyles): ?>
        <style type="text/css"><?php echo $additionalStyles; ?></style>
    <?php endif; ?>
    
    <?php if ($additionalScripts): ?>
        <script type="text/javascript"><?php echo $additionalScripts; ?></script>
    <?php endif; ?>
</head>

<body class="<?php echo isset($_GET['menuClass']) ? htmlspecialchars($_GET['menuClass']) : 'menu2'; ?>">
    <div id="wrap">
        <?php include('header.php'); ?>
        <?php include('menu.php'); ?>
        
        <div id="content-wrap">
            <div id="main">
                <div class="messagebox" style="display: none"></div>
                <?php echo $content; ?>
            </div>
            
            <div id="sidebar">
                <?php 
                if (isset($_SESSION['admin']) && strpos($_SERVER['PHP_SELF'], 'admin') !== false) {
                    echo '<h3>Admin Menu</h3>
                    <ul class="sidemenu">
                        <li><a href="admin/problems.php">Manage Problems</a></li>
                        <li><a href="admin/users.php">Manage Users</a></li>
                        <li><a href="admin/setting.php">Contest Settings</a></li>
                    </ul>';
                }
                include('sidebar.php'); 
                ?>
            </div>
        </div>
        
        <div id="footer">
            <?php include('footer.php'); ?>
        </div>
    </div>
</body>
</html>
<?php
}

// Common database connection function
function getDbConnection() {
    global $DBUSER, $DBPASS, $DBNAME;
    $cn = mysqli_connect('localhost', $DBUSER, $DBPASS, $DBNAME);
    if (!$cn) {
        die("Database connection failed: " . mysqli_connect_error());
    }
    return $cn;
}
?>