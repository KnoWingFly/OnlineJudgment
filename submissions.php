<?php
/*
 * @copyright (c) 2008 Nicolo John Davis
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

session_start();
if (!isset($_SESSION['isloggedin'])) {
	echo "<meta http-equiv='Refresh' content='0; URL=login.php' />";
	exit(0);
} else {
	$username = $_SESSION['username'];
	$userid = $_SESSION['userid'];
}

include('settings.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta name="Keywords" content="programming, contest, coding, judge" />
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="Distribution" content="Global" />
	<meta name="Robots" content="index,follow" />
	<link rel="stylesheet" href="images/Envision.css" type="text/css" />
	<title>Programming Contest</title>
	<script type="text/javascript" src="jquery-1.3.1.js"></script>
	<?php include('timer.php'); ?>

	<!-- Updated modal styles -->
	<style>
		.modal {
			display: none;
			position: fixed;
			z-index: 1000;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			overflow: auto;
			background-color: rgba(0, 0, 0, 0.5);
			animation: fadeIn 0.3s ease-in-out;
		}

		@keyframes fadeIn {
			from {
				opacity: 0;
			}

			to {
				opacity: 1;
			}
		}

		.modal-content {
			background-color: #fefefe;
			margin: 5% auto;
			padding: 20px;
			border: 1px solid #888;
			width: 80%;
			max-width: 800px;
			border-radius: 8px;
			box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
			position: relative;
			max-height: 80vh;
			display: flex;
			flex-direction: column;
		}

		.modal-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding-bottom: 10px;
			border-bottom: 1px solid #ddd;
			margin-bottom: 15px;
		}

		.modal-title {
			font-size: 1.25rem;
			font-weight: bold;
			margin: 0;
		}

		.close {
			position: absolute;
			right: 20px;
			top: 15px;
			color: #666;
			font-size: 24px;
			font-weight: bold;
			cursor: pointer;
			background: none;
			border: none;
			padding: 0;
			width: 30px;
			height: 30px;
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 50%;
			transition: all 0.2s ease;
		}

		.close:hover {
			background-color: #f0f0f0;
			color: #333;
		}

		.modal-body {
			flex: 1;
			overflow-y: auto;
			padding: 10px 0;
		}

		pre {
			background-color: #f8f8f8;
			padding: 15px;
			border-radius: 6px;
			border: 1px solid #ddd;
			overflow-x: auto;
			font-family: monospace;
			font-size: 14px;
			line-height: 1.4;
			margin: 0;
		}

		/* Style for the View Code button */
		.view-code-btn {
			background-color: #4CAF50;
			color: white;
			border: none;
			padding: 6px 12px;
			border-radius: 4px;
			cursor: pointer;
			font-size: 14px;
			transition: background-color 0.2s;
		}

		.view-code-btn:hover {
			background-color: #45a049;
		}
	</style>

	<script type="text/javascript">
		$(document).ready(function () {
			dispTime();
			getLeaders();
			getDetails();
			getSubmissions();
			setInterval("dispTime()", 1000);
			setInterval("getLeaders()", getLeaderInterval);
			setInterval("getDetails()", getLeaderInterval);
			setInterval("getSubmissions()", getLeaderInterval);

			$('.close').live('click', function () {
				closeModal();
			});

			$('#codeModal').live('click', function (event) {
				if (event.target.id === 'codeModal') {
					closeModal();
				}
			});

			$('.modal-content').live('click', function (event) {
				event.stopPropagation();
			});

			$(document).live('keydown', function (event) {
				if (event.key === "Escape") {
					closeModal();
				}
			});
		});

		// Function to open the modal
		function openModal(content) {
			$('#submissionCode').html(content); 
			$('#codeModal').show(); 
		}

		// Function to close the modal
		function closeModal() {
			$('#codeModal').hide(); 
			$('#submissionCode').html(''); 
		}

		function viewCode(submissionId) {
			$.ajax({
				url: 'get_submission_code.php',
				type: 'GET',
				data: { submission_id: submissionId },
				success: function (response) {
					try {
						var data = JSON.parse(response);
						if (data.success) {
							openModal(data.code); 
						} else {
							alert('Error: ' + data.message);
						}
					} catch (e) {
						console.error('Error parsing response:', e);
						alert('Error loading code');
					}
				},
				error: function (xhr, status, error) {
					console.error('AJAX error:', error);
					alert('Error loading code');
				}
			});
		}
	</script>


</head>

<body class="menu3">
	<!-- wrap starts here -->
	<div id="wrap">
		<!--header -->
		<?php include('header.php'); ?>
		<!-- menu -->
		<?php include('menu.php'); ?>
		<!-- content-wrap starts here -->
		<div id="content-wrap">
			<div id="main">
				<table id="submissions"> </table>
			</div>
			<div id="sidebar">
				<?php include('sidebar.php'); ?>
			</div>
			<!-- content-wrap ends here -->
		</div>
		<!--footer starts here-->
		<div id="footer">
			<?php include('footer.php'); ?>
		</div>
	</div>
	<!-- wrap ends here -->

	<!-- Improved modal structure -->
	<div id="codeModal" class="modal">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">Submission Code</h3>
				<button type="button" class="close">&times;</button>
			</div>
			<div class="modal-body">
				<pre id="submissionCode"></pre>
			</div>
		</div>
	</div>
</body>

</html>