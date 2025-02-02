<?php
/*
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

session_start();
if(!isset($_SESSION['isloggedin'])) {
    echo "<meta http-equiv='Refresh' content='0; URL=login.php' />";
    exit(0);
} else {
    $username = $_SESSION['username'];
    $userid = $_SESSION['userid'];
}

include('settings.php');

// Check scoreboard visibility
include('scoreboard_settings.php');
if (!$scoreboardEnabled && !isset($_SESSION['admin'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Programming Contest - Scoreboard Disabled</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
            body {
                font-family: 'Inter', sans-serif;
            }
        </style>
    </head>
    <body class="bg-gray-950 text-white">
        <?php include('Layout/header.php'); ?>
        <?php include('Layout/menu.php'); ?>
        
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="max-w-2xl mx-auto p-8 text-center">
                <div class="mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold mb-4">Scoreboard Currently Disabled</h1>
                <p class="text-gray-400 text-lg">The contest administrators have temporarily disabled the scoreboard. Please check back later.</p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programming Contest - Scoreboard</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <?php include('timer.php'); ?>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
    
    <script>
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
        setInterval("dispTime()", 1000);  
        setInterval("getScores()", <?php echo $getLeaderInterval; ?>);
    });
    </script>
</head>

<body class="bg-gray-950 text-white">
    <div class="min-h-screen">
        <?php include('Layout/header.php'); ?>
        <?php include('Layout/menu.php'); ?>
        
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-2xl font-semibold text-center mb-8">Scoreboard</h1>
            
            <div class="w-full overflow-x-auto shadow-lg rounded-lg bg-gray-900">
                <table id="scores" class="w-full min-w-full fade-in">
                    <!-- Table content will be loaded by getscores.php -->
                </table>
            </div>
        </div>
    </div>
</body>
</html>