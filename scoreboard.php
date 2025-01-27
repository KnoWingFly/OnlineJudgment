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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programming Contest - Scoreboard</title>
    
    <!-- Include Tailwind CSS from CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom styles -->
    <style>
        /* Any additional custom styles if needed */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>

    <script src="jquery-1.3.1.js"></script>
    <?php include('timer.php'); ?>
    
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
        getLeaders();
        getDetails();
        setInterval("dispTime()", 1000);  
        setInterval("getScores()", <?php echo $getLeaderInterval; ?>);  
        setInterval("getLeaders()", <?php echo $getLeaderInterval; ?>);  
        setInterval("getDetails()", <?php echo $getLeaderInterval; ?>);  
    });
    </script>
</head>

<body class="bg-black text-white">
    <div class="min-h-screen">
        <!-- Header -->
        <?php include('Layout/header.php'); ?>
        
        <!-- Menu -->
        <?php include('Layout/menu.php'); ?>
        
        <!-- Main Content -->
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-2xl font-semibold text-center mb-8">User</h1>
            
            <div class="w-full overflow-x-auto shadow-lg rounded-lg">
                <table id="scores" class="w-full min-w-full fade-in">
                    <!-- Table content will be loaded by getscores.php -->
                </table>
            </div>
        </div>
        
        <!-- Footer -->
    </div>
</body>
</html>