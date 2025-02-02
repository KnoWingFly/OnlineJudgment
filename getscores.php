<?php
include('settings.php');
include('scoreboard_settings.php');

// Check if scoreboard is disabled and user is not admin
if (!$scoreboardEnabled && !isset($_SESSION['admin'])) {
    echo '<tr><td colspan="100%" class="p-8 text-center text-gray-400">';
    echo 'Scoreboard is currently disabled by administrators.';
    echo '</td></tr>';
    exit();
}
// Create connection using MySQLi
$conn = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch problem points from the database
$points = array();
$pointsQuery = "SELECT id FROM problems ORDER BY id";
$pointsResult = $conn->query($pointsQuery);

if (!$pointsResult) {
    die("Failed to fetch problem points: " . $conn->error);
}

while ($pointRow = $pointsResult->fetch_assoc()) {
    $points[$pointRow['id'] - 1] = 1; // Each problem is worth 1 point
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

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $name = $row['username'];
    $idmaps[$id] = $ctr;

    $dataSub[$ctr] = array();
    $dataSub[$ctr]['username'] = $name;
    $dataSub[$ctr]['score'] = 0;
    $dataSub[$ctr]['last_submission'] = 0;
    for ($i = 1; $i <= $nProb; $i++) {
        $dataSub[$ctr]['tries_' . $i] = 0;
        $dataSub[$ctr]['solved_' . $i] = false;
        $dataSub[$ctr]['last_status_' . $i] = null;
        $dataSub[$ctr]['submissions_' . $i] = array();
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

while ($row = $result->fetch_assoc()) {
    $id = $row['userid'];
    $probid = $row['problemid'];
    $stat = intval($row['status']);
    $time = intval($row['time']);

    if (isset($idmaps[$id])) {
        $ctr = $idmaps[$id];

        if ($time > $dataSub[$ctr]['last_submission']) {
            $dataSub[$ctr]['last_submission'] = $time;
        }

        $dataSub[$ctr]['tries_' . $probid]++;
        $dataSub[$ctr]['last_status_' . $probid] = $stat;

        if ($stat == 0 && !$dataSub[$ctr]['solved_' . $probid]) {
            $dataSub[$ctr]['solved_' . $probid] = true;
            $dataSub[$ctr]['score'] += $points[$probid - 1];
        }
    }
}

// Sort teams by score (descending) and last submission time (ascending)
usort($dataSub, function ($a, $b) {
    if ($a['score'] != $b['score']) {
        return $b['score'] - $a['score'];
    }
    return $a['last_submission'] - $b['last_submission'];
});

// Output the scoreboard table with Tailwind CSS classes
print '<thead>';
print '<tr>';
print '<th class="bg-blue-600 p-4 text-white font-medium">Position</th>';
print '<th class="bg-blue-600 p-4 text-white font-medium">User</th>';

for ($i = 1; $i <= count($points); $i++) {
    print "<th class='bg-blue-600 p-4 text-white font-medium'>" . htmlspecialchars($i) . "</th>";
}

print '<th class="bg-blue-600 p-4 text-white font-medium">Score</th>';
print '<th class="bg-blue-600 p-4 text-white font-medium">Last Submission</th>';
print '</tr>';
print '</thead>';

print '<tbody>';
for ($i = 0; $i < count($dataSub); $i++) {
    $rankNow = $i + 1;
    $name = htmlspecialchars($dataSub[$i]['username']);

    // Alternate row backgrounds and highlight current user
    $rowClass = ($i % 2 == 0) ? 'bg-opacity-5 bg-white' : 'bg-opacity-10 bg-white';
    if (isset($username) && $dataSub[$i]['username'] == $username) {
        $rowClass .= ' font-bold bg-opacity-20 bg-white';
    }

    print "<tr class='$rowClass transition-colors duration-150'>";
    print "<td class='p-4 text-center border border-gray-800'>" . $rankNow . "</td>";
    print "<td class='p-4 text-center border border-gray-800'>" . $name . "</td>";

    // Problem status cells
    for ($j = 1; $j <= $nProb; $j++) {
        $tries = $dataSub[$i]['tries_' . $j];
        $status = intval($dataSub[$i]['last_status_' . $j]);
        $solved = $dataSub[$i]['solved_' . $j];

        print "<td class='p-4 text-center border border-gray-800'>";
        print "<div class='flex items-center justify-center w-12 h-12 mx-auto'>";

        if ($tries == 0) {
            // No attempts
            print "<span class='text-2xl text-gray-500'>—</span>";
        } else {
            if ($solved) {
                // Problem solved
                print "<span class='text-2xl text-green-500'>✓</span>";
            } else {
                // Different error states
                switch ($status) {
                    case 1: // Compile Error
                        print "<span class='text-red-500 font-semibold'>CE</span>";
                        break;
                    case 2: // Wrong Answer
                        print "<span class='text-2xl text-red-500'>X</span>";
                        break;
                    case 3: // Time Limit
                        print "<span class='text-red-500 font-semibold'>TL</span>";
                        break;
                    case 5: // Runtime Error
                        print "<span class='text-red-500 font-semibold'>RE</span>";
                        break;
                    default:
                        print "<span class='text-2xl text-gray-500'>—</span>";
                }
            }
        }

        print "</div>";
        print "</td>";
    }

    // Score and time
    $score = htmlspecialchars($dataSub[$i]['score']);
    $last_submission_time = $dataSub[$i]['last_submission'];

    if ($last_submission_time > 0) {
        $time_diff = time() - $last_submission_time;
        if ($time_diff < 60) {
            $last_update = "Just now";
        } elseif ($time_diff < 3600) {
            $minutes = floor($time_diff / 60);
            $last_update = $minutes . " min" . ($minutes > 1 ? "s" : "") . " ago";
        } elseif ($time_diff < 86400) {
            $hours = floor($time_diff / 3600);
            $last_update = $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
        } else {
            $last_update = date('M j, Y H:i', $last_submission_time);
        }
    } else {
        $last_update = "Never";
    }

    print "<td class='p-4 text-center border border-gray-800'>" . $score . "</td>";
    print "<td class='p-4 text-center border border-gray-800'>" . $last_update . "</td>";
    print "</tr>";
}
print '</tbody>';

$conn->close();
?>