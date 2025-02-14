    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['isloggedin'])) {
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

    $problemid = (int) htmlentities($problemid);

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

    <div class="bg-[#1A1A1A] p-6 rounded-xl border border-[#2E2E2E]">
        <form id="uploadForm<?php echo $problemid; ?>" class="uploadform" action="processfile.php" method="post"
            enctype="multipart/form-data">
            <div class="flex flex-col space-y-4">
                <div class="space-y-2">
                    <label class="block text-gray-100 text-sm font-medium">Select File</label>
                    <input type="file" name="<?php echo $problemid; ?>" id="fileInput<?php echo $problemid; ?>"
                        accept=".c,.cpp,.py,.java,.go"
                        class="w-full bg-[#0A0A0A] border border-[#2E2E2E] text-gray-100 rounded-lg px-4 py-2 file:mr-4 file:py-1 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-[#0736FF] file:text-white hover:file:bg-[#062DBF] transition-colors"
                        required />
                </div>

                <button type="submit"
                    class="w-full bg-[#0736FF] text-white px-6 py-3 rounded-lg font-medium hover:bg-[#062DBF] transition-colors flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Submit Solution
                </button>
            </div>
        </form>

<<<<<<< HEAD
        <div id="status<?php echo $problemid; ?>" class="mt-4 text-sm font-medium 
            <?php
            switch ($status) {
                case -1:
                    echo 'text-white';
                    break;
                case VERDICT_CORRECT:
                    echo 'text-green-400';
                    break;
                case VERDICT_COMPILE_ERROR:
                    echo 'text-red-400';
                    break;
                case VERDICT_WRONG:
                    echo 'text-yellow-400';
                    break;
                case VERDICT_TIME_EXCEEDED:
                    echo 'text-orange-400';
                    break;
                case VERDICT_ILLEGAL_FILE:
                    echo 'text-purple-400';
                    break;
                case VERDICT_RTE:
                    echo 'text-pink-400';
                    break;
                default:
                    echo 'text-white';
=======
<script type="text/javascript">
    $(document).ready(function () {
        // Clear status when new file is selected
        $('input[type="file"]').change(function () {
            var problemId = $(this).attr('name');
            $('#status' + problemId)
                .removeClass()
                .addClass('mt-4 text-sm font-medium text-gray-100')
                .html('<div class="flex items-center"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Ready to submit</div>');
        });

        // Initialize form submission handling
        $('.uploadform').ajaxForm({
            dataType: 'json',
            beforeSubmit: function (arr, $form, options) {
                var problemId = $form.find('input[type="file"]').attr('name');
                $('#status' + problemId)
                    .html(`
                    <div class="flex items-center text-blue-400">
                        <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                        <span>Submitting...</span>
                    </div>
                `);
                return true;
            },
            success: function (response) {
                if (!response || typeof response.verdict === 'undefined') {
                    throw new Error('Invalid response from server');
                }

                var status = $('#status' + response.problemid);
                var statusClass;
                var statusText;

                switch (parseInt(response.verdict)) {
                    case 0: // VERDICT_CORRECT
                        statusClass = 'text-green-400';
                        statusText = 'Accepted';
                        break;
                    case 1: // VERDICT_COMPILE_ERROR
                        statusClass = 'text-red-400';
                        statusText = 'Compile Error';
                        break;
                    case 2: // VERDICT_WRONG
                        statusClass = 'text-yellow-400';
                        statusText = 'Wrong Answer';
                        break;
                    case 3: // VERDICT_TIME_EXCEEDED
                        statusClass = 'text-orange-400';
                        statusText = 'Time Limit';
                        break;
                    case 4: // VERDICT_ILLEGAL_FILE
                        statusClass = 'text-purple-400';
                        statusText = 'Invalid File';
                        break;
                    case 5: // VERDICT_RTE
                        statusClass = 'text-pink-400';
                        statusText = response.message || 'Runtime Error';
                        break;
                    case 6: // System Error
                        statusClass = 'text-red-400';
                        statusText = response.message || 'System Error';
                        break;
                    default:
                        statusClass = 'text-red-400';
                        statusText = 'Unknown Error';
                }

                status.removeClass()
                    .addClass('mt-4 text-sm font-medium ' + statusClass)
                    .html('<div class="flex items-center"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><strong>' + statusText + '</strong></div>');

                if (response.execution_time) {
                    status.append('<br>Time: ' + response.execution_time + 's');
                }

                if (response.output) {
                    console.log('Submission output:', response.output);
                }
            },
            error: function (xhr, status, error) {
                var problemId = $(this).find('input[type="file"]').attr('name');
                $('#status' + problemId)
                    .removeClass()
                    .addClass('mt-4 text-lg font-semibold text-red-600')
                    .html('<strong>Submission Error</strong><br>' + error);
                console.error('Submission error:', error);
                console.error('XHR:', xhr.responseText);
>>>>>>> origin/master
            }
            ?>">
            <?php if ($status == -1): ?>
                <div class="flex items-center text-gray-100">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Ready to submit
                </div>
            <?php else: ?>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <?php echo htmlspecialchars($statString[$status]); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            // Clear status when new file is selected
            $('input[type="file"]').change(function () {
                var problemId = $(this).attr('name');
                $('#status' + problemId)
                    .removeClass()
                    .addClass('mt-4 text-lg font-semibold text-gray-100')
                    .html('<strong>Ready to submit</strong>');
            });

            // Initialize form submission handling
            $('.uploadform').ajaxForm({
                dataType: 'json',
                beforeSubmit: function (arr, $form, options) {
                    var problemId = $form.find('input[type="file"]').attr('name');
                    $('#status' + problemId)
                        .removeClass()
                        .addClass('mt-4 text-lg font-semibold text-[#1E1E1E]')
                        .html('<strong>Submitting...</strong>');
                    return true;
                },
                success: function (response) {
                    if (!response || typeof response.verdict === 'undefined') {
                        throw new Error('Invalid response from server');
                    }

                    var status = $('#status' + response.problemid);
                    var statusClass;
                    var statusText;

                    switch (parseInt(response.verdict)) {
                        case 0: // VERDICT_CORRECT
                            statusClass = 'text-green-400';
                            statusText = 'Accepted';
                            break;
                        case 1: // VERDICT_COMPILE_ERROR
                            statusClass = 'text-red-400';
                            statusText = 'Compile Error';
                            break;
                        case 2: // VERDICT_WRONG
                            statusClass = 'text-yellow-400';
                            statusText = 'Wrong Answer';
                            break;
                        case 3: // VERDICT_TIME_EXCEEDED
                            statusClass = 'text-orange-400';
                            statusText = 'Time Limit';
                            break;
                        case 4: // VERDICT_ILLEGAL_FILE
                            statusClass = 'text-purple-400';
                            statusText = 'Invalid File';
                            break;
                        case 5: // VERDICT_RTE
                            statusClass = 'text-pink-400';
                            statusText = response.message || 'Runtime Error';
                            break;
                        case 6: // System Error
                            statusClass = 'text-red-400';
                            statusText = response.message || 'System Error';
                            break;
                        default:
                            statusClass = 'text-red-400';
                            statusText = 'Unknown Error';
                    }

                    status.removeClass()
                        .addClass('mt-4 text-sm font-medium ' + statusClass)
                        .html('<div class="flex items-center"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><strong>' + statusText + '</strong></div>');

                    if (response.execution_time) {
                        status.append('<br>Time: ' + response.execution_time + 's');
                    }

                    if (response.output) {
                        console.log('Submission output:', response.output);
                    }
                },
                error: function (xhr, status, error) {
                    var problemId = $(this).find('input[type="file"]').attr('name');
                    $('#status' + problemId)
                        .removeClass()
                        .addClass('mt-4 text-lg font-semibold text-red-600')
                        .html('<strong>Submission Error</strong><br>' + error);
                    console.error('Submission error:', error);
                    console.error('XHR:', xhr.responseText);
                }
            });
        });
    </script>