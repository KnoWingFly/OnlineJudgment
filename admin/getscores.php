<?php
include('../settings.php');
include('../scoreboard_settings.php');

// Create connection using MySQLi
$conn = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get contest start time as Unix timestamp
$contestStart = $startTime->getTimestamp();

// Penalty for wrong answers (in seconds)
$WRONG_ANSWER_PENALTY = 30 * 60; // 30 minutes in seconds

// Helper function to format time to "Xh Ym" format
function formatDuration($seconds)
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);

    $result = '';
    if ($hours > 0) {
        $result .= $hours . 'h ';
    }
    $result .= $minutes . 'm';

    return $result;
}

// Fetch problem points from the database
$points = array();
$pointsQuery = "SELECT id FROM problems ORDER BY id";
$pointsResult = $conn->query($pointsQuery);

if (!$pointsResult) {
    die("Failed to fetch problem points: " . $conn->error);
}

while ($pointRow = $pointsResult->fetch_assoc()) {
    $points[$pointRow['id'] - 1] = 1;
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
    $dataSub[$ctr]['total_time'] = 0;  // Track total solve time
    $dataSub[$ctr]['wrong_attempts'] = array(); // Track wrong attempts for each problem

    for ($i = 1; $i <= $nProb; $i++) {
        $dataSub[$ctr]['tries_' . $i] = 0;
        $dataSub[$ctr]['solved_' . $i] = false;
        $dataSub[$ctr]['last_status_' . $i] = null;
        $dataSub[$ctr]['solve_time_' . $i] = null;
        $dataSub[$ctr]['wrong_attempts'][$i] = array(); // Initialize wrong attempts for this problem
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

        $dataSub[$ctr]['tries_' . $probid]++;

        // Track wrong attempts for penalty calculation
        if ($stat != 0 && !$dataSub[$ctr]['solved_' . $probid]) {
            $dataSub[$ctr]['wrong_attempts'][$probid][] = $time;
        }

        $dataSub[$ctr]['last_status_' . $probid] = $stat;

        if ($stat == 0 && !$dataSub[$ctr]['solved_' . $probid]) {
            // Calculate total penalty for wrong attempts
            $penalty_time = 0;
            foreach ($dataSub[$ctr]['wrong_attempts'][$probid] as $wrong_time) {
                $penalty_time += $WRONG_ANSWER_PENALTY;
            }

            $solve_time = $time - $contestStart + $penalty_time;
            $dataSub[$ctr]['solved_' . $probid] = true;
            $dataSub[$ctr]['score'] += $points[$probid - 1];
            $dataSub[$ctr]['solve_time_' . $probid] = $solve_time;
            $dataSub[$ctr]['total_time'] += $solve_time;
        }
    }
}

// Sort teams by score (descending) and total time (ascending)
usort($dataSub, function ($a, $b) {
    if ($a['score'] != $b['score']) {
        return $b['score'] - $a['score'];
    }
    return $a['total_time'] - $b['total_time'];
});

// Start table
print '<table class="w-full border-collapse bg-gray-900 text-gray-100">';

// Output table headers
print '<thead>';
print '<tr>';
print '<th class="bg-blue-600 p-4 text-white font-medium">Position</th>';
print '<th class="bg-blue-600 p-4 text-white font-medium">User</th>';

for ($i = 1; $i <= count($points); $i++) {
    print "<th class='bg-blue-600 p-4 text-white font-medium'>" . htmlspecialchars($i) . "</th>";
}

print '<th class="bg-blue-600 p-4 text-white font-medium">Score</th>';
print '<th class="bg-blue-600 p-4 text-white font-medium">Total Time</th>';
print '</tr>';
print '</thead>';

print '<tbody>';
for ($i = 0; $i < count($dataSub); $i++) {
    $rankNow = $i + 1;
    $name = htmlspecialchars($dataSub[$i]['username']);

    // Special design for top 3
    if ($i < 3) {
        // Updated color scheme with more vibrant and distinct colors
        $rowClasses = [
            0 => 'bg-amber-600 bg-opacity-30 text-amber-100', // 1st place (gold)
            1 => 'bg-slate-600 bg-opacity-30 text-slate-100', // 2nd place (silver)
            2 => 'bg-orange-700 bg-opacity-30 text-orange-100' // 3rd place (bronze)
        ];

        // Medal and icon emojis
        $medals = ['🥇', '🥈', '🥉'];

        print "<tr class='{$rowClasses[$i]} font-bold border-none'>";
        print "<td class='p-4 text-center text-xl'>{$medals[$i]} {$rankNow}</td>";
        print "<td class='p-4 text-center'>" . $name . "</td>";
    } else {
        // Alternate row backgrounds for others
        $rowClass = ($i % 2 == 0) ? 'bg-opacity-5 bg-white' : 'bg-opacity-10 bg-white';
        if (isset($username) && $dataSub[$i]['username'] == $username) {
            $rowClass .= ' font-bold bg-opacity-20 bg-white';
        }

        print "<tr class='$rowClass transition-colors duration-150'>";
        print "<td class='p-4 text-center border border-gray-800'>" . $rankNow . "</td>";
        print "<td class='p-4 text-center border border-gray-800'>" . $name . "</td>";
    }

    // Problem status cells
    for ($j = 1; $j <= $nProb; $j++) {
        $tries = $dataSub[$i]['tries_' . $j];
        $status = intval($dataSub[$i]['last_status_' . $j]);
        $solved = $dataSub[$i]['solved_' . $j];
        $solveTime = $dataSub[$i]['solve_time_' . $j];

        // Different styling for top 3 and other rows
        $cellClass = ($i < 3) ? 'p-4 text-center' : 'p-4 text-center border border-gray-800';

        print "<td class='$cellClass'>";
        print "<div class='flex flex-col items-center justify-center w-full h-full'>";

        if ($tries == 0) {
            // No attempts
            print "<span class='text-2xl text-gray-500'>—</span>";
        } else {
            if ($solved) {
                // Problem solved - show checkmark and time
                print "<span class='text-2xl text-green-500'>✓</span>";
                print "<span class='text-sm text-green-600 mt-1'>" . formatDuration($solveTime) . "</span>";
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

    // Score and total time
    $score = htmlspecialchars($dataSub[$i]['score']);
    $total_time = $dataSub[$i]['total_time'];

    // Different styling for top 3 and other rows
    $cellClass = ($i < 3) ? 'p-4 text-center' : 'p-4 text-center border border-gray-800';

    print "<td class='$cellClass'>" . $score . "</td>";
    print "<td class='$cellClass'>" .
        ($total_time > 0 ? formatDuration($total_time) : "—") . "</td>";
    print "</tr>";
}
print '</tbody>';
print '</table>';

$conn->close();
?>