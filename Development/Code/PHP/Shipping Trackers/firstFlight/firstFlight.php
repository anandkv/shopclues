<?php
/**
 * This File is create by GITHSR00025  
 * File name:-	onDotTracking.php
 * Author : BrainBox Network
 */

    include('htmlDomParser.php');
	$postNumber = $argv[1];
	 $trackingDetails = "";
    getProductDetails('http://www.firstflight.net/n_contrac_new.asp?tracking1='.$postNumber);
	echo $trackingDetails['BookedFrom'].',"'.$trackingDetails['BookedDate'].'",'.$trackingDetails['DeliveredAt'].',"'.$trackingDetails['DeliverDate'].'",'.$trackingDetails['Status'].'';
    if(strstr(strtoupper($trackingDetails['Status']),"DELIVERED") !== false) {
    	/*if delivered word is found in status then this block is executed to call a curl request*/
    	//'First Flight' is a carrier name
    	$dateTime= "";
		$ts = "";
date_default_timezone_set('Asia/Kolkata');
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
	updateDeliveryStatus('First Flight',$postNumber,$ts);
   	}
   	function updateDeliveryStatus($CARRIER,$AWBNUMBER,$TimeStamp){
		echo $update_url = "http://180.179.49.217/tools/shipment_track_update.php?carrier_name=".$CARRIER."&awbno=".$AWBNUMBER."&carrier_status=Delivered&update_date=".$TimeStamp;
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
function getProductDetails($url){
	 global $domain;
	 global $trackingDetails;
	//echo $url;
	//die();
	 $html = new simple_html_dom();
	 $ch = curl_init();
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$output = curl_exec($ch);
	curl_close($ch);
     $html->load($output);
	 $items = $html->find('tr[class=tahoma_text01]');
	 //var_dump($items);
//	echo $items = $items[0]->children(0)->innertext();

	if(sizeof($items)>0){
	 $trackingDetails['BookedFrom'] = $items[0]->children(2)->innertext();
	 $trackingDetails['BookedDate'] = $items[0]->children(1)->innertext();
	 $trackingDetails['DeliveredAt'] = $items[0]->children(3)->innertext();
	 $trackingDetails['Status'] 	= strip_tags($items[0]->children(4)->innertext());
	 $trackingDetails['DeliverDate'] = $items[0]->children(7)->innertext();
	}else{
	 $trackingDetails['BookedFrom'] = "";
	 $trackingDetails['BookedDate'] = "";
	 $trackingDetails['DeliveredAt'] = "";
	 $trackingDetails['DeliverDate'] = "";
	}/**/
	//return $output;
}
?>