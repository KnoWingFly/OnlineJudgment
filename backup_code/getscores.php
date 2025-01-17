
<?php

/*
* @copyright (c) 2008 Nicolo John Davis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
	
	
	include('settings.php');

	$cn = mysql_connect('localhost', $DBUSER ,$DBPASS);
	mysql_select_db($DBNAME, $cn);
	/*
	//Calculate the submission stats of each user
	$result = mysql_query("select username, problemid, status, time from users, submissions where users.id = submissions.userid order by time desc");
	while($row = mysql_fetch_array($result))
	{
		if(!isset($success[ "$row[username]" ][ $row['problemid'] ])){
			$success[ "$row[username]" ][ $row['problemid'] ] = $row[status];
      $ttime["$row[username]" ][ $row['problemid']] = $row[time];
    }
	}
	*/
	$nProb = count($points);
	$dataSub = array();
	$idmaps = array();
	$ctr = 0;
	
	
	$query = "select id, username from users WHERE rank = 0";
	$result = mysql_query($query);
	while($row = mysql_fetch_array($result)){
		$id = $row['id'];
		$name = $row['username'];
		$idmaps[$id] = $ctr;
		
		$dataSub[$ctr] = array();
		$dataSub[$ctr]['username'] = $name;
		$dataSub[$ctr]['score'] = 0;
		$dataSub[$ctr]['time'] = 0;
		for($i=1; $i<=$nProb; $i++){
			$dataSub[$ctr]['time_'.$i] = 0;
			$dataSub[$ctr]['tries_'.$i] = 0;
			$dataSub[$ctr]['penalty_'.$i] = -1;
			$dataSub[$ctr]['stat_'.$i] = -1;
		}
		$ctr++;
	}
	
	$query = "select userid, problemid, status, time from submissions, users WHERE users.id = submissions.userid AND rank=0 ORDER BY time ASC";
	$result = mysql_query($query);
	while($row = mysql_fetch_array($result)){
		$id = $row['userid'];
		$probid = $row['problemid'];
		$stat = $row['status'];
		$time = $row['time'];
		$ctr = $idmaps[$id];
		
		if($dataSub[$ctr]['penalty_'.$probid] == -1){
			$dataSub[$ctr]['stat_'.$probid] = $stat;
			$dataSub[$ctr]['time_'.$probid] = (int)(($time - $startTime->getTimestamp())/60);
				
			if($stat != 0){
				$dataSub[$ctr]['tries_'.$probid]++;
			}else{
				$dataSub[$ctr]['tries_'.$probid]++;
				$dataSub[$ctr]['penalty_'.$probid] = ($dataSub[$ctr]['tries_'.$probid]-1) * 20;
				$dataSub[$ctr]['score'] += $points[$probid-1];
				$dataSub[$ctr]['time'] += $dataSub[$ctr]['penalty_'.$probid] + $dataSub[$ctr]['time_'.$probid];
			}
		}
	}
	//var_dump($dataSub);
	
	//BUBBLE SORT CUK
	$nTeams = count($dataSub);
	for($i=0; $i<$nTeams; $i++){
		for($j=0; $j<$nTeams-$i-1; $j++){
			if(($dataSub[$j]['score'] < $dataSub[$j+1]['score']) || (($dataSub[$j]['score'] == $dataSub[$j+1]['score']) && ($dataSub[$j]['time'] > $dataSub[$j+1]['time']))){
				$arrTemp = $dataSub[$j];
				$dataSub[$j] = $dataSub[$j+1];
				$dataSub[$j+1] = $arrTemp;
			}
		}
	}
	
	$class = "row-a";
	
	print '<tr><th>Position</th><th>User</th>';
	for($i=1 ; $i<=count($points) ; $i++)
		print "<th>$i</th>";
	print '<th>Score</th><th>Time</th></tr>';
	
	for($i=0; $i<$nTeams; $i++){
		$rankNow = $i+1;
		$name = $dataSub[$i]['username'];
		
		print "<tr ";
		if($dataSub[$i]['username'] == $username)
			print "style='font-weight: bold;' ";
		print "class='$class'><td>$rankNow</td><td>$name</td>";

		for($j=1 ; $j<=$nProb ; $j++){
			$time = $dataSub[$i]['time_'.$j];
			$tries = $dataSub[$i]['tries_'.$j];
			$penalty = $dataSub[$i]['penalty_'.$j];
			$stat = $dataSub[$i]['stat_'.$j];
			
			if($stat == 0){ // AC
				print "<td style='background-color:#34ff68'>$tries / $time(+$penalty)<img title='Accepted' src='images/checkmark.png' class='plain'/></td>";
			}
			else if($stat == 1){ //CE
				print "<td style='background-color:#e8f174'>$tries / $time<img title='Compile Error' src='images/page.gif' class='plain' style='margin-top: 3px;'/></td>";
			}
			else if($stat == 2){ //WA
				print "<td style='background-color:#ff6666'>$tries / $time<img title='Wrong Answer' src='images/wrongmark.gif' class='plain' style='margin-top:5px;'/></td>";
			}
			else if($stat == 3){ //TLE
				print "<td style='background-color:#e8f174'>$tries / $time<img title='Time Limit' src='images/clock.gif' class='plain' style='margin-top:4px;'/></td>";
			}
			else if($stat == 5){ //TLE
				print "<td style='background-color:#e8f174'>$tries / $time<img title='RE' src='images/page.gif' class='plain' style='margin-top:4px;'/></td>";
			}
			else{
				print "<td style='width:25px;'>--</td>";
			}
		}
		
		$score = $dataSub[$i]['score'];
		$time = $dataSub[$i]['time'];
		print "<td>$score</td><td>$time</td></tr>";

		if($class == "row-a") $class = "row-b";
		else $class = "row-a";
	}
?>				
