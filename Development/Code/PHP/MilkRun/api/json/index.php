<?php
	date_default_timezone_set('Asia/Calcutta');
	$createddate=date( 'Y-m-d H:i:s');
	mysql_connect("localhost", "cabbeein_mgh", "test@123");
	mysql_select_db("cabbeein_mygrahak_pod");
	$user=mysql_real_escape_string($_GET['user']);
	$auth=mysql_real_escape_string($_GET['auth']);
	$imei=mysql_real_escape_string($_GET['imei']);
	$action=mysql_real_escape_string($_GET['action']);
	$output = Array();		

	//echo $action;
	
	if($action == "login"){
		$query= "SELECT userId, pwd, token FROM userMaster um WHERE um.deviceId = '$imei'"; 
	//	echo $query;
	
		$result=mysql_query($query) or die ( mysql_error() );
		$num=mysql_numrows($result);	
		
		//echo $num;
		if ($num < 1){
			header("Status: 401 Device Not registerted with BrainBox", true, 401 );
			exit;
		}else{
			$f1=mysql_result($result,0,"userId");
			$f2=mysql_result($result,0,"pwd");
			$f3=mysql_result($result,0,"token");

			if($f1==$user && $f2==$auth){
				$output['login']="OK";
				$output['token']=$f3;
			}else{
				header("Status: 401 Invalid login details", true, 401 );
				//$login['reason']="Invalid login details";
				exit;
			}
		}
	} else if($action == "update"){
		$token=mysql_real_escape_string($_GET['token']);
		$lat= $_GET['lat'];
		$lng= $_GET['lon'];
		$status= $_GET['ss'];
		$address= $_GET['add'];
		$response= $_GET['sr'];
		$orderId= $_GET['oid'];
		$city= $_GET['city'];
		$locationdate1= $_GET['locationtime'];
		$locationdate=date( 'Y-m-d H:i:s', $locationdate1/1000);


		$query="select count(1) as rowCount from userMaster um, orderMaster om 
			where om.orderId = '$orderId' 
			and um.deviceId='$imei'
			and um.rowId=om.userRowId";
		$result=mysql_query($query) or die (mysql_error());
		$num=mysql_numrows($result);
		//echo $num;
		if($num>0){
			
			$query = "UPDATE deviceLocation  SET isCurrent = 'N'  WHERE deviceId='$imei'";
			//echo $query;
			$result=mysql_query($query);

			$query = "INSERT INTO deviceLocation (deviceId, latitude, longitude, address, locationTime, createdDate, isCurrent) VALUES ('$imei','$lat','$lng','$address','$locationdate','$createddate','Y')";
	
	//echo $query;
			$result=mysql_query($query);

			$query = "UPDATE orderMaster  
				SET latitude='$lat',longitude='$lng',   
					location='$address', 
					status = '$status',
					response = '$response',
					lastUpdatedDate = '$createddate' WHERE orderId='$orderId'";
			//echo $query;
			$result=mysql_query($query) or die (mysql_error());

			$query = "UPDATE userMaster  
				SET latitude='$lat',longitude='$lng',   
					location='$address' 
					WHERE deviceId='$imei'";
					//echo $query;
			$result=mysql_query($query) or die (mysql_error());
			echo "OK";
		} else{
			echo "Error";
		}
	} else if($action == "pod"){
		$orders = Array();
		$query = "SELECT om.sequence as serial ,
				om.payMethod as pt,
				customerName as nm,
				amount as amt,
				status as ss,
				deliveryDate as dd,
				orderId as oid,
				customerPhone as ph,
				addressPin as \"pin\",
				shippingAddress as \"add\"
		FROM userMaster um, orderMaster om WHERE um.deviceId = '$imei' and om.userRowId = um.rowId" ;
		//echo $query;
		
		$result=mysql_query($query) or die (mysql_error());
		while($data = mysql_fetch_assoc($result)) {
			array_push($output, $data);
		}
		//echo json_encode($orders);
	}

	echo json_encode($output);
mysql_close();


