<?php
####### graphite sqlite events migration to mysql 	#######
####### phillip.hagedorn@gmail.com 					#######
# php-sqlite3 php-mysql

####### read config files configuration 			#######

require "config.php.inc";
include "config.php.local.inc";

function mysql_escape_mimic($inp) { 
    if(is_array($inp)) 
        return array_map(__METHOD__, $inp); 

    if(!empty($inp) && is_string($inp)) { 
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp); 
    } 

    return $inp; 
} 


# open mysql connection
$link = mysqli_connect($dest_mysql_host, $dest_mysql_user, $dest_mysql_password, $dest_mysql_db);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

####### read from sqlite
# open sqlite files configure as $sqlite_file
$db = new SQLite3($sqlite_file); 

# read events table from sqlite file
$sql = "SELECT * FROM events_event";
$result = $db->query($sql);
$row = array(); 
$insert = array();
$i = 0; 
 while($res = $result->fetchArray(SQLITE3_ASSOC)){ 
     if(!isset($res['id'])) continue; 
      $row[$i]['when'] = mysql_escape_mimic($res['when']); 
      $row[$i]['what'] = mysql_escape_mimic($res['what']); 
      $row[$i]['data'] = mysql_escape_mimic($res['data']); 
      $row[$i]['tags'] = mysql_escape_mimic($res['tags']); 
      $i++; 
       $sql = "SELECT count(*) as duplicate from events_event where what=\"".$res['what']."\" and data=\"".$res['data']."\" and tags=\"".$res['tags']."\";";
        if( $mysql_result = mysqli_query($link, $sql)){
          $row = mysqli_fetch_assoc($mysql_result);
          if ($row['duplicate'] != 0){
            continue;
          }else{
            $insert[$i] = "INSERT INTO `events_event` VALUES (NULL, '".$res['when']."', '".$res['what']."', '".$res['data']."', '".$res['tags']."');";
          }
        } else{
            echo "ERROR: not able to execute $sql. " . mysqli_error($link);
            echo "\n";
        } 
  } 
  echo "Inserts:".count($insert);
# close sqlite file
$db->close(); 
unset($db); 


# write to dest mysql graphite events table
foreach ($insert as $sql) {
 	echo "INSERTING:".$sql;
    echo "\n";

	if(mysqli_query($link, $sql)){
	    echo "Records inserted successfully.";
	} else{
	    echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
	}
}

# close mysql connection
// Close connection
mysqli_close($link);


