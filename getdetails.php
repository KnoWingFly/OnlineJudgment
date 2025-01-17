<?php
// getdetails.php
/*
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

session_start();
if (!isset($_SESSION['isloggedin'])) {
    http_response_code(403);
    exit('Not logged in');
}

$username = $_SESSION['username'];
$userid = $_SESSION['userid'];

require_once('settings.php');

try {
    // Create connection using mysqli
    $conn = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    print '<ul class="sidemenu">';
    print "<li style='position: relative'>";
    print "<span>" . htmlspecialchars($username) . "</span>";

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT score FROM users WHERE id = ? LIMIT 1");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        print "<span style='position: absolute; right: 0px'>" . 
              htmlspecialchars($row['score']) . 
              "</span>";
    }

    print "</li></ul>";

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    error_log("Error in getdetails.php: " . $e->getMessage());
    http_response_code(500);
    exit("An error occurred");
}
?>