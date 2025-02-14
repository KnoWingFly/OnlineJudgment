<?php
session_start();
include('settings.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

function cleanInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function dbConnect($DBUSER, $DBPASS, $DBNAME) {
    $mysqli = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);
    if ($mysqli->connect_error) {
        die("Database connection failed: " . $mysqli->connect_error);
    }
    return $mysqli;
}

function handleLogin($username, $password, $DBUSER, $DBPASS, $DBNAME) {
    $mysqli = dbConnect($DBUSER, $DBPASS, $DBNAME);

    $stmt = $mysqli->prepare("SELECT id, username, password, is_admin FROM users WHERE username = ?");
    if (!$stmt) {
        return ['error' => 'Query preparation failed'];
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $mysqli->close();

    if ($result && password_verify($password, $result['password'])) {
        return $result;
    }
    return null;
}

function handleRegister($username, $password, $firstname, $lastname, $college, $DBUSER, $DBPASS, $DBNAME) {
    $mysqli = dbConnect($DBUSER, $DBPASS, $DBNAME);

    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        $mysqli->close();
        return ['error' => 'Username already taken'];
    }
    $stmt->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare("INSERT INTO users (username, password, firstname, lastname, college) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $hashedPassword, $firstname, $lastname, $college);
    
    if ($stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        return ['success' => 'Registration successful! Redirecting to login...'];
    } else {
        $stmt->close();
        $mysqli->close();
        return ['error' => 'Registration failed. Please try again.'];
    }
}

$loginError = '';
$registerMessage = '';
$registerSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = cleanInput($_POST['username']);
    $password = cleanInput($_POST['password']);

    if (empty($username) || empty($password)) {
        $loginError = "Username and password are required";
    } else {
        $result = handleLogin($username, $password, $DBUSER, $DBPASS, $DBNAME);
        if (isset($result['error'])) {
            $loginError = "System error: Please try again later";
        } elseif ($result) {
            $_SESSION['isloggedin'] = true;
            $_SESSION['userid'] = $result['id'];
            $_SESSION['username'] = $result['username'];
            if ($result['is_admin']) {
                $_SESSION['admin'] = true;
            }
            header("Location: " . ($result['is_admin'] ? "admin/admin.php" : "index.php"));
            exit();
        } else {
            $loginError = "Invalid username or password";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = cleanInput($_POST['reg_username']);
    $password = cleanInput($_POST['reg_password']);
    $confirmPassword = cleanInput($_POST['confirm_password']);
    $firstname = cleanInput($_POST['first_name']);
    $lastname = cleanInput($_POST['last_name']);
    $college = cleanInput($_POST['college']);

    if (empty($username) || empty($password) || empty($confirmPassword) || empty($firstname) || empty($lastname) || empty($college)) {
        $registerMessage = "All fields are required!";
    } elseif ($password !== $confirmPassword) {
        $registerMessage = "Passwords do not match!";
    } else {
        $result = handleRegister($username, $password, $firstname, $lastname, $college, $DBUSER, $DBPASS, $DBNAME);
        if (isset($result['error'])) {
            $registerMessage = $result['error'];
        } else {
            $registerMessage = $result['success'];
            $registerSuccess = true;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UMN Programming Club</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-gray-800 p-6 rounded-lg shadow-md">
        <img src="images/UMNPC.png" class="h-56 justify-items-center mx-auto" alt="UMNPC Logo">

        <div id="loginSection">
            <h2 class="text-center text-xl font-bold mb-4">Login</h2>
            <?php if($loginError): ?>
                <div class="bg-red-500 text-white p-3 rounded text-center mb-4"> 
                    <?= htmlspecialchars($loginError); ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <input type="text" name="username" placeholder="Username" class="w-full p-2 mb-2 rounded bg-gray-700">
                <input type="password" name="password" placeholder="Password" class="w-full p-2 mb-2 rounded bg-gray-700">
                <button type="submit" name="login" class="w-full bg-purple-600 py-2 rounded">Login</button>
            </form>
            <!-- <p class="text-center mt-4">
                <a id="showRegister" class="text-purple-400 hover:underline cursor-pointer">Create an account</a>
            </p> -->
        </div>

        <!-- <div id="registerSection" style="display: none;">
            <h2 class="text-center text-xl font-bold mb-4">Register</h2>
            <?php if($registerMessage): ?>
                <div class="<?= $registerSuccess ? 'bg-green-500' : 'bg-red-500'; ?> text-white p-3 rounded text-center mb-4">
                    <?= htmlspecialchars($registerMessage); ?>
                </div>
                <?php if($registerSuccess): ?>
                    <script>
                        setTimeout(() => {
                            document.getElementById('showLogin').click();
                        }, 3000);
                    </script>
                <?php endif; ?>
            <?php endif; ?>

            <form method="post">
                <input type="text" name="first_name" placeholder="First Name" class="w-full p-2 mb-2 rounded bg-gray-700">
                <input type="text" name="last_name" placeholder="Last Name" class="w-full p-2 mb-2 rounded bg-gray-700">
                <input type="text" name="reg_username" placeholder="Username" class="w-full p-2 mb-2 rounded bg-gray-700">
                <input type="password" name="reg_password" placeholder="Password" class="w-full p-2 mb-2 rounded bg-gray-700">
                <input type="password" name="confirm_password" placeholder="Confirm Password" class="w-full p-2 mb-2 rounded bg-gray-700">
                <input type="text" name="college" placeholder="College" class="w-full p-2 mb-2 rounded bg-gray-700">
                <button type="submit" name="register" class="w-full bg-green-600 py-2 rounded">Register</button>
            </form>
            <p class="text-center mt-4">
                <a id="showLogin" class="text-purple-400 hover:underline cursor-pointer">Back to Login</a>
            </p>
        </div> -->
    </div>

    <script>
        document.getElementById('showRegister').onclick = () => {
            document.getElementById('loginSection').style.display = 'none';
            document.getElementById('registerSection').style.display = 'block';
        };
        document.getElementById('showLogin').onclick = () => {
            document.getElementById('registerSection').style.display = 'none';
            document.getElementById('loginSection').style.display = 'block';
        };
    </script>
</body>
</html>
