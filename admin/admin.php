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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programming Contest - Admin Panel</title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-emerald {
            background: linear-gradient(135deg, rgb(6 78 59) 0%, rgb(19 78 74) 100%);
        }

        .gradient-rose {
            background: linear-gradient(135deg, rgb(71 16 28) 0%, rgb(127 29 29) 100%);
        }

        .gradient-violet {
            background: linear-gradient(135deg, rgb(46 16 101) 0%, rgb(88 28 135) 100%);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: scale(1.02);
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
        <h1 class="text-3xl font-bold text-center mb-8 text-white">Admin Panel</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <!-- Add Problem Card -->
            <a href="problems.php"
                class="block rounded-xl p-6 gradient-emerald card-hover border border-gray-800 shadow-xl">
                <div class="text-center">
                    <div class="flex justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-xl mb-2 text-white">Add Problem</h3>
                    <p class="text-sm text-gray-300">Create and edit contest problems</p>
                </div>
            </a>

            <!-- Delete Problems Card -->
            <a href="delete-problems.php"
                class="block rounded-xl p-6 gradient-rose card-hover border border-gray-800 shadow-xl">
                <div class="text-center">
                    <div class="flex justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-rose-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-xl mb-2 text-white">Delete Problems</h3>
                    <p class="text-sm text-gray-300">Remove existing problems</p>
                </div>
            </a>

            <!-- System Settings Card -->
            <a href="setting.php"
                class="block rounded-xl p-6 gradient-violet card-hover border border-gray-800 shadow-xl">
                <div class="text-center">
                    <div class="flex justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-violet-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-xl mb-2 text-white">System Settings</h3>
                    <p class="text-sm text-gray-300">Configure contest parameters</p>
                </div>
            </a>
        </div>

        <!-- Server Status -->
        <div class="max-w-md mx-auto bg-gray-900 rounded-xl border border-gray-800 shadow-lg p-6">
            <div class="flex items-center justify-center gap-3 text-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm">Contest Server Time:</span>
                <span id="timedisplay" class="text-blue-400 time-display"></span>
            </div>
            <div class="text-xs text-gray-500 text-center mt-2">
                Database: <?php echo htmlspecialchars($DBNAME); ?>
            </div>
        </div>
    </div>

    <script>
        function dispTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString();
            document.getElementById('timedisplay').textContent = timeStr;
        }

        // Update time immediately and then every second
        dispTime();
        setInterval(dispTime, 1000);
    </script>
</body>

</html>