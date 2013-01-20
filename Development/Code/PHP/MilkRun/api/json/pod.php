<?php
require("../common/db.php");

$action = $_REQUEST['action'];
$timeFormat = "%Y-%m-%d %H:%i:%s";

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
	$pid =  $_REQUEST["pid"];

	$query=" select 
	pb.imei,
	ifnull(mf.manifest_id,'') manifest_id ,
	pb.pickupboy,
	pb.pickupboy_id,
	ifnull(from_unixtime(max(mf.logout_dt), '$timeFormat'),'') logout_dt,
	ifnull(from_unixtime(max(mf.login_dt), '$timeFormat'),'') login_dt,
	ifnull(from_unixtime(max(mf.manifest_date), '%d-%m-%Y'),'') date,
	ifnull(max(mfd.expected_qty),0) expected_qty,
	ifnull(max(mfd.received_qty),0) received_qty
from
    (select  * from clues_pickupboy where imei is not null and ( pickupboy_id = '$pid' or '$pid' = '')) pb
    left outer join
    (SELECT 
        manifest_id,
            company_id,
            pickupboy_id,
            case
                when status = 'Download' then status_date
            end as download_date,
            case
                when status = 'Assigned' then status_date
            end as manifest_date,
            case
                when status = 'logout' then status_date
            end as logout_dt,
			case
                when status = 'login' then status_date
            end as login_dt
    FROM
        clues_mri_manifest mf
	where 1=1
		and (mf.status_date >= '$fromDate' or '$fromDate' = '' )
		and (mf.status_date <= '$toDate' or '$toDate' = '' )
		and (mf.manifest_id = '$manifestId' or '$manifestId' = '')

) mf ON pb.pickupboy_id = mf.pickupboy_id
left outer join (

select 
	manifest_id,
	merchant_id as company_id,
	sum(expected_qty)  expected_qty, 
	sum(received_qty) received_qty
	from clues_mri_manifest_details_mobile_app
	where 1=1
		and company_name like '%".$searchCriteria[mName]."%'
		and company_add like '%".$searchCriteria[mAddress]."%' 
		and company_add like '%".$searchCriteria[mCity]."%' 
	group by merchant_id, manifest_id
) mfd
on mfd.manifest_id = mf.manifest_id and mf.company_id=mfd.company_id
			
			
			

group by mf.manifest_id ,  pb.pickupboy_id";

//	echo $query;
	return db_get_array($query);
}

function listProducts(){
	global $searchCriteria, $fromDate, $toDate;
	$company_id = $_REQUEST['company_id'];
	$manifest_id = $_REQUEST['manifestId'];
	$pid = $_REQUEST['pid'];
	$query="select * from clues_mri_manifest_details_mobile_app mfd 
			left outer join clues_reasons cr on mfd.reason_id = cr.rowId 
			join  (
			
			SELECT 
        manifest_id,
            company_id,
            pickupboy_id,
			status,
            status_date
            
    FROM
        clues_mri_manifest mf
where status_date = (select max(status_date) from clues_mri_manifest mf1 
where mf1.company_id= mf.company_id
and mf1.manifest_id = mf.manifest_id 
and mf1.pickupboy_id = mf.pickupboy_id 
)
and mf.pickupboy_id = '$pid'
			
			) mf ON mfd.manifest_id = mf.manifest_id and mf.company_id = mfd.merchant_id
			where mf.company_id = '$company_id'
			and mf.manifest_id='$manifest_id'
			and mfd.company_name like '%".$searchCriteria[mName]."%'
			and mfd.company_add like '%".$searchCriteria[mAddress]."%' 
			and mfd.company_add like '%".$searchCriteria[mCity]."%' 
			and (mfd.order_id = '".$searchCriteria[orderId]."' or '".$searchCriteria[orderId]."' = '')
			and (mfd.product_id = '".$searchCriteria[productId]."' or '".$searchCriteria[productId]."' = '')
		";

//			echo $query;
	return db_get_array($query);
}

function listMerchants(){
	global $searchCriteria, $fromDate, $toDate, $timeFomat;
	$pid = $searchCriteria['pid'];
	$query="
	
	select 
	mfd.company_id,
	mfd.company_name,
mfd.company_add,
	ifnull(SUM(mfd.expected_qty),0) expected_qty,
	ifnull(SUM(mfd.received_qty),0) received_qty,
	mf.status,
	mf.status_date
from
    (SELECT 
        manifest_id,
            company_id,
            pickupboy_id,
			status,
            status_date
            
    FROM
        clues_mri_manifest mf
where status_date = (select max(status_date) from clues_mri_manifest mf1 
where mf1.company_id= mf.company_id
and mf1.manifest_id = mf.manifest_id 
and mf1.pickupboy_id = mf.pickupboy_id 
)
and mf.pickupboy_id = '$pid'
) mf join (

select 
manifest_id,
company_name ,
company_add as company_add,
merchant_id as company_id,
sum(expected_qty)  expected_qty, 
sum(received_qty) received_qty
from clues_mri_manifest_details_mobile_app mfd1
where mfd1.company_name like '%".$searchCriteria[mName]."%'
			and mfd1.company_add like '%".$searchCriteria[mAddress]."%' 
			and mfd1.company_add like '%".$searchCriteria[mCity]."%' 
			and (mfd1.order_id = '".$searchCriteria[orderId]."' or '".$searchCriteria[orderId]."' = '')
			and (mfd1.product_id = '".$searchCriteria[productId]."' or '".$searchCriteria[productId]."' = '')
			
group by merchant_id, company_id
) mfd
on mfd.manifest_id = mf.manifest_id and mf.company_id=mfd.company_id
			where 1=1
			group by mfd.company_id";
			
	return db_get_array($query);
}



?>