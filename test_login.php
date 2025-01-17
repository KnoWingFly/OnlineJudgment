<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Replace with your WSL IP
$WSL_IP = '172.17.0.1'; // Use the IP you found earlier
$DBUSER = 'root';
$DBPASS = 'your_password'; // The password you set in step 4
$DBNAME = 'onj';
$WSL_PORT = 3306;

echo "Attempting to connect to MariaDB...<br>";
echo "IP: $WSL_IP<br>";
echo "Port: $WSL_PORT<br>";
echo "Database: $DBNAME<br><br>";

try {
    $conn = mysqli_connect($WSL_IP, $DBUSER, $DBPASS, $DBNAME, $WSL_PORT);
    
    if (!$conn) {
        throw new Exception(mysqli_connect_error());
    }
    
    echo "Successfully connected to MariaDB!<br>";
    echo "Server Info: " . mysqli_get_server_info($conn);
    
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>