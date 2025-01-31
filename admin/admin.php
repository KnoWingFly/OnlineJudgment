<?php
/*
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

session_start();
if(!isset($_SESSION['isloggedin'])) {
    echo "<meta http-equiv='Refresh' content='0; URL=../login.php' />";
    exit(0);
}

if($_SESSION['admin'] != true) {
    print "You need to be the administrator to access this file";
    exit(0);
}

$username = $_SESSION['username'];
$userid = $_SESSION['userid'];

include('../settings.php');
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta name="Keywords" content="programming, contest, coding, judge" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="Distribution" content="Global" />
    <meta name="Robots" content="index,follow" />
    <title>Programming Contest - Admin Panel</title>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;700&family=Inter:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: #000000;
            color: #FFFFFF;
        }
        
        .code-font { font-family: 'IBM Plex Mono', monospace; }
        
        #codeplaceholder {
            @apply bg-[#0A0A0A] text-gray-200 p-4 rounded-lg border border-[#1A1A1A] overflow-x-auto code-font text-sm;
        }
    </style>
    
    <?php include('../timer.php'); ?>
    
    <script type="text/javascript">
    $(document).ready(function() { 
        // Ensure the page is scrolled to top on reload
        $(window).scrollTop(0);
        
        dispTime();
        getLeaders();
        getDetails();
        setInterval("dispTime()", 1000);  
        setInterval("getLeaders()", getLeaderInterval);  
        setInterval("getDetails()", getLeaderInterval);  

        // Initialize announcement form
        $(".announcementform").focus();
        $(".announcementform").keypress(function(e) {
            if(e.which == 13) {
                var msg = this.value;
                this.value = "";

                if(msg != "") {
                    $.post("submitannouncement.php", { message: msg }, function(){
                        messagebox('Announcement posted');
                    });
                }
            }
        });

        // Code viewing functionality
        $("#uname, #problemid").change(function() {
            var uname = $("#uname").val();
            var id = $("#problemid").val();

            $.get("getcode.php", {username: uname, problemid: id, mode: 'getdir'}, function(data) { 
                $("#filename").html(data); 
            });
        });
                
        // Initially populate filename box
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
    });
    </script>
</head>

<body class="bg-black text-gray-200">
    <!-- Header and Menu -->
    <?php include('../Layout/header.php'); ?>
    <?php include('../Layout/Menu.php'); ?>

    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-[#1A1A1A] min-h-screen">
            <div class="sticky top-0 p-6">
                <h3 class="text-xl font-bold text-[#0736FF] mb-4">Admin Menu</h3>
                <nav class="space-y-2">
                    <a href="problems.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-[#0A0A0A] rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Manage Problems
                    </a>
                    <a href="delete-problems.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-[#0A0A0A] rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Problems
                    </a>
                    <a href="users.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-[#0A0A0A] rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Manage Users
                    </a>
                    <a href="setting.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-[#0A0A0A] rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Contest Settings
                    </a>
                </nav>
                
                <?php include('../sidebar.php'); ?>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="max-w-6xl mx-auto space-y-6">
                <!-- Announcement Section -->
                <div class="bg-[#1A1A1A] p-6 rounded-xl border border-[#2A2A2A]">
                    <h2 class="text-2xl font-bold text-[#0736FF] mb-4">Post Announcement</h2>
                    <input type="text" maxlength="400" 
                           class="announcementform w-full bg-[#0A0A0A] border border-[#2A2A2A] text-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#0736FF] focus:border-[#0736FF] outline-none"
                           placeholder="Type announcement and press Enter to post">
                </div>

                <!-- Code View Section -->
                <div class="bg-[#1A1A1A] p-6 rounded-xl border border-[#2A2A2A]">
                    <h2 class="text-2xl font-bold text-[#0736FF] mb-6">View Code</h2>
                    
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-gray-400 mb-2">Username:</label>
                            <select id="uname" class="w-full bg-[#0A0A0A] border border-[#2A2A2A] text-gray-200 rounded-lg px-4 py-2">
                                <?php
                                $cn = mysqli_connect('localhost', $DBUSER, $DBPASS, $DBNAME);
                                if ($cn) {
                                    $result = mysqli_query($cn, "SELECT username FROM users");
                                    while($row = mysqli_fetch_array($result)) {
                                        echo "<option value='".htmlspecialchars($row[0])."'>".htmlspecialchars($row[0])."</option>";
                                    }
                                    mysqli_close($cn);
                                }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-400 mb-2">Problem:</label>
                            <select id="problemid" class="w-full bg-[#0A0A0A] border border-[#2A2A2A] text-gray-200 rounded-lg px-4 py-2">
                                <?php for($i=1; $i <= count($points); $i++) echo "<option value='$i'>$i</option>"; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-400 mb-2">Filename:</label>
                            <select id="filename" class="w-full bg-[#0A0A0A] border border-[#2A2A2A] text-gray-200 rounded-lg px-4 py-2"></select>
                        </div>
                    </div>

                    <button id="viewcodebutton" class="bg-[#0736FF] text-white px-6 py-3 rounded-lg hover:bg-[#062DBF] transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        View Code
                    </button>

                    <pre id="codeplaceholder" class="mt-6" style="display: none"></pre>
                </div>
            </div>
        </div>
    </div>

    <footer class="border-t border-[#2A2A2A] py-6">
        <?php include('../Layout/footer.php'); ?>
    </footer>
</body>
</html>