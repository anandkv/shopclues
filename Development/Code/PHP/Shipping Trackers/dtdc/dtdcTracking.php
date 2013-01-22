<?php
/**
 * This File is create by GITHSR00005  
 * File name:-	dtdcTracking.php
 * Author : BrainBox Network
 */

    include('htmlDomParser.php'); //Including Selector Base DOM pArser Of the Shelf
	$postNumber =  $argv[1]; // $argv[1] is commmand line parameter used
	$trackingDetails = "";  // Empty String will be used as buffer for buffer out put
    getProductDetails("http://www.dtdc.in/dtdc-corporate-web_liferay/trackingAction.do?method=submitTrackingIds&to.trackIdType=awb_no&to.awbNo=$postNumber");
echo $trackingDetails['BookedFrom'].',"'.$trackingDetails['BookedDate'].'",'.$trackingDetails['DeliveredAt'].',"'.$trackingDetails['DeliverDate'].'",'.$trackingDetails['Status'].'';
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
/***** code below is dev code intentionally left for time being****/
//echo date("D, M d, Y g:i A",$dateTime->getTimestamp());
//	echo $a = strptime('Nov 03, 2012', '%M %d %Y');
//print_r($a);
//$time = mktime();mktime
/************/
if(strstr(strtoupper($trackingDetails['Status']),"DELIVERED") !== false) {
    	/*if delivered word is found in status then this block is executed to call a curl request*/
    	//'DTDC Tracking' is a carrier name
    	updateDeliveryStatus('DTDC Tracking',$postNumber,$ts);
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

/**
	 * This function is for scrapping the data from given url as per dtdc pattern
	 *
	 * @param string  $url
	 * @return Will be an Array with details in it
	 * @author GITHSR00005
	 * @example $Value = DTDCTrackingURL With awbNo in URL return Array
	 */

function getProductDetails($url){
	 global $domain;
	 global $trackingDetails;
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
	$decoded = json_decode($output,true);
     $html->load($output);
	 $items = $html->find('table[id=box-table-a]');
	if(sizeof($items)>0){
	 $trackingDetails['BookedFrom'] = "";
	 $trackingDetails['BookedDate'] = "";
	 $trackingDetails['DeliveredAt'] = trim(strip_tags($items[0]->children(1)->children(0)->children(3)->innertext()));
	 $trackingDetails['Status'] 	= trim(strip_tags($items[0]->children(1)->children(0)->children(1)->innertext()));
	 $trackingDetails['DeliverDate'] = trim(str_ireplace("&nbsp;"," ", strip_tags($items[0]->children(1)->children(0)->children(2)->innertext())));
	 $trackingDetails['DeliverDate'] = str_ireplace("\r","",$trackingDetails['DeliverDate']);
	 $trackingDetails['DeliverDate'] = str_ireplace("\n","",$trackingDetails['DeliverDate']);
	 $trackingDetails['DeliverDate'] = str_ireplace("\t","",$trackingDetails['DeliverDate']);

	}else{
	 $trackingDetails['BookedFrom'] = "";
	 $trackingDetails['BookedDate'] = "";
	 $trackingDetails['DeliveredAt'] = "";
	 $trackingDetails['DeliverDate'] = "";
	}
}
?>