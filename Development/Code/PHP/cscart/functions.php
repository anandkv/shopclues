<?php 

function getresult($query,$type=1) {
	   	$returnArray = array();
   		$queryResult = mysql_query($query);
   		while($resultRow = mysql_fetch_array($queryResult,$type)) {
		   	$returnArray[] = $resultRow;  	
   	 }
   	 return $returnArray;
   }
   function connectDb() {
   		$linkToserver =  mysql_connect('localhost','root','root');
   		$linkDb = 	   	mysql_select_db('scdb',$linkToserver);
   		return $linkToserver;
   }
   function closeDb($link) {
   		mysql_close($link);
   }
?>