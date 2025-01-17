<?php

/*
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

session_start();
if (!isset($_SESSION['isloggedin'])) {
    echo "<meta http-equiv='Refresh' content='0; URL=../login.php' />";
    exit(0);
} else {
    $username = $_SESSION['username'];
    $userid = $_SESSION['userid'];
}

include('../settings.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection using mysqli
$cn = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);
if ($cn->connect_error) {
    die("Database connection failed: " . $cn->connect_error);
}

// Get the list of problem folders dynamically
$problemDir = "../problems";
$problems = array_filter(glob("$problemDir/*"), 'is_dir');
$problemCount = count($problems);

// Initialize arrays to store stats
$cor = array_fill(1, $problemCount, 0);
$tot = array_fill(1, $problemCount, 0);

// Fetch submissions and compute stats
$query = 'SELECT problemid, status FROM submissions';
$result = $cn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $problemId = (int)$row['problemid'];
        if (isset($cor[$problemId])) {
            if ($row['status'] == 0) {
                $cor[$problemId]++;
            }
            $tot[$problemId]++;
        }
    }
}

// Generate table
echo '<table style="margin-top: 10px;">';
echo '<tr><th>Problem</th><th>Correct</th><th>Total</th></tr>';

$class = 'row-a';
for ($i = 1; $i <= $problemCount; $i++) {
    echo "<tr class='$class'><td><strong>$i</strong></td><td>{$cor[$i]}</td><td>{$tot[$i]}</td></tr>";
    $class = ($class === "row-a") ? "row-b" : "row-a";
}
echo '</table>';

// Close database connection
$cn->close();

?>
