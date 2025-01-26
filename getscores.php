<?php
include('settings.php');

// Create connection using MySQLi
$conn = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch problem points from the database
$points = array();
$pointsQuery = "SELECT id, points FROM problems ORDER BY id";
$pointsResult = $conn->query($pointsQuery);

if (!$pointsResult) {
    die("Failed to fetch problem points: " . $conn->error);
}

while($pointRow = $pointsResult->fetch_assoc()) {
    $points[$pointRow['id'] - 1] = intval($pointRow['points']);
}

$nProb = count($points);
$dataSub = array();
$idmaps = array();
$ctr = 0;

// Get all users
$query = "SELECT id, username FROM users";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

while($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $name = $row['username'];
    $idmaps[$id] = $ctr;
    
    $dataSub[$ctr] = array();
    $dataSub[$ctr]['username'] = $name;
    $dataSub[$ctr]['score'] = 0;
    $dataSub[$ctr]['last_submission'] = 0;
    for($i=1; $i<=$nProb; $i++) {
        $dataSub[$ctr]['tries_'.$i] = 0;
        $dataSub[$ctr]['solved_'.$i] = false;
        $dataSub[$ctr]['last_status_'.$i] = null;
        $dataSub[$ctr]['submissions_'.$i] = array();
    }
    $ctr++;
}

// Get all submissions ordered by time
$query = "SELECT userid, problemid, status, time 
          FROM submissions 
          INNER JOIN users ON users.id = submissions.userid 
          ORDER BY time ASC";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

while($row = $result->fetch_assoc()) {
    $id = $row['userid'];
    $probid = $row['problemid'];
    $stat = intval($row['status']); // Ensure status is an integer
    $time = intval($row['time']);   // Ensure time is an integer
    
    if (isset($idmaps[$id])) {
        $ctr = $idmaps[$id];
        
        if ($time > $dataSub[$ctr]['last_submission']) {
            $dataSub[$ctr]['last_submission'] = $time;
        }
        
        // Track submission
        $dataSub[$ctr]['tries_'.$probid]++;
        $dataSub[$ctr]['last_status_'.$probid] = $stat;
        
        // Update score if correct
        if ($stat == 0 && !$dataSub[$ctr]['solved_'.$probid]) {
            $dataSub[$ctr]['solved_'.$probid] = true;
            $dataSub[$ctr]['score'] += $points[$probid-1];
        }
    }
}

// Sort teams by score (descending) and last submission time (ascending)
usort($dataSub, function($a, $b) {
    if ($a['score'] != $b['score']) {
        return $b['score'] - $a['score'];
    }
    return $a['last_submission'] - $b['last_submission'];
});

$class = "row-a";

// Output table header
print '<tr><th>Position</th><th>User</th>';
for($i=1; $i<=count($points); $i++) {
    print "<th>" . htmlspecialchars($i) . "</th>";
}
print '<th>Score</th><th>Last Update</th></tr>';

// Set timezone to match your server's timezone
date_default_timezone_set('Asia/Jakarta'); // Adjust this to your timezone

// Output data rows
for($i=0; $i<count($dataSub); $i++) {
    $rankNow = $i+1;
    $name = htmlspecialchars($dataSub[$i]['username']);
    
    print "<tr ";
    if(isset($username) && $dataSub[$i]['username'] == $username) {
        print "style='font-weight: bold;' ";
    }
    print "class='" . htmlspecialchars($class) . "'><td>" . $rankNow . "</td><td>" . $name . "</td>";

    for($j=1; $j<=$nProb; $j++) {
        $tries = $dataSub[$i]['tries_'.$j];
        $status = intval($dataSub[$i]['last_status_'.$j]); // Ensure status is an integer
        $solved = $dataSub[$i]['solved_'.$j];
        
        if ($tries == 0) {
            // No attempts - default color
            print "<td class='problem-cell' style='background-color:#f5f5f5;'>--</td>";
        } else {
            // Define status colors and messages
            $statusColor = '#f5f5f5'; // Default light gray
            $statusMessage = "-- ($tries)";
            $statusTitle = 'Unknown Status';
            
            switch($status) {
                case 0: // Accepted
                    $statusColor = '#90EE90';
                    $statusMessage = "✓ ($tries)";
                    $statusTitle = 'Solved correctly';
                    break;
                case 1: // Compile Error
                    $statusColor = '#FFB6C1';
                    $statusMessage = "CE ($tries)";
                    $statusTitle = 'Compile Error';
                    break;
                case 2: // Wrong Answer
                    $statusColor = '#FFB6C1';
                    $statusMessage = "✗ ($tries)";
                    $statusTitle = 'Wrong Answer';
                    break;
                case 3: // Time Limit Exceeded
                    $statusColor = '#FFB6C1';
                    $statusMessage = "TL ($tries)";
                    $statusTitle = 'Time Limit Exceeded';
                    break;
                case 5: // Runtime Error
                    $statusColor = '#FFB6C1';
                    $statusMessage = "RE ($tries)";
                    $statusTitle = 'Runtime Error';
                    break;
            }
            
            print "<td class='problem-cell' style='background-color:" . $statusColor . ";' title='" . 
                  htmlspecialchars($statusTitle) . "'>" . 
                  htmlspecialchars($statusMessage) . "</td>";
        }
    }
    
    $score = htmlspecialchars($dataSub[$i]['score']);
    // Format the last update time using the server's timezone
    $last_update = date('H:i:s', $dataSub[$i]['last_submission']);
    print "<td>$score</td><td>$last_update</td></tr>";

    $class = ($class == "row-a") ? "row-b" : "row-a";
}

$conn->close();
?>