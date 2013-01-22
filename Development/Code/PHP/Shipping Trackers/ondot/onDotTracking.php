<?php
/**
 * This File is create by GITHSR00025  
 * File name:-	onDotTracking.php
 * Author : BrainBox Network
 */
    include('htmlDomParser.php');
  	 global $trackingDetails;
  	 $postNumber = $argv[1];
    getProductDetails("http://ondot.co/TrackResult.asp",$postNumber);
	echo '"'.$trackingDetails['StatusDate'].'",'.$trackingDetails['DeliveredAt'].','.$trackingDetails['Status'];
  if(strstr(strtoupper($trackingDetails['Status']),"DELIVERED") !== false) {
    	/*if delivered word is found in status then this block is executed to call a curl request*/
    	//'On Dot Tracking' is a carrier name
    	date_default_timezone_set('Asia/Kolkata');
    	$dateTime= "";
		$ts = "";
		try{
			$dateTime = new DateTime($trackingDetails['DeliverDate']); 
			$ts =  $dateTime->getTimestamp();
		}catch(Exception $e){
			$dateString = $trackingDetails['DeliverDate'];
			$dateString = substr($dateString, 0, -8);
			$dateString = $dateString." 3:30 PM";
			$dateTime = new DateTime($dateString); 
			$ts =  $dateTime->getTimestamp();
		}	
    	updateDeliveryStatus('On Dot Tracking',$postNumber,$ts);
   	}
   	function updateDeliveryStatus($CARRIER,$AWBNUMBER,$TimeStamp){
		$update_url = "http://180.179.49.217/tools/shipment_track_update.php?carrier_name=".$CARRIER."&awbno=".$AWBNUMBER."&carrier_status=Delivered&update_date=".$TimeStamp;
	    $ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_URL, $update_url);
		curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		//$output = 
		curl_exec($ch);
		curl_close($ch);
	}
function getProductDetails($url,$tccode){
	 global $domain;
	 global $trackingDetails;
	  $trackcode = $tccode;
//echo "trackType=multy&txtTrack=$trackcode&submit1:Track";
	//die();
	 $html = new simple_html_dom();
	 $ch = curl_init();
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POSTFIELDS,  "trackType=multy&txtTrack=$trackcode&submit1:Track");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	//echo 
	$output = curl_exec($ch);
	curl_close($ch);
	//die();
	//$decoded = json_decode($output,true);
     $html->load($output);
	 $items = $html->find('td[class=text]');
$data =	 $items[22]->innertext();
$res = explode("<td class=text>", $data);

	 //var_dump($items);
//	 print_r($res);
	if(sizeof($items)>0){
	 $trackingDetails['StatusDate'] = $res[5];
	 $trackingDetails['DeliveredAt'] = $res[6];
	 $trackingDetails['Status'] 	= $res[7];
	}else{
		$trackingDetails['StatusDate'] = "";
		$trackingDetails['DeliveredAt'] = "";
		$trackingDetails['Status'] 	= "";
	}/**/
}
?>