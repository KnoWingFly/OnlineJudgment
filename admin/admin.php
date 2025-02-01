<?php
/*
 * @copyright (c) 2008 Nicolo John Davis
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

session_start();
if (!isset($_SESSION['isloggedin'])) {
    echo "<meta http-equiv='Refresh' content='0; URL=../login.php' />";
    exit(0);
}

if ($_SESSION['admin'] != true) {
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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
        }

        .code-font {
            font-family: 'Fira Code', monospace;
        }
    </style>

    <?php include('../timer.php'); ?>

    <script type="text/javascript">
        $(document).ready(function () {
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
            $(".announcementform").keypress(function (e) {
                if (e.which == 13) {
                    var msg = this.value;
                    this.value = "";

                    if (msg != "") {
                        $.post("submitannouncement.php", { message: msg }, function () {
                            messagebox('Announcement posted');
                        });
                    }
                }
            });

            // Code viewing functionality
            $("#uname, #problemid").change(function () {
                var uname = $("#uname").val();
                var id = $("#problemid").val();

                $.get("getcode.php", { username: uname, problemid: id, mode: 'getdir' }, function (data) {
                    $("#filename").html(data);
                });
            });

            // Initially populate filename box
            $.get("getcode.php", { username: 'admin', problemid: '1', mode: 'getdir' }, function (data) {
                $("#filename").html(data);
            });

            $("#viewcodebutton").click(function () {
                var uname = $("#uname").val();
                var id = $("#problemid").val();
                var file = $("#filename").val();

                $.get("getcode.php", { username: uname, problemid: id, filename: file }, function (data) {
                    $("#codeplaceholder").html(data).fadeIn("fast");
                });
            });
        });
    </script>
</head>


<body class="bg-slate-900 text-slate-200">
    <!-- Header -->
    <header class="bg-slate-800 border-b border-slate-700">
        <?php include('../Layout/header.php'); ?>
        <?php include('../Layout/Menu.php'); ?>
    </header>

    <div class="flex">
        <!-- Enhanced Sidebar -->
        <nav class="w-64 h-screen bg-slate-800 border-r border-slate-700 sticky top-0">
            <div class="p-4 border-b border-slate-700">
                <h2 class="text-lg font-bold text-blue-400">Admin Panel</h2>
                <p class="text-sm text-slate-400 mt-1">Management Console</p>
            </div>

            <div class="p-2 space-y-1">
                <a href="problems.php"
                    class="flex items-center p-3 rounded-lg hover:bg-slate-700/50 transition-colors group">
                    <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Problems
                </a>

                <a href="delete-problems.php"
                    class="flex items-center p-3 rounded-lg hover:bg-slate-700/50 transition-colors group">
                    <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete Problems
                </a>

                <a href="users.php"
                    class="flex items-center p-3 rounded-lg hover:bg-slate-700/50 transition-colors group">
                    <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Users
                </a>

                <a href="setting.php"
                    class="flex items-center p-3 rounded-lg hover:bg-slate-700/50 transition-colors group">
                    <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c-.94 1.543.826 3.31 2.37 2.37a1.724 1.724 0 002.572 1.065c.426 1.756 2.924 1.756 3.35 0a1.724 1.724 0 002.573-1.066c1.543.94 3.31-.826 2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <!-- Announcement Card -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6 border border-slate-700">
                <h2 class="text-lg font-semibold mb-4 text-blue-400">Post Announcement</h2>
                <input type="text" maxlength="400"
                    class="w-full p-3 bg-slate-900 border border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                    placeholder="Type announcement and press Enter...">
            </div>

            <!-- Code Viewer Card -->
            <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                <h2 class="text-lg font-semibold mb-4 text-blue-400">Code Viewer</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Username</label>
                        <select id="uname"
                            class="w-full p-2 bg-slate-900 border border-slate-700 rounded-lg text-slate-200">
                            <?php
                            $cn = mysqli_connect('localhost', $DBUSER, $DBPASS, $DBNAME);
                            if ($cn) {
                                $result = mysqli_query($cn, "SELECT username FROM users");
                                while ($row = mysqli_fetch_array($result)) {
                                    echo "<option value='" . htmlspecialchars($row[0]) . "'>" . htmlspecialchars($row[0]) . "</option>";
                                }
                                mysqli_close($cn);
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Other form elements -->
                </div>

                <!-- Code Display -->
                <pre id="codeplaceholder"
                    class="bg-slate-900 text-slate-100 p-4 rounded-lg border border-slate-700 overflow-x-auto code-font text-sm"></pre>
            </div>
        </main>
    </div>
</body>

</html>