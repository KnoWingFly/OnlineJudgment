<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['isloggedin'])) {
    header("Location: login.php");
    exit(0);
}

$username = $_SESSION['username'];
$userid = $_SESSION['userid'];

include('settings.php');

// Define verdicts to match onj script
define('VERDICT_CORRECT', 0);
define('VERDICT_COMPILE_ERROR', 1);
define('VERDICT_WRONG', 2);
define('VERDICT_TIME_EXCEEDED', 3);
define('VERDICT_ILLEGAL_FILE', 4);
define('VERDICT_RTE', 5);

// Validate problemid
if (!isset($problemid) || !is_numeric($problemid)) {
    $problemid = 1;
}

$problemid = (int)htmlentities($problemid);

try {
    $conn = new mysqli('localhost', $DBUSER, $DBPASS, $DBNAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $status = -1; // Default to "Not submitted"
    $statString = array(
        -1 => 'Not submitted',
        VERDICT_CORRECT => 'Accepted',
        VERDICT_COMPILE_ERROR => 'Compile Error',
        VERDICT_WRONG => 'Wrong Answer',
        VERDICT_TIME_EXCEEDED => 'Time Limit',
        VERDICT_ILLEGAL_FILE => 'Invalid File',
        VERDICT_RTE => 'Runtime Error'
    );

    $stmt = $conn->prepare("SELECT status FROM submissions WHERE userid = ? AND problemid = ? ORDER BY time DESC LIMIT 1");
    $stmt->bind_param("ii", $userid, $problemid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $status = $row['status'];
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log("Database Error in uploadform: " . $e->getMessage());
    $status = -1;
}
?>

<table style="margin-top: 20px; border: 0;">
    <tr>
        <td id="upload<?php echo $problemid; ?>" class="submitform">
            <form id="uploadForm<?php echo $problemid; ?>" 
                  class="uploadform" 
                  action="processfile.php" 
                  method="post" 
                  enctype="multipart/form-data">
                <input type="file" name="<?php echo $problemid; ?>" 
                       id="fileInput<?php echo $problemid; ?>" 
                       accept=".c,.cpp,.py,.java,.go" required/>
                <input type="submit" value="Submit Solution" 
                       id="submitBtn<?php echo $problemid; ?>"/>
            </form>
        </td>
        <td id="status<?php echo $problemid; ?>" 
            class="<?php 
                switch($status) {
                    case -1: echo 'none'; break;
                    case VERDICT_CORRECT: echo 'accepted'; break;
                    case VERDICT_COMPILE_ERROR: echo 'compile'; break;
                    case VERDICT_WRONG: echo 'wrong'; break;
                    case VERDICT_TIME_EXCEEDED: echo 'time'; break;
                    case VERDICT_ILLEGAL_FILE: echo 'invalid'; break;
                    case VERDICT_RTE: echo 'RTE'; break;
                    default: echo 'none';
                }
            ?>">
            <?php if ($status == -1): ?>
                <strong>Ready to submit</strong>
            <?php else: ?>
                <strong><?php echo htmlspecialchars($statString[$status]); ?></strong>
            <?php endif; ?>
        </td>
    </tr>
</table>

<script type="text/javascript">
$(document).ready(function() {
    // Clear status when new file is selected
    $('input[type="file"]').change(function() {
        var problemId = $(this).attr('name');
        $('#status' + problemId)
            .attr('class', 'none')
            .html('<strong>Ready to submit</strong>');
    });

    // Initialize form submission handling
    $('.uploadform').ajaxForm({
        dataType: 'json',
        beforeSubmit: function(arr, $form, options) {
            var problemId = $form.find('input[type="file"]').attr('name');
            $('#status' + problemId)
                .attr('class', 'pending')
                .html('<strong>Submitting...</strong>');
            return true;
        },
        success: function(response) {
            if (!response || typeof response.verdict === 'undefined') {
                throw new Error('Invalid response from server');
            }

            var status = $('#status' + response.problemid);
            var statusClass;
            var statusText;
            
            switch(parseInt(response.verdict)) {
                case 0: // VERDICT_CORRECT
                    statusClass = 'accepted';
                    statusText = 'Accepted';
                    break;
                case 1: // VERDICT_COMPILE_ERROR
                    statusClass = 'compile';
                    statusText = 'Compile Error';
                    break;
                case 2: // VERDICT_WRONG
                    statusClass = 'wrong';
                    statusText = 'Wrong Answer';
                    break;
                case 3: // VERDICT_TIME_EXCEEDED
                    statusClass = 'time';
                    statusText = 'Time Limit';
                    break;
                case 4: // VERDICT_ILLEGAL_FILE
                    statusClass = 'invalid';
                    statusText = 'Invalid File';
                    break;
                case 5: // VERDICT_RTE
                    statusClass = 'RTE';
                    statusText = response.message || 'Runtime Error';
                    break;
                case 6: // System Error
                    statusClass = 'error';
                    statusText = response.message || 'System Error';
                    break;
                default:
                    statusClass = 'error';
                    statusText = 'Unknown Error';
            }
            
            status.attr('class', statusClass)
                  .html('<strong>' + statusText + '</strong>');
                  
            if(response.execution_time) {
                status.append('<br>Time: ' + response.execution_time + 's');
            }
            
            if(response.output) {
                console.log('Submission output:', response.output);
            }
        },
        error: function(xhr, status, error) {
            var problemId = $(this).find('input[type="file"]').attr('name');
            $('#status' + problemId)
                .attr('class', 'error')
                .html('<strong>Submission Error</strong><br>' + error);
            console.error('Submission error:', error);
            console.error('XHR:', xhr.responseText);
        }
    });
});
</script> 