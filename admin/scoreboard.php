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

include('../settings.php');

// Read scoreboard state
$scoreboardEnabled = 1;
if (file_exists("../scoreboard_settings.php")) {
    include("../scoreboard_settings.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programming Contest - Scoreboard</title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .time-display {
            font-family: 'Courier New', monospace;
        }
    </style>
</head>

<body class="bg-gray-950 text-slate-200 min-h-screen">
    <?php include('../Layout/header.php'); ?>
    <?php include('../Layout/menu.php'); ?>

    <div class="container mx-auto p-6">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-white">Live Scoreboard</h1>
            
            <!-- Back to Admin Panel Button -->
            <a href="admin.php" class="flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                Back to Admin Panel
            </a>
        </div>

        <!-- Scoreboard Status Banner -->
        <?php if (!$scoreboardEnabled): ?>
        <div class="mb-6 p-4 bg-red-900 rounded-lg">
            <div class="flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>The scoreboard is currently disabled. Enable it from the admin panel to make it visible to contestants.</span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Server Time -->
        <div class="mb-6 p-4 bg-gray-900 rounded-lg border border-gray-800">
            <div class="flex items-center justify-center gap-3 text-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm">Contest Server Time:</span>
                <span id="timedisplay" class="text-blue-400 time-display"></span>
            </div>
        </div>

        <!-- Scoreboard Table -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <div id="scores" class="min-w-full">
                    <table class="w-full border-collapse">
                        <?php include('../getscores.php'); ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function dispTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString();
            document.getElementById('timedisplay').textContent = timeStr;
        }

        function getScores() {
            $.ajax({
                url: '../getscores.php',
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
            setInterval("dispTime()", 1000);
            setInterval("getScores()", <?php echo $getLeaderInterval; ?>);
        });
    </script>
</body>
</html>