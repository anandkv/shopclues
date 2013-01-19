<?php
require("../common/db.php");

$action = $_REQUEST['action'];
$timeFomat = "%Y-%m-%d %H:%m:%s";

$searchCriteria = $_REQUEST;

$searchCriteria['userId'] = $_REQUEST["userId"];
$searchCriteria['zone'] = $_REQUEST["zone"];
$searchCriteria['fromDate'] = $_REQUEST["fromDate"];
$searchCriteria['mAddress'] = $_REQUEST["mAddress"];
$searchCriteria['mCity'] =  $_REQUEST["mCity"];
$searchCriteria['mName'] =  $_REQUEST["mName"];
$searchCriteria['manifestId'] =  $_REQUEST["manifestId"];
$searchCriteria['pbName'] =  $_REQUEST["pbName"];
$searchCriteria['toDate']	=  $_REQUEST["toDate"];
$toDate = strtotime ($searchCriteria['toDate']);
$fromDate = strtotime ($searchCriteria['fromDate']);

switch($action){
	case "podSummary":
		$output = podSummary();
		break;
	case "listProducts":
		$output = listProducts();
		break;
	case "listOrders":
		$output = listOrders();
		break;
	case "listMerchants":
		$output = listMerchants();
	break;
	case "MilkRunSummary":
		$output = milkRunSummary();
		break;
	}

echo json_encode($output);


function milkRunSummary(){
	global $searchCriteria;

	$output = Array();
	$pickupBoySummary = podSummary();	

	foreach ($pickupBoySummary as $pickupBoy){
		$record = Array();
		$record[pickupboy] = $pickupBoy;
		$searchCriteria[pid] = $pickupBoy[pickupboy_id];
		$merchants = listMerchants();
		$record[merchants] = $merchants;
		$output[$pickupBoy[pickupboy_id]] =  $record;
	}
	//print_r($output);
	return $output;
}

function podSummary(){
	global $searchCriteria, $fromDate, $toDate, $timeFomat;
	
	$mAddress = $_REQUEST["mAddress"];
	$mCity =  $_REQUEST["mCity"];
	$manifestId =  $_REQUEST["manifestId"];
	$pbName =  $_REQUEST["pbName"];

	$query="SELECT mf.pickupboy_id ,pb.pickupboy, mf.manifest_id,
			case mf.logout_date
			when null then ''
			when 0 then ''
			else FROM_UNIXTIME(mf.logout_date, '%Y-%m-%d %H:%m:%s')
			END  logout_dt, 
			case mf.login_date
			when null then ''
			when 0 then ''
			else FROM_UNIXTIME(mf.login_date, '%Y-%m-%d %H:%m:%s')
			END  login_dt,
			FROM_UNIXTIME(dispatch_date, '%d-%m-%Y') AS date, SUM( received_qty ) received_qty, SUM( expected_qty ) expected_qty
						FROM clues_mri_manifest_details_mobile_app mfd
						JOIN clues_mri_manifest mf ON mfd.manifest_id = mf.manifest_id
						JOIN clues_pickupboy pb ON mfd.pickupboy_id = pb.pickupboy_id


			where (mf.manifest_id = '$manifestId' or '$manifestId' = '')
			and (mfd.pickupboy_id = '$pbName' or '$pbName' = '')
			and mfd.company_name like '%".$searchCriteria[mName]."%'
			and mfd.company_add like '%".$searchCriteria[mAddress]."%' 
			and mfd.company_add like '%".$searchCriteria[mCity]."%' 
			and (mf.dispatch_date >= '$fromDate' or '$fromDate' = '' )
			and (mf.dispatch_date <= '$toDate' or '$toDate' = '' )
		GROUP BY pickupboy_id, manifest_id";
	//echo $query;
	return db_get_array($query);
}

function listProducts(){
	global $searchCriteria, $fromDate, $toDate;
	$merchant_id = $_REQUEST['merchant_id'];
	$query="select * from clues_mri_manifest_details_mobile_app mfd 
			left outer join clues_reasons cr on mfd.reason_id = cr.rowId 
			left outer join  clues_mri_manifest mf ON mfd.manifest_id = mf.manifest_id
			where mfd.merchant_id = '$merchant_id'
			and mfd.company_name like '%".$searchCriteria[mName]."%'
			and mfd.company_add like '%".$searchCriteria[mAddress]."%' 
			and mfd.company_add like '%".$searchCriteria[mCity]."%' 
			
			and (mfd.order_id = '".$searchCriteria[orderId]."' or '".$searchCriteria[orderId]."' = '')
			and (mfd.product_id = '".$searchCriteria[productId]."' or '".$searchCriteria[productId]."' = '')
			
			and (mf.dispatch_date >= '$fromDate' or '$fromDate' = '' )
			and (mf.dispatch_date <= '$toDate' or '$toDate' = '' )
			";

//			echo $query;
	return db_get_array($query);
}

function listMerchants(){
	global $searchCriteria, $fromDate, $toDate, $timeFomat;
	$pid = $searchCriteria['pid'];
	$query="select 
    merchant_id,
    case mfd.close_date
        when null then ''
        when 0 then ''
        else FROM_UNIXTIME(mfd.close_date, '%Y-%m-%d %H:%m:%s')
    END close_dt,
    company_name,
    status,
    company_add,
    sum(expected_qty) expected_qty,
    sum(received_qty) received_qty
from
    clues_mri_manifest_details_mobile_app mfd
        left outer join
    clues_mri_manifest mf ON mfd.manifest_id = mf.manifest_id
			where mfd.pickupboy_id = '$pid'
			and mfd.company_name like '%".$searchCriteria[mName]."%'
			and mfd.company_add like '%".$searchCriteria[mAddress]."%' 
			and mfd.company_add like '%".$searchCriteria[mCity]."%' 
			and (mfd.order_id = '".$searchCriteria[orderId]."' or '".$searchCriteria[orderId]."' = '')
			and (mfd.product_id = '".$searchCriteria[productId]."' or '".$searchCriteria[productId]."' = '')
			and (mf.dispatch_date >= '$fromDate' or '$fromDate' = '' )
			and (mf.dispatch_date <= '$toDate' or '$toDate' = '' )
			group by company_name , company_add, merchant_id, status";
	return db_get_array($query);
}



?>