<!--
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
-->

<div id="menu">
	<ul>
		<?php if(!isset($_SESSION['isloggedin2'])) print '<li id="menu1"><a href="/newOJ/login.php">Login</a></li>'; ?>
		<?php if(isset($_SESSION['isloggedin2'])) print '<li id="menu2"><a href="/newOJ/index.php">Problems</a></li>'; ?>
		<?php if(isset($_SESSION['isloggedin2'])) print '<li id="menu3"><a href="/newOJ/submissions.php">Submissions</a></li>'; ?>
		<?php if(isset($_SESSION['isloggedin2'])) print '<li id="menu4"><a href="/newOJ/scoreboard.php">Scoreboard</a></li>'; ?>
		<li id="menu5"><a href="/newOJ/faq.php">FAQ</a></li>	
		<?php if(isset($_SESSION['isloggedin2'])) print '<li id="menu6"><a href="/newOJ/chat.php">Chat</a></li>'; ?>
		<?php if(isset($_SESSION['admin'])) print '<li id="menu7"><a href="/newOJ/admin.php">Admin</a></li>'; ?>
		<?php if(isset($_SESSION['isloggedin2'])) print '<li id="menu8"><a href="/newOJ/personal.php">Personal</a></li>'; ?>
		<?php if(isset($_SESSION['isloggedin2'])) print '<li id="menu9"><a href="/newOJ/logout.php">Logout</a></li>'; ?>
			
	</ul>
</div>					
