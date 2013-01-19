<?php
include("common/db.php");
$params = $_REQUEST;
$action = $params['action'];
$output = Array();

switch ($action) {
    case "login" :
        login();
        break;

    case "close" :
        close();
        break;

    case "skip" :
        skip();
        break;

    case "revise" :
    case "confirm" :
        revise();
        break;

    case "download" :
        download();
        break;

    case "reasonids":
        loadReasons();
        break;
}

echo json_encode($output);

function loadReasons()
{
    clues();
}

function clues()
{
    echo executeCURL("http://180.179.49.217/tools/mobile_mri.php?" . $_SERVER["QUERY_STRING"], null);
    exit;
}
function skip()
{
    $merchant_id = $_REQUEST['merchantId'];
    $reasonId = $_REQUEST['reasonId'];
    $manifestId = $_REQUEST['manifest_id'];
    $stmt = "update clues_mri_manifest_details_mobile_app
				set close_manifest = 'Y', reason_id = '$reasonId',received_qty=0
				where merchant_id = '$merchant_id' and manifest_id= '$manifestId'";
    $result = db_query($stmt);

    global $output;
    $output['status'] = 'Success';
    $output['Reason'] = 'Merchant Skipped';
}
/**
 * To close the milkrun.
 */
function close()
{
    $merchant_id = $_REQUEST['merchantId'];
    $stmt = "update clues_mri_manifest_details_mobile_app
				set close_manifest = 'Y'
				where merchant_id = '$merchant_id'";
    $result = db_query($stmt);

    global $output;
    $output['status'] = 'Success';
    $output['Reason'] = 'Manifest Closed';
}
/**
 * Login API
 */

function login()
{
    echo "{'status':'Success', 'pid':'33'}";
}

/**
 * Download milkrun from Shopclues
 */
function download()
{
    clues();
}

/**
 * Validate manifest
 */
function validateManifest()
{
    global $output, $params;
    $sql = "select manifest_id,Date(date_created) as date_created from clues_order_manifest where manifest_type_id=3 and pickupboy_id='" . $params['pickupboyid'] . "' order by manifest_id desc limit 0,1";
    $result = db_get_row($sql);
    // Check if old manifest is still valid
    if ($result['manifest_id'] != $params['manifest_id']) {
        $output['status'] = 'Failed';
        $output['Reason'] = 'Manifest Changed fo' . $result['manifest_id'];
        echo json_encode($output);
        exit;
    }
    // Check if old manifest is unaltered
    $sql = "select o.company_id from clues_order_manifest_details m
					inner join cscart_orders o on o.order_id=m.order_id
					where manifest_id='" . $params['manifest_id'] . "' group by o.order_id order by
					o.company_id,o.order_id asc";
    $order_list = db_get_array($sql);
    // Read company IDs from main table
    $comp = array();
    foreach($order_list as $order) {
        if (!in_array($order['company_id'], $comp))
            $comp[] = $order['company_id'];
    }
    // Read companyIds from local table
    $sql = "select distinct(merchant_id) from clues_mri_manifest_details_mobile_app where manifest_id='" . $result['manifest_id'] . "'";
    $comp_ids = db_get_array($sql);
    $co = array();
    foreach($comp_ids as $comp_id) {
        $co[] = $comp_id['merchant_id'];
    }
    /*
	$diff=array_diff($comp,$co);
	if(!empty($diff)){
		$output['status']='Failed';
		$output['Reason']='Manifest Altered';
		echo json_encode($output);
		exit;
	}*/

}
/**
 * Validate manifest item wise
 */

function reviseItems()
{
    clues();
}

/**
 * COmmon function to revise manifest.
 */
function revise()
{
    validateManifest();

    global $output, $params;

    if (isset($params['item'])) {
        reviseItems();
        return;
    } else if (isset($params['orders'])) {
        reviseOrders();
        return;
    } else {
        $output['status'] = 'Failed';
        $output['message'] = 'Invalid Request';
    }
}

/**
 * *Function to load manifest for a merchant
 */
function loadManifest($mf, $mer)
{
    $sql = "select merchant_id, company_name, company_add, company_phone, manifest_id, manifest_creation_date, order_id, order_date, order_status, product, product_id, item_id, expected_qty, received_qty,reason_id
	from clues_mri_manifest_details_mobile_app
	where manifest_id='$mf'
	and merchant_id='$mer'";
    $res = db_get_array($sql);

    $count = 0;

    $outputArray = Array();
    foreach ($res as $item) {
        $row = Array();
        $row[0] = $item[merchant_id];
        // Status
        $row[] = "REVISED";
        if ($count != 0) {
            // Merchant Name
            $row[] = "";
            // Address
            $row[] = "";
            // Phone
            $row[] = "";
            // Manifest ID
            $row[] = "";
            // Manifest Date
            $row[] = "";
        } else {
            // Merchant Name
            $row[] = $item[company_name];
            // Address
            $row[] = $item[company_add];
            // Phone
            $row[] = $item[company_phone];
            // Manifest ID
            $row[] = $item[manifest_id];
            // Manifest Date
            $row[] = $item[manifest_creation_date];
        }
        $count++;

        $row[] = $item[order_id];
        $row[] = $item[order_date];
        $row[] = $item[order_status];
        $row[] = $item[product];
        $row[] = $item[product_id];
        $row[] = $item[item_id];
        $row[] = $item[expected_qty];
        $row[] = $item[received_qty];
        if ($item[received_qty] < $item[expected_qty]) {
            $row[] = "Partial Fill";
        } else {
            $row[] = "";
        }
        $row[] = $item[reason_id];
        $row[] = "";
        $row[] = "";
        $row[] = "";
        $row[] = "";

        $outputArray[] = $row;
    }
    return $outputArray;
}
/**
 * revise manifest orderwise
 */
function reviseOrders()
{
    global $output;
    $params = $_REQUEST;
    $orders = explode('|', $params['orders']);
    $orderArray = Array();
    for($i = 0; $i < count($orders); $i++) {
        $item = explode(',', $orders[$i]);
        $item1 = Array();
        $item1[orderId] = $item[0];
        $item1['itemId'] = $item[1];
        $item1['rec'] = $item[2];
        if (isset($item[3]))
            $item1['reason'] = $item[3];
        array_push($orderArray, $item1);
    }
    // Update received data
    foreach ($orderArray as $order) {
        $stmt = "update clues_mri_manifest_details_mobile_app
				set received_qty = '" . $order[rec] . "',
				reason_id = '" . $order[reason] . "'
				where order_id = '" . $order[orderId] . "'
				and item_id = '" . $order[itemId] . "'
				and manifest_id= '" . $params['manifest_id'] . "'
				and merchant_id='" . $params['merchantId'] . "'";
        // echo $stmt."\n";
        $result = db_query($stmt);
    }

    header('Content-Type: text/csv; charset=utf-8');
    // header("Content-Disposition: attachment; filename=mri_list".date('Y-m-d').".csv");
    header("Pragma: no-cache");
    header("Expires: 0");
    if (isset($params['revise'])) {
        echo arrayToCsv(loadManifest($params['manifest_id'], $params['merchantId']));
        exit;
    } else {
        $output['status'] = 'Success';
        $output['Reason'] = 'Milkrun Confirmed';
    }
}


function arrayToCsv($array)
{
    $csvHeader = "Merchant Id,Received Status,Merchant Name,Address,Phone,Manifest Id,Manifest Date,Order ID,Order Date,Order Status,Product Name,Product Id,Item Id,Expected QTY,Received QTY,Warning,ReasonId,signal1,msg1,signal2,msg2";

    $output = array();
    $output[] = $csvHeader;
    foreach ($array as $row) {
        $output[] = implode(",", $row);
    }
    return implode("\n", $output);
    // return implode( ",", $output );
}

?>