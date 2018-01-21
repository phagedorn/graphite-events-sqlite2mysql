<?php
####### graphite sqlite events migration to mysql 	#######
####### phillip.hagedorn@gmail.com 					#######
# php-sqlite3 php-mysql

####### read config files configuration 			#######

require "config.php.inc";
include "config.php.local.inc";
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
      $row[$i]['when'] = $res['when']; 
      $row[$i]['what'] = $res['what']; 
      $row[$i]['data'] = $res['data']; 
      $row[$i]['tags'] = $res['tags']; 
      $i++; 
      $insert[$i] = "INSERT INTO `events_event` VALUES (NULL, '".$res['when']."', '".$res['what']."', '".$res['data']."', '".$res['tags']."');";
  } 
# close sqlite file
$db->close(); 
unset($db); 

####### write to mysql

# open mysql connection
$link = mysqli_connect($dest_mysql_host, $dest_mysql_user, $dest_mysql_password, $dest_mysql_db);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
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


