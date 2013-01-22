<?php
/**
 * This File is create by GITHSR00025  
 * File name:-	onDotTracking.php
 * Author : BrainBox Network
 */
    include('htmlDomParser.php');
	$postNumber = "EH383559411IN";
	 $trackingDetails = "";
 $postNumber=$argv[1];
    getProductDetails('http://services.ptcmysore.gov.in/Speednettracking/Track.aspx?articlenumber='.$postNumber);
echo $trackingDetails['BookedFrom'].',"'.$trackingDetails['BookedDate'].'",'.$trackingDetails['DeliveredAt'].',"'.$trackingDetails['DeliverDate'].'"';
	   if(!strstr(strtoupper($trackingDetails['DeliverDate']),"NOT")) {
    	/*if Not Available word not found in status then this block is executed to call a curl request*/
    	//'India Post' is a carrier name
    	date_default_timezone_set('Asia/Kolkata');
    	$dateTime= "";
		$ts = "";
		try{
			$dateTime = new DateTime($trackingDetails['DeliverDate']); 
			$ts =  $dateTime->getTimestamp();
		}catch(Exception $e){
			$dateString = $trackingDetails['DeliverDate'];
			$dateString = str_ireplace("/",'-',$dateString);
			$dateString = $dateString." 3:30 PM";
			$dateTime = new DateTime($dateString); 
			$ts =  $dateTime->getTimestamp();
		}/**/	
    	updateDeliveryStatus('India Post',$postNumber,$ts);
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
function getProductDetails($url){
	 global $domain;
	 global $trackingDetails;
	//echo $url;
	//die();
	 $html = new simple_html_dom();
     $html->load_file($url);
	 $items = $html->find('td[align=left]');
	if(sizeof($items)>0){
	 $trackingDetails['BookedFrom'] = $items[0]->innertext();
	 $trackingDetails['BookedDate'] = $items[1]->innertext();
	 $trackingDetails['DeliveredAt'] = $items[2]->innertext();
	 $trackingDetails['DeliverDate'] = $items[3]->innertext();
	}else{
	 $trackingDetails['BookedFrom'] = "";
	 $trackingDetails['BookedDate'] = "";
	 $trackingDetails['DeliveredAt'] = "";
	 $trackingDetails['DeliverDate'] = "";
	}
}
?>