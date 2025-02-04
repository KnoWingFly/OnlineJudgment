<?php

/*
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

	session_start();
	if(!isset($_SESSION['isloggedin2']))
	{
		echo "<meta http-equiv='Refresh' content='0; URL=login.php' />";
		exit(0);
	}
	else
	{
		$username = $_SESSION['username2'];
		$userid = $_SESSION['userid2'];
	}

	include('settings.php');

	$cn = mysql_connect('localhost', $DBUSER ,$DBPASS);
	mysql_select_db($DBNAME, $cn);

	//Calculate the submission stats of each user
	$result = mysql_query("select username, problemid, status, time from users, submissions where users.id = submissions.userid order by time desc");
	while($row = mysql_fetch_array($result))
	{
		if(!isset($success[ "$row[username]" ][ $row[problemid] ])){
			$success[ "$row[username]" ][ $row[problemid ] ] = $row[status];
      $ttime["$row[username]" ][ $row[problemid ]] = $row[time];
    }
	}

	//Get scores of users
	$query = "select * from scores";
	$result = mysql_query($query);

	$class = "row-a";
	$pos = 1;
	
	print '<tr><th>Position</th><th>User</th>';
	for($i=1 ; $i<=count($points) ; $i++)
		print "<th>$i</th>";
	print '<th>Score</th></tr>';

	if($result)
	{
		while($row = mysql_fetch_array($result))
		{											
			//Print only normal users (not admins)
			if($row[rank] == 0)
			{
				print "<tr ";
				if($row[username] == $username)
					print "style='font-weight: bold;' ";
				print "class='$class'><td>$pos</td><td>$row[username]</td>";

				for($i=1 ; $i<=count($points) ; $i++)
				{
					if($success[ "$row[username]" ][$i] == '0'){
						$ts = (int)(($ttime[ "$row[username]" ][$i] - $startTime->getTimestamp()) / 60);
						print "<td style='background-color:#34ff68'>$ts<img title='Accepted' src='images/checkmark.png' class='plain'/></td>";
					}else if($success[ "$row[username]" ][$i] == '1'   ){
						$ts = (int)(($ttime[ "$row[username]" ][$i] - $startTime->getTimestamp()) / 60);
						print "<td style='background-color:#e8f174'>$ts<img title='Compile Error' src='images/page.gif' class='plain' style='margin-top: 3px;'/></td>";
					}
					else if($success[ "$row[username]" ][$i] == '2'){
						$ts = (int)(($ttime[ "$row[username]" ][$i] - $startTime->getTimestamp()) / 60);
						print "<td style='background-color:#ff6666'>$ts<img title='Wrong Answer' src='images/wrongmark.gif' class='plain' style='margin-top:5px;'/></td>";
          }
					else if($success[ "$row[username]" ][$i] == '3'){
						$ts = (int)(($ttime[ "$row[username]" ][$i] - $startTime->getTimestamp()) / 60);
						print "<td style='background-color:#e8f174'>$ts<img title='Time Limit' src='images/clock.gif' class='plain' style='margin-top:4px;'/></td>";
          }
		  else if($success[$success[ "$row[username]" ][$i] == '5' ){
						$ts = (int)(($ttime[ "$row[username]" ][$i] - $startTime->getTimestamp()) / 60);
						print "<td style='background-color:#e8f174'>$ts<img title='Runtime Error' src='images/page.gif' class='plain' style='margin-top: 3px;'/></td>";
					}
					else
					{
						print "<td style='width:25px;'>--</td>";
					}
				}
				print "<td>$row[score]</td></tr>";
				$pos++;

				if($class == "row-a") $class = "row-b";
				else $class = "row-a";
			}
		}
	}
	else
	print 'No';
?>				
