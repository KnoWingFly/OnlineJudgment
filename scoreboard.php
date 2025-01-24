<?php
/*
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

session_start();
if(!isset($_SESSION['isloggedin']))
{
    echo "<meta http-equiv='Refresh' content='0; URL=login.php' />";
    exit(0);
}
else
{
    $username = $_SESSION['username'];
    $userid = $_SESSION['userid'];
}

include('settings.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta name="Keywords" content="programming, contest, coding, judge" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="Distribution" content="Global" />
    <meta name="Robots" content="index,follow" />

    <link rel="stylesheet" href="images/Envision.css" type="text/css" />

    <title>Programming Contest - Scoreboard</title>

    <script type="text/javascript" src="jquery-1.3.1.js"></script>
    <?php include('timer.php'); ?>
    <script type="text/javascript">
    <!--
    function getScores() {
        $.ajax({
            url: 'getscores.php',
            success: function(data) {
                $('#scores').html(data);
            },
            error: function(xhr, status, error) {
                console.error("Error fetching scores:", error);
            }
        });
    }

    $(document).ready(function() { 
        dispTime();
        getScores();
        getLeaders();
        getDetails();
        setInterval("dispTime()", 1000);  
        setInterval("getScores()", getLeaderInterval);  
        setInterval("getLeaders()", getLeaderInterval);  
        setInterval("getDetails()", getLeaderInterval);  
    });
    -->
    </script>
</head>

<body class="menu4">
    <!-- wrap starts here -->
    <div id="wrap">
        
        <!--header -->
        <?php include('Layout/header.php'); ?>    

        <!-- menu -->    
        <?php include('Layout/menu.php'); ?>

        <!-- content-wrap starts here -->
        <div id="content-wrap">
            
            <div id="main">
                <h2>Scoreboard</h2>
                <p>Real-time contest standings. Scores are automatically updated every <?php echo $getLeaderInterval/1000; ?> seconds.</p>
                
                <div class="table-container">
                    <table id="scores" class="contest-table">
                        <!-- This will be populated by getscores.php -->
                    </table>
                </div>
            </div>
            
            <div id="sidebar">
                <?php include('sidebar.php'); ?>    
            </div>
        
        <!-- content-wrap ends here -->    
        </div>
                    
        <!--footer starts here-->
        <div id="footer">
            <?php include('Layout/footer.php'); ?>
        </div>    

    <!-- wrap ends here -->
    </div>

</body>
</html>