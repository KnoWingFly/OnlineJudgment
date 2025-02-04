<?php
// update_scores.php
include('settings.php');

// Simple error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);
    if ($db->connect_error) {
        die(json_encode(array('status' => 'error', 'message' => 'Connection failed')));
    }

    // Update scores by summing points for distinct correctly solved problems
    $updateScores = "
       UPDATE users u 
        SET u.score = (
            SELECT COUNT(DISTINCT s.problemid)
            FROM submissions s
            WHERE s.userid = u.id AND s.status = 0
        )";
        
    if (!$db->query($updateScores)) {
        die(json_encode(array('status' => 'error', 'message' => 'Score update failed: ' . $db->error)));
    }

    // Update ranks
    $updateRanks = "
        UPDATE users u
        JOIN (
            SELECT id, @rank := @rank + 1 as new_rank
            FROM users, (SELECT @rank := 0) r
            ORDER BY score DESC, id ASC
        ) r ON u.id = r.id
        SET u.ranks = r.new_rank";

    if (!$db->query($updateRanks)) {
        die(json_encode(array('status' => 'error', 'message' => 'Rank update failed: ' . $db->error)));
    }

    echo json_encode(array('status' => 'success'));

} catch (Exception $e) {
    echo json_encode(array('status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()));
} finally {
    if (isset($db)) {
        $db->close();
    }
}
?>