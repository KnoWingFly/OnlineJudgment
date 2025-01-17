
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
<!--
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
-->

<script type="text/javascript">
<!--

var currentTime = new Date("October 02, 2014 02:37:54");var startTime = new Date("October 02, 2014 01:15:00");var endTime = new Date("October 02, 2014 06:15:00");
var getLeaderInterval = 10000;
var getChatInterval = 1000;

var diff1 = startTime - currentTime;
var diff2 = endTime - currentTime;
var diff = 0;

function getLeaders()
{
	$.get("getleaders.php", function(data){
	  $("#leaders").html(data);
	  });
}

function getDetails()
{
	$.get("getdetails.php", function(data){
	  $("#details").html(data);
	  });
}

function getScores()
{
	$.get("getscore2.php", function(data){
	  $("#scores").html(data);
	  });
}

function getSubmissions()
{
	$.get("getsubmissions.php", function(data){
	  $("#submissions").html(data);
	  });
}

function getAnnouncements()
{
	$.get("getannouncements.php", function(data){
	  $("#announcements").html(data);
	  });
}

function getProblemStats()
{
	$.get("statistics/getproblemstats.php", function(data){
	  $("#problemstats").html(data);
	  });
}

function getLeader()
{
	$.get("statistics/getleader.php", function(data){
	  $("#leader").html(data);
	  });
}

function dispTime() 
{
	if(diff1>0)
	{
		$("#timeheading").text('Contest starts in');			
		diff = diff1;
	}
	else if(diff2>0)
	{
		$("#timeheading").text('Contest ends in');			
		diff = diff2;
	}
	else
	{
		$("#timeheading").text('Contest over');			
		diff = 0;
	}

	diff = Math.floor(diff/1000);

	var d = Math.floor(diff/(3600*24));
	diff = diff - d * 3600 *24;
	var h = Math.floor(diff/3600);
	var m = Math.floor((diff/60)%60);
	var s = Math.floor(diff%60);

	var hh = h;
	var mm = m;
	var ss = s;
	if(h<10) hh = '0' + h;
	if(m<10) mm = '0' + m;
	if(s<10) ss = '0' + s;

	var str = "<strong>"+hh+":"+mm+":"+ss+"</strong>";

	if(d > 1)
		str = "<strong>" + d + " days</strong>";
	else if(d == 1)
		str = "<strong>" + d + " day</strong>";

	$("#time").html(str);

	if(diff1 > 0)
	{
		diff1 = diff1-1000;
		if(diff1 <= 0)
		{
			alert('Contest has started');
			window.location.reload();
		}
	}

	if(diff2 > 0)
	{
		diff2 = diff2-1000;
		if(diff2 <= 0)
		{
			alert('Contest has ended');
			setTimeout("reloadPage()", 2000);
		}
	}
}

function reloadPage() {
	window.location.reload();
}

function messagebox(text)
{
	$(".messagebox").text(text);
	$(".messagebox").hide();
	$(".messagebox").slideDown("slow").oneTime("5s", function() { $(this).slideUp("fast") });
	$(".messagebox").click( function() { $(this).slideUp("fast"); } );
}

-->
</script>
<script type="text/javascript">
<!--

$(document).ready(
	function()
	{ 
		dispTime();
		getLeaders();
		getDetails();
		getScores();
		setInterval("dispTime()", 1000);  
		setInterval("getLeaders()", getLeaderInterval);  
		setInterval("getDetails()", getLeaderInterval);  
		setInterval("getScores()", getLeaderInterval);  
	} 
);

-->
</script>
	
</head>

<body class="menu4">
<!-- wrap starts here -->
<div id="wrap">
		
		<!--header -->
		<!--
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
-->

<div id="header">			
	<h1 id="logo-text"><a href="index.php">Programming Contest</a></h1>		
	<p id="slogan" style="margin-left: 420px">powered by <a style="color:white; text-decoration: none; font-weight: bold" href="https://sourceforge.net/projects/onj">ONJ</a></p>		
</div>
		
		<!-- menu -->	
		<!--
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
-->

<div id="menu">
	<ul>
		<li id="menu1"><a href="login.php">Login</a></li>										<li id="menu5"><a href="faq.php">FAQ</a></li>	
							</ul>
</div>					
			
		<!-- content-wrap starts here -->
		<div id="content-wrap">
				
			<div id="main">
				
				<table id="scores"> </table>
				
			</div>
			
			<div id="sidebar">
				<!--
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
-->

<h3 id="timeheading"></h3>
<ul class="sidemenu">
	<li id="time"></li>
</ul>

	
			</div>
		
		<!-- content-wrap ends here -->	
		</div>
					
		<!--footer starts here-->
		<div id="footer">
			<!--
* @copyright (c) 2008-2009 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
-->

<p> 
&copy;2008-2009 <strong>Nicolo Davis</strong> <br/> 
CSS by <a href="http://www.styleshout.com">styleshout</a> | ONJ uses <a href="http://jquery.com">jQuery</a> | <a href="http://code.google.com/p/flot/">flot</a> | <a href="http://unraveled.com/publications/css_tabs/">CSS Tabs 2.0</a>
</p>
		</div>	

<!-- wrap ends here -->
</div>

</body>
</html>
