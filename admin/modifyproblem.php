<?php
session_start();
if (!isset($_SESSION['isloggedin'])) {
	echo "<meta http-equiv='Refresh' content='0; URL=../login.php' />";
	exit(0);
} else {
	$username = $_SESSION['username'];
	$userid = $_SESSION['userid'];

	if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
		echo json_encode(['success' => false, 'message' => 'You need to be the administrator to access this file']);
		exit(0);
	}
}

include('../settings.php');

// Establish database connection
$cn = mysqli_connect('localhost', $DBUSER, $DBPASS, $DBNAME);
if (!$cn) {
	die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$problemid = mysqli_real_escape_string($cn, $_REQUEST['problemid'] ?? '');
$mode = mysqli_real_escape_string($cn, $_REQUEST['mode'] ?? '');

$problemDir = "../$PROBLEMDIR/$problemid/";
$statementFile = $problemDir . "statement.html";

if ($mode == 'get') {
	// Read and output the statement file
	if (file_exists($statementFile)) {
		readfile($statementFile);
	} else {
		echo "Problem statement not found.";
	}
} else if ($mode == 'getFullDetails') {
	// Fetch problem details from database
	$query = "SELECT title, time_limit FROM problems WHERE id = ?";
	$stmt = mysqli_prepare($cn, $query);
	mysqli_stmt_bind_param($stmt, "i", $problemid);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	$problem = mysqli_fetch_assoc($result);

	// Read statement HTML
	if (file_exists($statementFile)) {
		$statementContent = file_get_contents($statementFile);
		$problem['originalHtml'] = $statementContent;

		// Extract details from HTML using DOM
		$dom = new DOMDocument();
		@$dom->loadHTML($statementContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

		// Extracting description
		$problem['description'] = '';
		$descElements = $dom->getElementsByTagName('p');
		foreach ($descElements as $elem) {
			if ($elem->getAttribute('align') === 'justify') {
				$problem['description'] = trim($elem->textContent);
				break;
			}
		}

		// Extracting input format
		$problem['inputFormat'] = '';
		$inputFormatElements = $dom->getElementsByTagName('font');
		foreach ($inputFormatElements as $fontElement) {
			if (stripos($fontElement->textContent, 'Input Format') !== false) {
				$nextElement = $fontElement->nextSibling;
				while ($nextElement) {
					if ($nextElement->nodeType == XML_ELEMENT_NODE) {
						if ($nextElement->tagName == 'p' || $nextElement->tagName == 'pre') {
							$problem['inputFormat'] .= trim($nextElement->textContent) . "\n";
						}
						if (
							$nextElement->tagName == 'font' &&
							(stripos($nextElement->textContent, 'Output Format') !== false ||
								stripos($nextElement->textContent, 'Constraints') !== false)
						) {
							break;
						}
					}
					$nextElement = $nextElement->nextSibling;
				}
			}
		}

		// Extracting output format
		$problem['outputFormat'] = '';
		$outputFormatElements = $dom->getElementsByTagName('font');
		foreach ($outputFormatElements as $fontElement) {
			if (stripos($fontElement->textContent, 'Output Format') !== false) {
				$nextElement = $fontElement->nextSibling;
				while ($nextElement) {
					if ($nextElement->nodeType == XML_ELEMENT_NODE) {
						if ($nextElement->tagName == 'p' || $nextElement->tagName == 'pre') {
							$problem['outputFormat'] .= trim($nextElement->textContent) . "\n";
						}
						if (
							$nextElement->tagName == 'font' &&
							stripos($nextElement->textContent, 'Constraints') !== false
						) {
							break;
						}
					}
					$nextElement = $nextElement->nextSibling;
				}
			}
		}

		// Extracting constraints
		$problem['constraints'] = '';
		$constraintsElements = $dom->getElementsByTagName('font');
		foreach ($constraintsElements as $fontElement) {
			if (stripos($fontElement->textContent, 'Constraints') !== false) {
				$nextElement = $fontElement->nextSibling;
				$constraintsText = '';
				while ($nextElement) {
					if ($nextElement->nodeType == XML_ELEMENT_NODE) {
						if (
							$nextElement->tagName == 'font' &&
							(stripos($nextElement->textContent, 'Sample Input') !== false ||
								stripos($nextElement->textContent, 'Input') !== false)
						) {
							break;
						}
						if ($nextElement->tagName == 'p' || $nextElement->tagName == 'pre') {
							$constraintsText .= trim($nextElement->textContent) . "\n";
						}
					}
					$nextElement = $nextElement->nextSibling;
				}
				$problem['constraints'] = trim($constraintsText);
				break;
			}
		}

		// Extracting sample input and output
		$preElements = $dom->getElementsByTagName('pre');
		$problem['sampleInput'] = $preElements->length > 0 ? trim($preElements->item(0)->textContent) : '';
		$problem['sampleOutput'] = $preElements->length > 1 ? trim($preElements->item(1)->textContent) : '';

		// Trim any extra whitespace
		$problem['inputFormat'] = trim($problem['inputFormat']);
		$problem['outputFormat'] = trim($problem['outputFormat']);

		echo json_encode($problem);
	} else {
		echo json_encode(['success' => false, 'message' => 'Statement file not found']);
	}
} else if ($mode == 'updateProblem') {
	// Validate and sanitize inputs
	$title = mysqli_real_escape_string($cn, $_POST['title'] ?? '');
	$timeLimit = floatval($_POST['timeLimit'] ?? 0);
	$description = $_POST['description'] ?? '';
	$inputFormat = $_POST['inputFormat'] ?? '';
	$outputFormat = $_POST['outputFormat'] ?? '';
	$constraints = $_POST['constraints'] ?? '';
	$sampleInput = $_POST['sampleInput'] ?? '';
	$sampleOutput = $_POST['sampleOutput'] ?? '';

	// Validate required fields
	if (empty($title) || empty($timeLimit)) {
		echo json_encode(['success' => false, 'message' => 'Title and time limit are required']);
		exit;
	}

	// Begin transaction
	mysqli_begin_transaction($cn);

	try {
		// Update problem details in database
		$query = "UPDATE problems SET title = ?, time_limit = ? WHERE id = ?";
		$stmt = mysqli_prepare($cn, $query);

		if (!$stmt) {
			throw new Exception("Failed to prepare statement: " . mysqli_error($cn));
		}

		mysqli_stmt_bind_param($stmt, "sdi", $title, $timeLimit, $problemid);

		if (!mysqli_stmt_execute($stmt)) {
			throw new Exception("Failed to update problem details: " . mysqli_stmt_error($stmt));
		}

		// Check if statement file directory exists, create if not
		if (!file_exists($problemDir)) {
			if (!mkdir($problemDir, 0777, true)) {
				throw new Exception("Failed to create problem directory");
			}
		}

		// Generate new HTML content
		$newStatementContent = <<<EOT
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
    <meta charset="utf-8">
</head>
<body bgcolor="white">
    <font color="#0000FF"><h1>{$title}</h1></font>
    <h3>Time Limit: {$timeLimit}s</h3>

    <p align="justify">{$description}</p>

    <font color="#0000FF"><h2>Input Format</h2></font>
    <p align="justify">{$inputFormat}</p>

    <font color="#0000FF"><h2>Output Format</h2></font>
    <p align="justify">{$outputFormat}</p>

    <font color="#0000FF"><h2>Constraints</h2></font>
    <p align="justify">{$constraints}</p>

    <font color="#0000FF"><h2>Sample Input</h2></font>
    <pre>{$sampleInput}</pre>

    <font color="#0000FF"><h2>Sample Output</h2></font>
    <pre>{$sampleOutput}</pre>

    <hr>
    <font size="4">
    <center>Programming Contest</center>
    </font>
</body>
</html>
EOT;

		// Write updated statement HTML
		if (file_put_contents($statementFile, $newStatementContent) === false) {
			throw new Exception("Failed to write statement file");
		}

		// Commit transaction
		mysqli_commit($cn);

		echo json_encode(['success' => true]);
	} catch (Exception $e) {
		// Rollback transaction
		mysqli_rollback($cn);
		echo json_encode(['success' => false, 'message' => $e->getMessage()]);
	}
}
?>