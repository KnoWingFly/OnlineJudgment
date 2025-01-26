<?php
/*
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

session_start();
include('settings.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="Keywords" content="programming, contest, coding, judge" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="Distribution" content="Global" />
    <meta name="Robots" content="index,follow" />
    <link rel="stylesheet" href="images/Envision.css" type="text/css" />
    <title>Programming Contest</title>
    <script src="jquery-1.3.1.js"></script>
    <?php include('timer.php'); ?>
    <script>
        $(document).ready(function() { 
            setInterval("dispTime()", 1000); 
            dispTime(); 

            $('form').submit(function() {
                const oldPass = $("input[name='old_pass']").val();
                const newPass = $("input[name='new_pass']").val();
                const confPass = $("input[name='conf_new_pass']").val();

                $('#error').hide();

                if (oldPass.length === 0) {
                    $('#error').text('Old Password field cannot be left blank');
                    $('#error').attr('class', 'error');
                    $('#error').fadeIn('slow');
                    return false;
                }
                if (newPass.length === 0) {
                    $('#error').text('New Password field cannot be left blank');
                    $('#error').attr('class', 'error');
                    $('#error').fadeIn('slow');
                    return false;
                }
                if (confPass.length === 0) {
                    $('#error').text('Confirm Password field cannot be left blank');
                    $('#error').attr('class', 'error');
                    $('#error').fadeIn('slow');
                    return false;
                }
                if (newPass !== confPass) {
                    $('#error').text('New Password and Confirm Password do not match');
                    $('#error').attr('class', 'error');
                    $('#error').fadeIn('slow');
                    return false;
                }
            });
        });
    </script>
</head>
<body class="menu1">
<div id="wrap">
    <?php include('Layout/header.php'); ?>
    <?php include('Layout/menu.php'); ?>

    <div id="content-wrap">
        <div id="main">
            <?php
            if (isset($_SESSION['isloggedin']) && $_SESSION['isloggedin'] === "Yes" && isset($_POST['old_pass'])) {
                $mysqli = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);

                if ($mysqli->connect_error) {
                    die('<div class="error">Database connection failed: ' . $mysqli->connect_error . '</div>');
                }

                $id = $_SESSION['userid'];
                $oldPassword = $_POST['old_pass'];

                $query = "SELECT password FROM users WHERE id = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($hashedPassword);
                $stmt->fetch();
                $stmt->close();

                if (password_verify($oldPassword, $hashedPassword)) {
                    $newPassword = $_POST['new_pass'];
                    $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                    $updateQuery = "UPDATE users SET password = ? WHERE id = ?";
                    $updateStmt = $mysqli->prepare($updateQuery);
                    $updateStmt->bind_param("si", $hashedNewPassword, $id);

                    if ($updateStmt->execute()) {
                        echo '<div id="error" class="success">Password changed successfully</div>';
                    } else {
                        echo '<div id="error" class="error">Error changing password</div>';
                    }

                    $updateStmt->close();
                } else {
                    echo '<div id="error" class="error">Old password is incorrect</div>';
                }

                $mysqli->close();
            }
            ?>
            <form style="position: relative; margin-left: auto; margin-right: auto; width: 250px; background-color: #ECF1EF;" action="personal.php" method="post">
                <p>
                    <label>Old Password</label>
                    <input name="old_pass" value="" type="password" size="30" />
                    <label>New Password</label>
                    <input name="new_pass" value="" type="password" size="30" />
                    <label>Confirm New Password</label>
                    <input name="conf_new_pass" value="" type="password" size="30" />
                    <br />
                    <div style="position: relative; width: 100px; text-align: center; margin-left: auto; margin-right: auto; margin-bottom: 10px">
                        <input id="loginbutton" class="button" type="submit" name="change_password" value="Change Password" />
                    </div>
                </p>
            </form>
        </div>
        <div id="sidebar">
            <h3 id="timeheading"></h3>
            <ul class="sidemenu">
                <li id="time"></li>
            </ul>
        </div>
    </div>
    <div id="footer">
        <?php include('Layout/footer.php'); ?>
    </div>
</div>
</body>
</html>
