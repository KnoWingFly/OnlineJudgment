<?php

require( "config.php" );

$action = isset( $_POST['cmd'] ) ? $_POST['cmd'] : "";
 
 

switch ( $action ) {
  case 'login':
	login();
	break;
  case 'checkIn':
	checkIn();
	break;
}

function login(){
	$un=$_POST['username'];
	$pw=$_POST['password'];
	//connect to the db
	//run the query to search for the username and password the match
	$query = "SELECT * FROM users WHERE username = '$un' AND password = '$pw'";
	$result = mysql_query($query) or die("Unable to verify user because : " . mysql_error());
	//this is where the actual verification happens
	if(mysql_num_rows($result) > 0) echo 1;  // for correct login response
	else echo 0; // for incorrect login response
}

function checkIn(){
	$name = $_POST['name'];
	$num = $_POST['num'];
	$man = $_POST['man'];
	$note = $_POST['note'];
	$valType = $_POST['valType'];
	$venType = $_POST['venType'];
	$user = $_POST['user'];
	$time = $_POST['time'];
	$query = "INSERT INTO transaction(name,number,man,note,valType,venType,user,checkInTime) values('$name','$number','$man','$note','$valType','$venType','$user','$time');";
	$result = mysql_query($query) or die("Unable to verify user because : " . mysql_error());
	//this is where the actual verification happens
	if(mysql_num_rows($result) > 0) echo 1;  // for correct login response
	else echo 0; // for incorrect login response
}
?>
