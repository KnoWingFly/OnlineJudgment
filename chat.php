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
	<script type="text/javascript">
		let lastMessageId = 0;

		function getChat() {
			$.getJSON("getchat.php", { lastId: lastMessageId }, function (data) {
				if (data.messages && data.messages.length > 0) {
					data.messages.forEach(function (msg) {
						$("#chat").append(
							$('<div class="chat-message"></div>').text(
								msg.username + ': ' + msg.msg
							)
						);
					});

					lastMessageId = data.lastId;

					// Scroll to bottom
					const chatDiv = document.getElementById("chat");
					chatDiv.scrollTop = chatDiv.scrollHeight;
				}
			});
		}

		$(document).ready(function () {
			// Initial setup
			dispTime();
			getLeaders();
			getDetails();
			getChat();

			// Set intervals
			setInterval(dispTime, 1000);
			setInterval(getLeaders, getLeaderInterval);
			setInterval(getDetails, getLeaderInterval);
			setInterval(getChat, 2000); // 2 second interval for chat refresh

			// Chat form handling
			$(".chatform").focus();
			$(".chatform").keypress(function (e) {
				if (e.which == 13) {
					const msg = this.value.trim();
					if (msg !== "") {
						this.value = "";

						$.post("submitchat.php", { message: msg })
							.done(function () {
								
								getChat();
							})
							.fail(function (xhr) {
								alert("Failed to send message: " +
									(xhr.responseText || "Unknown error"));
							});
					}
				}
			});
		});
	</script>

</head>

<body class="menu6">
	<!-- wrap starts here -->
	<div id="wrap">

		<!--header -->
		<?php include('Layout/header.php'); ?>

		<!-- menu -->
<<<<<<< HEAD
		<?php include('Layout/menu.php'); ?>
=======
		<?php include('layout/menu.php'); ?>
>>>>>>> origin/master

		<!-- content-wrap starts here -->
		<div id="content-wrap">

			<div id="main">

				<div id="chat"> </div>

				<input maxlength="60" class="chatform" type="text" />

			</div>

			<div id="sidebar">
				<?php include('sidebar.php'); ?>
			</div>

			<!-- content-wrap ends here -->
		</div>

		<!--footer starts here-->
		<div id="footer">
			<?php include('Layout/footer.php'); ?>
		</div>

		<!-- wrap ends here -->
	</div>

</body>

</html>