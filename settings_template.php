<?php

/*
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

	//The user name of your database
	$DBUSER = '';
	//The password of your database
	$DBPASS = '';
	//The name of the database
	$DBNAME = '';

	//The point values of the problems
	//Set it to the values you wish to use
	//The number of elements in this array determines the number of problems
	$points = array(10,10,10,10,10);

	//Start time of the contest in the format 'YYYY-MM-DD HH:MM:SS'
	$startTime = date_create('2025-01-10 00:00:00');
	// kelebihan 10 jam.
	// jadi kalau mau mulai jam 15:00 maka kurangi 10:00.
	//2014-09-28 13:00:00
	//End time of the contest in the format 'YYYY-MM-DD HH:MM:SS'
	$endTime = date_create('2026-02-12 08:00:00');
	//2014-09-28 17:00:00
	//Interval between refreshes of the leaderboard (milliseconds)
	$getLeaderInterval = 10000;

	//Interval between refreshes of the chat room (milliseconds)
	$getChatInterval = 1000;

	//This is used to disable PHP error messages
	ini_set('display_errors', false);

	//You can use the variable $running to determine if the contest is running or not
	$time = date_create();
	$running = false;
	if($time >= $startTime && $time <= $endTime)
		$running = true;

	//The following variables hold values of the code and problem directories. These directories are renamed with random strings for security purposes.
	$CODEDIR = 'code';
	$PROBLEMDIR = 'problems';
?>
