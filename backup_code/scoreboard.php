<?php
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
<script type="text/javascript" src="jquery.min.js"></script>
<?php include('timer.php'); ?>
<script type="text/javascript">
<!--

$(document).ready(
	function()
	{ 
		getScores();
		setInterval("getScores()", getLeaderInterval);  
	} 
);

-->
</script>
	
</head>

<body class="menu4">
<!-- wrap starts here -->
<div id="wrap">
		
			
		<!-- content-wrap starts here -->
		<div id="content-wrap">
				
			<div id="main">
				
				<table id="scores"> </table>
				
			</div>
		
		<!-- content-wrap ends here -->	
		</div>

<!-- wrap ends here -->
</div>

</body>
</html>
