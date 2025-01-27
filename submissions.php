<?php
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

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Programming Contest</title>
	<script src="jquery-1.3.1.js"></script>
	<?php include('timer.php'); ?>
	<script src="https://cdn.tailwindcss.com"></script>

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

		.fade-in {
			animation: fadeIn 0.5s ease-in;
		}

		/* Add cursor pointer to close button */
		.close-btn {
			cursor: pointer;
			padding: 8px;
			border-radius: 4px;
			transition: background-color 0.2s;
		}

		.close-btn:hover {
			background-color: rgba(255, 255, 255, 0.1);
		}
	</style>

	<script>
		function getSubmissions() {
			$.ajax({
				url: 'getsubmissions.php',
				success: function (data) {
					$('#submissions').html(data);
				}
			});
		}

		$(document).ready(function () {
			dispTime();
			getSubmissions();
			setInterval(dispTime, 1000);
			setInterval(getSubmissions, 5000);

			// Modal handling with `.live()` for compatibility
			$('.close-modal').live('click', function () {
				closeModal();
			});

			// Close modal when clicking outside the modal content
			$('#codeModal').click(function (event) {
				if ($(event.target).is('#codeModal')) {
					closeModal();
				}
			});

			// Close modal on escape key
			$(document).keyup(function (event) {
				if (event.keyCode === 27) { // Escape key
					closeModal();
				}
			});
		});

		function openModal(content) {
			$('#submissionCode').html(content);
			$('#codeModal').fadeIn(300);
		}

		function closeModal() {
			$('#codeModal').fadeOut(300, function () {
				$('#submissionCode').html('');
			});
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

<body class="bg-black text-white">
	<div class="min-h-screen">
		<?php include('Layout/header.php'); ?>
		<?php include('Layout/menu.php'); ?>

		<div class="container mx-auto px-4 py-8">
			<h1 class="text-2xl font-semibold text-center mb-8">User</h1>

			<div class="w-full overflow-x-auto shadow-lg rounded-lg">
				<table id="submissions" class="w-full min-w-full fade-in">
					<!-- Content will be loaded by AJAX -->
				</table>
			</div>
		</div>
	</div>

	<!-- Modal -->
	<div id="codeModal" class="modal">
		<div class="modal-content bg-black mx-auto my-8 p-6 rounded-lg max-w-4xl w-11/12 border border-gray-800">
			<div class="flex justify-between items-center mb-4">
				<h3 class="text-xl font-semibold text-white">Submission Code</h3>
				<button class="close-modal close-btn text-gray-400 hover:text-white text-2xl font-bold">&times;</button>
			</div>
			<div class="overflow-x-auto">
				<pre id="submissionCode" class="bg-opacity-10 bg-white p-4 rounded text-sm text-white"></pre>
			</div>
		</div>
	</div>

</body>

</html>