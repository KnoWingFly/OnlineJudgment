<?php
session_start();
if (!isset($_SESSION['isloggedin'])) {
    echo "<meta http-equiv='Refresh' content='0; URL=login.php' />";
    exit(0);
}

include('settings.php');

// Print table header
print '<tr>
    <th>ID</th>
    <th>Time</th>
    <th>Problem</th>
    <th>Status</th>
    <th>Execution Time</th>
    <th>Actions</th>
</tr>';

$conn = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Modified query to only show current user's submissions
$query = "SELECT id, time, problemid, status, execution_time 
          FROM submissions 
          WHERE userid = ? 
          ORDER BY time DESC 
          LIMIT 30";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['userid']);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Convert status code to text
        $status = match((int)$row['status']) {
            0 => "Accepted",
            1 => "Compile Error",
            2 => "Wrong Answer",
            3 => "Time Limit",
            4 => "Invalid File",
            5 => "Runtime Error",
            default => "System Error"
        };

        // Format the timestamp
        $submissionTime = new DateTime();
        $submissionTime->setTimestamp($row['time']);
        $formattedTime = $submissionTime->format('Y-m-d H:i:s');

        // Set row class based on status
        $rowClass = ($status === "Accepted") ? "correct" : "wrong";

        // Format execution time
        $executionTime = number_format($row['execution_time'], 3);

        // Output the row with styled view code button
        printf(
            "<tr class='%s'>
                <td>%d</td>
                <td>%s</td>
                <td>%d</td>
                <td>%s</td>
                <td>%s s</td>
                <td><button class='view-code-btn' onclick='viewCode(%d)'>View Code</button></td>
            </tr>",
            htmlspecialchars($rowClass),
            (int)$row['id'],
            htmlspecialchars($formattedTime),
            (int)$row['problemid'],
            htmlspecialchars($status),
            htmlspecialchars($executionTime),
            (int)$row['id']
        );
    }
    $result->free();
}

$conn->close();
?>