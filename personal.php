<?php

/*
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

session_start();
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
<!--

$(document).ready(function(){ 
		setInterval("dispTime()", 1000); 
		dispTime(); 

		
		$('form').submit( function() {
				var oldPass = $("input[name='old_pass']").attr('value');
				var newPass = $("input[name='new_pass']").attr('value');
				var confPass = $("input[name='conf_pass']").attr('value');
				
				$('#error').hide();

				if(oldPass.length == 0)
				{
					$('#error').text('Old Password field cannot be left blank');
					$('#error').attr('class', 'error');
					$('#error').fadeIn('slow');
					return false;
				}
				if(newPass.length == 0)
				{
					$('#error').text('New Password field cannot be left blank');
					$('#error').attr('class', 'error');
					$('#error').fadeIn('slow');
					return false;
				}
				if(confPass.length == 0)
				{
					$('#error').text('Confirm Password field cannot be left blank');
					$('#error').attr('class', 'error');
					$('#error').fadeIn('slow');
					return false;
				}

				

			});

	} );

-->
</script>
	
</head>

<body class="menu1">
<!-- wrap starts here -->
<div id="wrap">
		
		<!--header -->
		<?php include('header.php'); ?>
		
		<!-- menu -->	
		<?php include('menu.php'); ?>
			
		<!-- content-wrap starts here -->
		<div id="content-wrap">
				
			<div id="main">


<?php
if(isset($_SESSION['isloggedin']) == "Yes" && $_POST['old_pass'])
{
	$cn = mysql_connect('localhost', $DBUSER, $DBPASS);
	mysql_select_db($DBNAME, $cn);
	$id = $_SESSION['userid'];
	$password = $_POST['old_pass'];

	$query = "select * from `users` where `id` = '$id' and `password` = '$password'";
	$logged = mysql_query($query);
	$logged = mysql_fetch_array($logged);
	
	if(count($logged) > 0){
		$new_pass = $_POST['new_pass'];
		$query = "update users set password='$new_pass' where id='$id'";
		mysql_query($query);
	}	
	else{
		print '<div id="error" class="error" style="display: one">Error changing pasword</div>';
		$printedError = true;
	}
	mysql_close($cn);
}

		if($printedError == false)
			print '<div id="error" class="success" style="display: none">Error</div>';

		echo <<<EOL
		<form style="position: relative; margin-left: auto; margin-right: auto; width: 250px; background-color: #ECF1EF;" action="personal.php" method="post">			
		<p>			
		<label>Old Password</label>
		<input name="old_pass" value="" type="password" size="30" />
		<label>New Password (if needed to be changed)</label>
		<input name="new_pass" value="" type="password" size="30" />	
		<label>Confirm New Password</label>
		<input name="conf_new_pass" value="" type="password" size="30" />				
		
		<br />	
		<div style="position: relative; width: 100px; text-align: center; margin-left: auto; margin-right: auto; margin-bottom: 10px">
			<input id="loginbutton" class="button" type="submit" name="login" value="Login" />		
		</div>
		</p>		

		</form>	

		
EOL;


?>


		<!-- main ends here -->
		</div>

		<div id="sidebar">

		<h3 id="timeheading"></h3>

		<ul class="sidemenu">
		<li id="time"></li>
		</ul>

		</div>
		
		<!-- content-wrap ends here -->	
		</div>
					
		<!--footer starts here-->
		<div id="footer">
			<?php include('footer.php'); ?>
		</div>	

<!-- wrap ends here -->
</div>

</body>
</html>
