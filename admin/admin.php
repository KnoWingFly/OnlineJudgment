<?php
/*
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

session_start();
if(!isset($_SESSION['isloggedin']))
{
    echo "<meta http-equiv='Refresh' content='0; URL=../login.php' />";
    exit(0);
}
else
{
    $username = $_SESSION['username'];
    $userid = $_SESSION['userid'];

    if($_SESSION['admin'] != true)
    {
        print "You need to be the administrator to access this file";
        exit(0);
    }
}

include('../settings.php');
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

    <title>Programming Contest - Admin Panel</title>
    <script type="text/javascript" src="../jquery-1.3.1.js"></script>
    <script type="text/javascript" src="../jquery.timers-1.1.2.js"></script>
    <?php include('../timer.php'); ?>
    <style type="text/css">
        .admin-section {
            background: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }
        .admin-section h2 {
            color: #88ac0b;
            font-size: 18px;
            margin-top: 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #e0e0e0;
        }
        .announcementform {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .code-view-section {
            margin: 15px 0;
        }
        .code-view-section select {
            margin: 0 10px;
            padding: 4px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        #viewcodebutton {
            padding: 5px 15px;
            background: #88ac0b;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        #viewcodebutton:hover {
            background: #7a9d0a;
        }
        #codeplaceholder {
            background: #f5f5f5;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 15px;
        }
    </style>
    <script type="text/javascript">
    <!--
    $(document).ready(function() { 
        // Ensure the page is scrolled to top on reload
        $(window).scrollTop(0);
        
        dispTime();
        getLeaders();
        getDetails();
        setInterval("dispTime()", 1000);  
        setInterval("getLeaders()", getLeaderInterval);  
        setInterval("getDetails()", getLeaderInterval);  

        // Initialize the announcement form
        $(".announcementform").focus();
        $(".announcementform").keypress(function(e) {
            if(e.which == 13) {
                var msg = this.value;
                this.value = "";

                if(msg != "") //To prevent empty line announcements
                    $.post("submitannouncement.php", { message: msg }, function(){
                        messagebox('Announcement posted');
                    });
            }
        });

        // Code viewing functionality
        $("#uname,#problemid").change(function() {
            var uname = $("#uname").val();
            var id = $("#problemid").val();

            $.get("getcode.php", {username: uname, problemid: id, mode: 'getdir'}, function(data) { 
                $("#filename").html(data); 
            });
        });
                
        //Initially populate the filename box with problem 1 of admin
        $.get("getcode.php", {username: 'admin', problemid: '1', mode: 'getdir'}, function(data) { 
            $("#filename").html(data); 
        });

        $("#viewcodebutton").click(function() {
            var uname = $("#uname").val();
            var id = $("#problemid").val();
            var file = $("#filename").val();

            $.get("getcode.php", {username: uname, problemid: id, filename: file}, function(data) { 
                $("#codeplaceholder").html(data).fadeIn("fast"); 
            });
        });

        // Ensure proper menu highlighting
        $("#menu7").addClass("current");
    });
    -->
    </script>
</head>

<body class="menu7">
<!-- wrap starts here -->
<div id="wrap">
    <!--header -->
    <?php include('../Layout/header.php'); ?>
    
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
        
    <!-- content-wrap starts here -->
    <div id="content-wrap">
        <div id="main">
            <div class="messagebox" style="display: none"></div>

            <div class="admin-section">
                <h2>Post Announcement</h2>
                <input maxlength="400" class="announcementform" type="text" placeholder="Type announcement and press Enter to post"/>
            </div>

            <div class="admin-section">
                <h2>View Code</h2>
                <div class="code-view-section">
                    <label>Username:</label>
                    <select id="uname">
                        <?php
                            $cn = mysqli_connect('localhost', $DBUSER, $DBPASS, $DBNAME);
                            if (!$cn) {
                                die("Connection failed: " . mysqli_connect_error());
                            }

                            $result = mysqli_query($cn, "SELECT username FROM users");

                            while($row = mysqli_fetch_array($result))
                                print "<option value='".htmlspecialchars($row[0])."'>".htmlspecialchars($row[0])."</option>";

                            mysqli_close($cn);
                        ?>
                    </select>

                    <label>Problem:</label>
                    <select id="problemid">
                        <?php
                            for($i=1; $i <= count($points); $i++)
                                print "<option value='$i'>$i</option>";
                        ?>
                    </select>

                    <label>Filename:</label>
                    <select id="filename">
                    </select>

                    <button id="viewcodebutton">View</button>
                </div>

                <pre id="codeplaceholder" style="display: none">
                </pre>
            </div>
        </div>
        
        <div id="sidebar">
            <h3>Admin Menu</h3>
            <ul class="sidemenu">
                <li><a href="problems.php">Add Problems</a></li>
                <li><a href="delete-problems.php">Delete Problems</a></li>
                <li><a href="users.php">Manage Users</a></li>
                <li><a href="setting.php">Contest Settings</a></li>
            </ul>

            <?php include('../sidebar.php'); ?>    
        </div>
    
    <!-- content-wrap ends here -->    
    </div>
                
    <!--footer starts here-->
    <div id="footer">
        <?php include('../Layout/footer.php'); ?>
    </div>    

<!-- wrap ends here -->
</div>

</body>
</html>