<?php
session_start();
if (!isset($_SESSION['isloggedin'])) {
    echo "<meta http-equiv='Refresh' content='0; URL=login.php' />";
    exit(0);
}

include('settings.php');

// Print table header
echo '<thead>';
echo '<tr>';
echo '<th class="bg-blue-600 p-4 text-white font-medium">#</th>';
echo '<th class="bg-blue-600 p-4 text-white font-medium">When</th>';
echo '<th class="bg-blue-600 p-4 text-white font-medium">Problem</th>';
echo '<th class="bg-blue-600 p-4 text-white font-medium">Lang</th>';
echo '<th class="bg-blue-600 p-4 text-white font-medium">Verdict</th>';
echo '<th class="bg-blue-600 p-4 text-white font-medium">Time</th>';
echo '<th class="bg-blue-600 p-4 text-white font-medium">Actions</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

$conn = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT id, time, problemid, status, execution_time, filename 
          FROM submissions 
          WHERE userid = ? 
          ORDER BY time DESC 
          LIMIT 30";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['userid']);
$stmt->execute();
$result = $stmt->get_result();

function detectLanguage($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return match($extension) {
        'c' => 'C',
        'cpp', 'cc', 'cxx' => 'C++',
        'py' => 'Python',
        'go' => 'Go',
        'java' => 'Java',
        default => 'Unknown'
    };
}

if ($result) {
    $rowCount = 0;
    while ($row = $result->fetch_assoc()) {
        $rowClass = ($rowCount % 2 == 0) ? 'bg-opacity-5 bg-white' : 'bg-opacity-10 bg-white';
        
        $status = match((int)$row['status']) {
            0 => ["✓", "text-green-500"],
            1 => ["CE", "text-red-500"],
            2 => ["X", "text-red-500"],
            3 => ["TL", "text-red-500"],
            4 => ["IF", "text-red-500"],
            5 => ["RE", "text-red-500"],
            default => ["SE", "text-red-500"]
        };

        $submissionTime = new DateTime();
        $submissionTime->setTimestamp($row['time']);
        $formattedTime = $submissionTime->format('M/d/Y H:i');

        $language = detectLanguage($row['filename']);
        
        echo "<tr class='$rowClass transition-colors duration-150'>";
        echo "<td class='p-4 text-center border border-gray-800'>" . (int)$row['id'] . "</td>";
        echo "<td class='p-4 text-center border border-gray-800'>" . htmlspecialchars($formattedTime) . "</td>";
        echo "<td class='p-4 text-center border border-gray-800'>" . (int)$row['problemid'] . "</td>";
        echo "<td class='p-4 text-center border border-gray-800'>" . htmlspecialchars($language) . "</td>";
        echo "<td class='p-4 text-center border border-gray-800'><span class='" . $status[1] . "'>" . $status[0] . "</span></td>";
        echo "<td class='p-4 text-center border border-gray-800'>" . number_format($row['execution_time'] * 1000, 0) . " ms</td>";
        echo "<td class='p-4 text-center border border-gray-800'>";
        echo "<button onclick='viewCode(" . (int)$row['id'] . ")' class='bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors duration-150'>View Code</button>";
        echo "</td>";
        echo "</tr>";
        
        $rowCount++;
    }
    $result->free();
}

echo '</tbody>';
$conn->close();
?>