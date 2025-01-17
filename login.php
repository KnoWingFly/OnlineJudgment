<?php
session_start();
include('settings.php');

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to clean input data
function cleanInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to safely connect to database
function dbConnect($DBUSER, $DBPASS, $DBNAME) {
    $mysqli = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);
    if ($mysqli->connect_error) {
        return false;
    }
    return $mysqli;
}

// Handle Login with prepared statements
function handleLogin($username, $password, $DBUSER, $DBPASS, $DBNAME) {
    $mysqli = dbConnect($DBUSER, $DBPASS, $DBNAME);
    if (!$mysqli) {
        return array('error' => 'Database connection failed');
    }
    
    // Query to check login and is_admin status
    $query = "SELECT id, username, password, is_admin FROM users WHERE username = ? AND password = ?";
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        $mysqli->close();
        return array('error' => 'Query preparation failed');
    }
    
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $stmt->close();
    $mysqli->close();
    
    return $user;
}

$loginError = '';
$registrationMessage = '';

// Check if already logged in
if(isset($_SESSION['isloggedin']) && $_SESSION['isloggedin'] == "Yes") {
    // Check if user is admin and redirect accordingly
    if(isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
        header("Location: admin/admin.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

// Handle Login Form Submission
if(isset($_POST['login']) && isset($_POST['username']) && isset($_POST['password'])) {
    $username = cleanInput($_POST['username']);
    $password = cleanInput($_POST['password']);
    
    if(empty($username) || empty($password)) {
        $loginError = "Username and password are required";
    } else {
        $result = handleLogin($username, $password, $DBUSER, $DBPASS, $DBNAME);
        
        if (isset($result['error'])) {
            $loginError = "System error: Please try again later";
        } 
        elseif ($result) {
            // Set session variables
            $_SESSION['isloggedin'] = "Yes";
            $_SESSION['userid'] = $result['id'];
            $_SESSION['username'] = $username;
            
            // Set admin privileges if is_admin is 1 and redirect accordingly
            if($result['is_admin'] == 1) {
                $_SESSION['admin'] = true;
                header("Location: admin/admin.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $loginError = "Invalid username or password";
        }
    }
}

// Handle Registration
if(isset($_POST['register'])) {
    $mysqli = dbConnect($DBUSER, $DBPASS, $DBNAME);
    if (!$mysqli) {
        $registrationMessage = '<div class="error">Database connection failed</div>';
    } else {
        $username = cleanInput($_POST['username']);
        $password = cleanInput($_POST['password']);
        $firstname = cleanInput($_POST['firstname']);
        $lastname = cleanInput($_POST['lastname']);
        $college = cleanInput($_POST['college']);
        
        if(strlen($username) < 2) {
            $registrationMessage = '<div class="error">Username must be at least 2 characters long</div>';
        } else {
            // New users are registered with is_admin = 0
            $query = "INSERT INTO users (username, password, firstname, lastname, college, is_admin) VALUES (?, ?, ?, ?, ?, 0)";
            $stmt = $mysqli->prepare($query);
            
            if($stmt) {
                $stmt->bind_param("sssss", $username, $password, $firstname, $lastname, $college);
                
                if(!$stmt->execute()) {
                    if($mysqli->errno == 1062) {
                        $registrationMessage = '<div class="error">Username already taken</div>';
                    } else {
                        $registrationMessage = '<div class="error">Error creating account</div>';
                    }
                } else {
                    $registrationMessage = '<div class="success">Account created successfully</div>';
                }
                $stmt->close();
            }
        }
        $mysqli->close();
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta name="Keywords" content="programming, contest, coding, judge" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="Distribution" content="Global" />
    <meta name="Robots" content="index,follow" />
    <link rel="stylesheet" href="images/Envision.css" type="text/css" />
    <title>Programming Contest - Login</title>
    <script type="text/javascript" src="jquery-1.3.1.js"></script>
    <?php include('timer.php'); ?>
    <style type="text/css">
        .error { 
            color: red; 
            background: #ffebee;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ffcdd2;
            border-radius: 4px;
        }
        .success { 
            color: green; 
            background: #e8f5e9;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #a5d6a7;
            border-radius: 4px;
        }
    </style>
</head>

<body class="menu1">
<div id="wrap">
    <?php include('header.php'); ?>
    <?php include('menu.php'); ?>
    
    <div id="content-wrap">
        <div id="main">
            <?php 
            if($loginError) {
                echo '<div id="error" class="error">' . htmlspecialchars($loginError) . '</div>';
            }
            if($registrationMessage) {
                echo $registrationMessage;
            }
            ?>
            
            <form style="position: relative; margin-left: auto; margin-right: auto; width: 250px; background-color: #ECF1EF;" action="login.php" method="post">
                <p>
                <label>Username</label>
                <input name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" type="text" size="30" />
                
                <label>Password</label>
                <input name="password" value="" type="password" size="30" />

                <div id="registerfields" style="display: none">
                    <label>Confirm Password</label>
                    <input name="confirm" value="" type="password" size="30" />
                    
                    <label>First Name</label>
                    <input name="firstname" value="<?php echo isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : ''; ?>" type="text" size="30" />
                    
                    <label>Last Name</label>
                    <input name="lastname" value="<?php echo isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : ''; ?>" type="text" size="30" />
                    
                    <label>College</label>
                    <input name="college" value="<?php echo isset($_POST['college']) ? htmlspecialchars($_POST['college']) : ''; ?>" type="text" size="30" />
                </div>

                <div style="text-align: center; margin: 10px 0;">
                    <input id="loginbutton" class="button" type="submit" name="login" value="Login" />
                </div>
                </p>
            </form>

            <div style="text-align: center; margin: 10px 0;">
                <a id="register" style="cursor: pointer;">Create an account</a>
            </div>
        </div>

        <div id="sidebar">
            <h3 id="timeheading"></h3>
            <ul class="sidemenu">
                <li id="time"></li>
            </ul>
        </div>
    </div>
    
    <div id="footer">
        <?php include('footer.php'); ?>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(){ 
    if (typeof dispTime === "function") {
        setInterval("dispTime()", 1000); 
        dispTime(); 
    }

    $('#register').click(function() {
        var $registerFields = $('#registerfields');
        var $loginButton = $('#loginbutton');
        var $register = $('#register');
        
        if($registerFields.is(':hidden')) {
            $registerFields.slideDown('fast');
            $register.text('Cancel');
            $loginButton.val('Register');
            $loginButton.attr('name', 'register');
        } else {
            $registerFields.slideUp('fast');
            $register.text('Create an account');
            $loginButton.val('Login');
            $loginButton.attr('name', 'login');
        }
        $('#error').hide();
    });

    $('form').submit(function(e) {
        var $error = $('#error');
        var username = $("input[name='username']").val();
        var password = $("input[name='password']").val();
        
        $error.hide();

        if(!username) {
            $error.text('Username field cannot be left blank').attr('class', 'error').fadeIn('slow');
            e.preventDefault();
            return false;
        }
        if(!password) {
            $error.text('Password field cannot be left blank').attr('class', 'error').fadeIn('slow');
            e.preventDefault();
            return false;
        }

        if($('#loginbutton').attr('name') === 'register') {
            var confirmpass = $("input[name='confirm']").val();
            var firstname = $("input[name='firstname']").val();
            var lastname = $("input[name='lastname']").val();
            var college = $("input[name='college']").val();
            
            if(password !== confirmpass) {
                $error.text('Passwords entered do not match').attr('class', 'error').fadeIn('slow');
                e.preventDefault();
                return false;
            }
            if(!firstname) {
                $error.text('First name missing').attr('class', 'error').fadeIn('slow');
                e.preventDefault();
                return false;
            }
            if(!lastname) {
                $error.text('Last name missing').attr('class', 'error').fadeIn('slow');
                e.preventDefault();
                return false;
            }
            if(!college) {
                $error.text('College name missing').attr('class', 'error').fadeIn('slow');
                e.preventDefault();
                return false;
            }
        }
    });
});
</script>

</body>
</html>