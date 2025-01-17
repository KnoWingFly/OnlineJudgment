<?php
// getleaders.php
/*
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

session_start();
if (!isset($_SESSION['isloggedin'])) {
    http_response_code(403);
    exit('Not logged in');
}

require_once('settings.php');

try {
    // Create connection using mysqli
    $conn = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Getting top 5 scores from users table
    $stmt = $conn->prepare("SELECT username, score FROM users WHERE score > 0 ORDER BY score DESC LIMIT 5");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    print '<ul class="sidemenu">';
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            print "<li style='position: relative'>" .
                  "<span>" . htmlspecialchars($row['username']) . "</span>" .
                  "<span style='position: absolute; right: 0px'>" . 
                  htmlspecialchars($row['score']) . 
                  "</span></li>";
        }
    } else {
        print "<li>No high scores yet!</li>";
    }
    print "</ul>";

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    error_log("Error in getleaders.php: " . $e->getMessage());
    http_response_code(500);
    exit("An error occurred");
}
?>