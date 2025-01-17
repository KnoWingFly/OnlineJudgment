<?php

/*
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

	session_start(); 
	
	unset($_SESSION['username2']);
	unset($_SESSION['password2']);
	unset($_SESSION['userid2']);
	unset($_SESSION['admin']);
	unset($_SESSION['isloggedin2']);
	
	echo "<meta http-equiv='Refresh' content='0; URL=login.php'/>";
?>
