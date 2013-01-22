<?php
$display = "none";
	/**
	 * 
	 * DB Connection Configuration
	 * @var $connection array type global for use in functions
	 */
	$connection ['servername'] = "localhost";
	$connection ['username'] = "root";
	$connection ['password'] = "root";
	$connection ['dbname'] = "scdb";
	mysql_select_db ( $connection ['dbname'], mysql_connect ( $connection ['servername'], $connection ['username'], $connection ['password'] ) );
	$tableName = "clues_billing_fee_config";
/**
 * Script for db changes alter table 

	ALTER TABLE `clues_billing_fee_config` ADD `fee_group_id` BIGINT NOT NULL ,
	ADD `region_code` varchar(1024) NOT NULL,
	ADD `billing_cats` varchar(1024) NOT NULL ;

 */
/**
 * 
 * Fucntion will generate options for on selection type using a sql query
 * @param string $_sql query to fetch options data
 * @param string $_ElementValue selected element value
 * @return string HTMLString
 * @author Kamlesh Rani(GITHSR00026)
 */
function queryToHTMLOptions($_sql, $_ElementValue = "") {
	global $conn, $connection;
	mysql_select_db ( $connection ['dbname'], mysql_connect ( $connection ['servername'], $connection ['username'], $connection ['password'] ) );
	$sql = $_sql;
	$res = mysql_query ( $sql ) or die ( $sql . mysql_error () );
	$i = 0;
	$returnOptionsHtml = "<option value=''>Select </option>";
	//generating option html string using db values
	while ( @$line = @mysql_fetch_array ( @$res ) ) {
		$extString = "";
		if ($_ElementValue == $line [0]) {
			$extString = "selected";
		}
		$returnOptionsHtml .= "<option value='" . $line [0] . "' $extString>" . $line [1] . "</option>\n";
	}
	return $returnOptionsHtml;

}
/**
 * 
 * Fucntion will generate options for on selection type using a sql query
 * @param string $_sql query to fetch options data
 * @param array $_ElementValues selected element values
 * @return string HTMLString
 * @author Kamlesh Rani(GITHSR00026)
 */
function queryToHTMLOptionsMultiple($_sql, $_ElementValues) {
	global $conn,$connection;
	mysql_select_db ( $connection ['dbname'], mysql_connect ( $connection ['servername'], $connection ['username'], $connection ['password'] ) );
	$sql = $_sql;
	$_ElementValues = explode ( ",", $_ElementValues );
	$res = mysql_query ( $sql ) or die ( $sql . mysql_error () );
	$i = 0;
	$returnOptionsHtml = "<option value=''>Select </option>";
	while ( @$line = @mysql_fetch_array ( @$res ) ) {
		$extString = "";
		// check if value is selected or not
		if (@in_array ( @$line [0], @$_ElementValues )) {
			$extString = "selected";
		}
		$returnOptionsHtml .= "<option value='" . $line [0] . "' $extString>" . $line [1] . "</option>\n";
	}
	return $returnOptionsHtml;

}

/*  $orders = array();
   $selectMarchent = "SHOW COLUMNS FROM  clues_billing_fee_config";
   $selectMarchentResult = mysql_query($selectMarchent) or die(mysql_error());
   while ($selectMarchentRow = mysql_fetch_assoc($selectMarchentResult)) {
 $orders[]  = $selectMarchentRow;  
   }
   foreach( $orders as $mercahntKey => $merchantOrderArray) {
   	echo '<input type="text" name="'.$merchantOrderArray['Field'].'" />'."\n";			
   }/**/
/**
 * Code for insert and update operation in db using post data
 */
if (isset ( $_POST ['insert_fee_configs'] )) {
	global $DBError, $DBErrorMessage, $DBError1;
	$DBError = false;
	$DBError1 = false;
	$error = false;
	$fulFillment_id = @$_POST ['fulfillment_ids'];
	$region_codes_id = @$_POST ['region_code'];
	$statusApp = @$_POST ['status_applicable'];
	$billing_cats = @$_POST['billing_cats'];
	/**
	 * unsetting elements which can't be updatedin db as it is.
	 */
	
	/**
	 * setting up the values to be updated/inserted in db
	 */
	$details = $_POST;
	$details ['fulfillment_ids'] = @implode ( ",", $fulFillment_id );
	$details ['region_code'] = @implode ( ",", $region_codes_id );
	$details ['status_applicable'] = @implode ( ",", $statusApp );
	$details ['billing_cats']= @implode ( ",", $billing_cats );
	$details ['billing_cats']= @implode ( ",", @$_POST['billing_cats'] );
	
	foreach ( $details as $k => $v ) {
		if (empty ( $v )) {
			$_POST ['Err_' . $k] = "Please Enter Value for " . $k;
			$error = true;
		}
	}
	if (strtotime ( $_POST ['from_date'] ) >= strtotime ( $_POST ['to_date'] )) {
		$_POST ['Err_to_date'] = "Please Enter a greater date";
		$error = true;
	}
	if (! $error) {
	unset ( $_POST ['fulfillment_id'] );
	unset ( $_POST ['region_id'] );
	unset ( $_POST ['status_applicable'] );
	unset ( $_POST ['insert_fee_configs'] );
	unset ( $_POST ['billing_cats'] );
		$tableFields = getColumns ( $tableName );
		foreach ( $tableFields as $k => $v ) {
			@$tableFieldsValue [$v] = $details [$v];
			if (array_key_exists ( $v, $details )) {
				$updatevalues [] = " $v = '" . $details [$v] . "'";
			}
		}
		// Check if edit mode is active or insert statement is ready
		if (isset ( $_GET ['edit_id'] ) && $_GET ['mode'] === 'edit' && ! empty ( $_GET ['edit_id'] ) && is_numeric ( $_GET ['edit_id'] )) {
			
			$update = "UPDATE $tableName SET ";
			$update .= implode ( ",", $updatevalues );
			$update .= " WHERE fee_config_id  =" . $_GET ['edit_id'];
			mysql_query ( $update ) or logDBError ( mysql_error () );
			header ( "LOCATION:fee_config.php?status=s" );
			unset ( $_POST );
		} else {
			if ($DBError1) {
				//   			 $DBErrorMessage;	
			} else {
				$insertQuery = "Insert Into $tableName (" . implode ( ',', array_keys ( $tableFieldsValue ) ) . ") values('" . implode ( "','", $tableFieldsValue ) . "')";
				$result = mysql_query ( $insertQuery ) or logDBError ( mysql_error (), $tableFieldsValue ['name'] );
				
				if ($DBError) {
					$_POST ['Err_name'] = "Duplicate Entry For Name";
				} else {
					$code = mysql_insert_id ();
					$newCode = "FEE" . $code;
					$updateInsert = "UPDATE $tableName SET code = '" . $newCode . "' WHERE fee_config_id = " . $code;
					mysql_query ( $updateInsert );
					$DBErrorMessage = $details ['name'] . " Fee Successfully Created";
					unset ( $_POST );
				}
			}
		}
	}
} elseif (is_numeric ( @$_GET ['edit_id'] ) && ! empty ( $_GET ['edit_id'] )) {
	$display = "block";
	$getDetailsQuery = "SELECT * from $tableName WHERE fee_config_id = " . $_GET ['edit_id'];
	$detailsResult = mysql_query ( $getDetailsQuery );
	$resultRow = mysql_fetch_assoc ( $detailsResult );
	foreach ( $resultRow as $key => $value ) {
		$_POST [$key] = $value;
	}
	$full = $_POST ['fulfillment_ids'];
	$region = $_POST ['region_code'];
	$status = $_POST ['status_applicable'];
	unset ( $_POST ['fulfillment_ids'] );
	unset ( $_POST ['region_ids'] );
	unset ( $_POST ['status_applicable'] );
	$_POST ['fulfillment_id'] = explode ( ",", $full );
	$_POST ['region_id'] = explode ( ",", $region );
	$_POST ['status_applicable'] = explode ( ",", $status );
}
if(@$error){
	@$display = "block";
	
}
$orderStatus = "SELECT status,description FROM  cscart_status_descriptions WHERE type = 'O' order by description";
$orderResult = mysql_query ( $orderStatus );

$fulFillment_lookup = "SELECT fulfillment_id,description FROM  clues_fulfillment_lookup";
$fulFillment_lookup_Result = mysql_query ( $fulFillment_lookup );

$region_lookup = "SELECT region_id,region_name FROM  clues_region_lookup";
$region_lookup_Result = mysql_query ( $region_lookup ) or die ( mysql_error () );

$fee_configs = "SELECT * FROM  clues_billing_fee_config";
$fee_configsResult = mysql_query ( $fee_configs );

/**
 * function to get list of columns of given table 
 * @param string $tableName
 * @return ArrayObject $columnList
 */
function getColumns($tableName) {
	$columnList = array ();
	$columnTable = "SHOW COLUMNS FROM $tableName";
	$columnResult = mysql_query ( $columnTable ) or logDBError ( mysql_error () );
	while ( $columnRow = @mysql_fetch_row ( $columnResult ) ) {
		$columnList [] = $columnRow [0];
	}
	return $columnList;
}
/**
 * Fucntion to log errors
 */
function logDBError($error = "", $name = "") {
	global $DBError, $DBErrorMessage, $DBError1;
	if ($error == "Duplicate entry '$name' for key 'name'") {
		$DBError = true;
		$DBErrorMessage = "";
	} else {
		$DBError1 = true;
		$DBErrorMessage = $error;
	}
	return $error;
}
mysql_close ();

?>
<!DOCTYPE HTML5>
<html>
<head>
<link rel="stylesheet" href="css/demoTable.css" media="screen" />
<link rel="stylesheet" href="css/jquery-ui-css.css" media="screen" />
<link rel="stylesheet" href="css/style.css" media="screen" />
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery-ui.js"></script>
<script type="text/javascript" src="js/jquery-dataTables.js"></script>
<script type="text/javascript" src="js/common.js"></script>
<script type="text/javascript" src="js/dashboard.js"></script>
<script type="text/javascript" src="js/simpleModel.js"></script>
<script type="text/javascript" src="js/modenizer.js"></script>
<style type="text/css">
p {
	color: red;
}

#dash-summary_previous {
	float: left;
	width: 60px;
	text-align: left;
}

#dash-summary_next {
	text-align: right;
	width: 60px;
	float: right;
}
</style>
</head>
<body>
<h1>Billing Fee Configurations</h1>
<br />
<h2><?php
echo @$DBErrorMessage;
if (@$_GET ['status'] === 's')
	echo "Successfully Updated";
?></h2>
<div style="text-align: right; padding-right: 50px;">
<button
	onclick="document.getElementById('register_block').style.display = 'block';this.style.display = 'none';">Add
New Fee Config</button>
<button
	onclick="window.location='fee_group.php'">Fee Group</button>
</div>
<div id="register_block" style="display:<?php
echo $display;
?>;border:2px solid #999">
<form method="post">
<table>
	<tr>
		<td><label>Name</label></td>
		<td><input type="text" name="name"
			value="<?php
			echo @$_POST ['name'];
			?>"
			title="Name for fee Config Containing" /><br />
		<p><?php
		echo @$_POST ['Err_name'];
		?></p>
		</td>
	</tr>
	<tr>
		<td><label>Type</label></td>
		<td><select name="type">
			<option value="F">Fixed</option>
			<option value="P">Percentage</option>
		</select></td>
	</tr>
	<tr>
		<td><label>Amount</label></td>
		<td><input type="text" name="amount"
			value="<?php
			echo @$_POST ['amount'];
			?>" /><br />
		<span style="color: blue;">Enter Amount in % if type is Percentage</span>
		<p><?php
		echo @$_POST ['Err_amount'];
		?></p>
		</td>
	</tr>
	<tr>
		<td><label>Status Applicatble</label></td>
		<td>
		<table>
			<tr>
								<?php
								$count = 0;
								while ( $orderRow = mysql_fetch_assoc ( $orderResult ) ) {
									$count ++;
									if ($count % 5 === 1)
										echo "</tr><tr>";
									?>
										<td><input type="checkbox" name="status_applicable[]"
					value="<?php
									echo $orderRow ['status'];
									?>"
					<?php
									if (@in_array ( @$orderRow ['status'], @$_POST ['status_applicable'] ))
										echo ' CHECKED ';
									?> /><?php
									echo $orderRow ['description'];
									?></td>
								<?php
								}
								?>
							</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td><label>Tax Rate</label></td>
		<td><input type="text" name="tax_rate"
			value="<?php
			echo @$_POST ['tax_rate'];
			?>" /><br />
		<p><?php
		echo @$_POST ['Err_tax_rate'];
		?></p>
		</td>
	</tr>
	<tr>
		<td><label>Fee Group</label></td>
		<td><select name="fee_group_id">
						<?php
						echo queryToHTMLOptions ( "select fee_group_id, name from clues_billing_fee_group", @$_POST ['fee_group_id'] );
						?>
					</select></td>
	</tr>
	<tr>
		<td><label>Unit</label></td>
		<td><select name="unit">
			<option value="I">Item</option>
			<option value="O">Order</option>
			<option value="M">Merchant</option>
		</select></td>
	</tr>
	<tr>
		<td><label>From Date</label></td>
		<td><input type="text" name="from_date" class="filter date-picker"
			id="from_date" value="<?php
			echo @$_POST ['from_date'];
			?>" /><br />
		<p><?php
		echo @$_POST ['Err_from_date'];
		?></p>
		</td>
	</tr>
	<tr>
		<td><label>To Date</label></td>
		<td><input type="text" name="to_date" class="filter date-picker"
			id="to_date" value="<?php
			echo @$_POST ['to_date'];
			?>" /><br />
		<p><?php
		echo @$_POST ['Err_to_date'];
		?></p>
		</td>
	</tr>
	<tr>
		<td><label>Fee status</label></td>
		<td><select name="fee_status">
			<option value="A"
				<?php
				echo @$_POST ['fee_status'] === 'A' ? 'SELECTED' : "";
				?>>Active</option>
			<option value="I"
				<?php
				echo @$_POST ['fee_status'] === 'I' ? 'SELECTED' : "";
				?>>InActive</option>
		</select> <!--<input type="text" name="fee_status" value="<?php
		echo @$_POST ['fee_status'];
		?>" pattern="[A-Z]{1}" title="Single Capital Char for status"/><br/> -->
		<p><?php
		echo @$_POST ['Err_fee_status'];
		?></p>
		</td>
	</tr>
<tr>
		<td><label>Billing Category</label></td>
		<td><select name="billing_cats[]"  size=5 multiple>
						<?php
						echo queryToHTMLOptionsMultiple ( "select id, category from clues_billing_categories", @$_POST ['billing_cats'] );
						?>
					</select>
					<?php echo "<p>". @$_POST ['Err_billing_cats']."<p>";?>
					</td>
	</tr>
	
	<tr>
		<td valign="top"><label>Region </label></td>
		<td>
			<?php
			echo "<select name=\"region_code[]\" size=5 multiple>";
			echo queryToHTMLOptionsMultiple ( $region_lookup, @$_POST ['region_code'] );
			echo "</select>";
			
		echo "<p>". @$_POST ['Err_region_code']."<p>";
		/*/
			<table><tr>
			
						<?php
						 
						$i=0;
						while($region_lookup_ResultRow = mysql_fetch_assoc($region_lookup_Result)) {
						 echo "<td>";
							?>
								<input type="checkbox" name="region_code[]" <?php if(in_array($region_lookup_ResultRow['region_id'],$_POST['region_id'])) echo ' CHECKED ';?> value="<?php echo $region_lookup_ResultRow['region_id'];?>"/><?php echo $region_lookup_ResultRow['region_name'];?>&nbsp;&nbsp;&nbsp;
						<?php 
						if($i<5){ echo "</td>";
						++$i;
						}else{
							echo "</td></tr><tr>";
							$i=0;
						}
						}
						?>
<!-- 					<input type="text" name="region_ids" value="<?php echo @$_POST['region_code'];?>"/><br/> -->
				<p><?php echo @$_POST['Err_region_ids'];?></p></td>
				</tr></table><?php /**/?></td>
	</tr>
	<tr>
		<td><label>FulFillment Type</label></td>
		<td>
						<?php
						while ( @$fulFillment_lookup_ResultRow = @mysql_fetch_assoc ( @$fulFillment_lookup_Result ) ) {
							?>
								<input type="checkbox" name="fulfillment_ids[]"
			<?php
							if (@in_array ( @$fulFillment_lookup_ResultRow ['fulfillment_id'], @$_POST ['fulfillment_id'] ))
								echo ' CHECKED ';
							?>
			value="<?php
							echo $fulFillment_lookup_ResultRow ['fulfillment_id'];
							?>" /><?php
							echo $fulFillment_lookup_ResultRow ['description'];
							?>&nbsp;&nbsp;&nbsp;
						<?php
						}
						?>
<!-- 					<input type="text" name="fulfillment_ids" value="<?php
echo @$_POST ['fulfillment_ids'];
?>"/><br/> -->
		<p><?php
		echo @$_POST ['Err_fulfillment_ids'];
		?></p>
		</td>
	</tr>
	<tr>
		<td><label>&nbsp;&nbsp;</label></td>
		<td><input type="submit" name="insert_fee_configs"
			value="Save Fee Configs" /></td>
	</tr>
</table>
</form>
</div>
<table style="border: 2px solid black;" cellspacing="0"
	class='dashboard' id='dash-summary'>
	<?php
	echo "<thead><tr>";
	/*		$numFields = mysql_num_fields($fee_configsResult);
		for ($i = 0; $i < $numFields; $i++) {
	    	$meta = mysql_fetch_field($fee_configsResult, $i);
	    	if('fee_config_id' !== $meta->name || 'created_by' !== $meta->name || 'updated_by' !== $meta->name || 'created_date' !== $meta->name || 'updated_date' !== $meta->name) {
					echo "<td>".strtoupper($meta->name)."</td>";			
	    	}
		}/**/
	echo "<td>Name</td>";
	echo "<td>Code</td>";
	echo "<td>Type</td>";
	echo "<td>Amount</td>";
	echo "<td>Order Status</td>";
	echo "<td>Tax Rate</td>";
	echo "<td>Unit</td>";
	echo "<td>From Date</td>";
	echo "<td>To Date</td>";
	echo "<td>Fee status</td>";
	echo "<td>Fulfillements</td>";
	echo "<td>Fee Group</td>";
	echo "<td>Region </td>";
	echo "<td>Billing Category</td>";
	echo "<td>Created by</td>";
	echo "<td>Updated by</td>";
	echo "<td>Created Date</td>";
	echo "<td>Updated Date</td>";
	
	
	echo "</tr></thead>";
	
	while ( $fee_configsResultRow = mysql_fetch_assoc ( $fee_configsResult ) ) {
		$crby = $fee_configsResultRow ['created_by'];
		$upby = $fee_configsResultRow ['updated_by'];
		$crd = $fee_configsResultRow ['created_date'];
		$upd = $fee_configsResultRow ['updated_date'];
		$fee_group = mysql_fetch_array ( mysql_query ( "select name from clues_billing_fee_group where fee_group_id = " . $fee_configsResultRow ['fee_group_id'] ) );
		$fee_configsResultRow ['fee_group_id'] = $fee_group [0];
		$fee_configsResultRow ['name'] = '<a href="?mode=edit&edit_id=' . $fee_configsResultRow ['fee_config_id'] . '">' . $fee_configsResultRow ['name'] . "</a>";
		unset ( $fee_configsResultRow ['fee_config_id'] );
		unset ( $fee_configsResultRow ['created_by'] );
		unset ( $fee_configsResultRow ['created_date'] );
		unset ( $fee_configsResultRow ['updated_by'] );
		unset ( $fee_configsResultRow ['updated_date'] );
		// $fee_configsResultRow['region_code'];
		$sql_fetch_region = $region_lookup . " where region_id in (" . $fee_configsResultRow ['region_code'] . ")";
		$region_result_set = @mysql_query ( $sql_fetch_region ) or die ( mysql_error () );
		$region_fname = "";
		while ( $region_fetch = @mysql_fetch_array ( $region_result_set ) ) {
			$region_fname .= $region_fetch ["region_name"] . ", ";
		}
		unset ( $fee_configsResultRow ['region_code'] ); // = $region_fname;
		$billing_cats = "";
		if($fee_configsResultRow ['billing_cats'] !=""){
			$sql_fetch_billing_cats = "select id,category from clues_billing_categories " . " where id in (" . $fee_configsResultRow ['billing_cats'] . ")";
			$billing_cats_result_set = @mysql_query ( $sql_fetch_billing_cats ) or die ( mysql_error () );
			
			while ( $billing_fetch = @mysql_fetch_array ( $billing_cats_result_set ) ) {
				$billing_cats .= $billing_fetch ["category"] . ", ";
			}
		}
		unset ( $fee_configsResultRow ['billing_cats'] ); // = $region_fname;
		
		if ($fee_configsResultRow ['unit'] === 'O')
			$fee_configsResultRow ['unit'] = "Order";
		else if ($fee_configsResultRow ['unit'] === 'I')
			$fee_configsResultRow ['unit'] = "Item";
		else if ($fee_configsResultRow ['unit'] === 'M')
			$fee_configsResultRow ['unit'] = "Merchant";
		
		if ($fee_configsResultRow ['type'] === 'F')
			$fee_configsResultRow ['type'] = "Fixed";
		else if ($fee_configsResultRow ['type'] === 'P')
			$fee_configsResultRow ['type'] = "Percentage";
		
		if ($fee_configsResultRow ['fee_status'] === 'A')
			$fee_configsResultRow ['fee_status'] = "Active";
		else if ($fee_configsResultRow ['fee_status'] === 'I')
			$fee_configsResultRow ['fee_status'] = "InActive";
		echo "<tr>";
		echo "<td>" . implode ( "</td><td>", $fee_configsResultRow ) . "</td>";
		echo "<td><div style=\"width:100%;max-width:300px;max-height:100px;overflow:auto;\">" . $region_fname . "</td>";
		echo "<td><div style=\"width:100%;max-width:300px;max-height:100px;overflow:auto;\">" . $billing_cats . "</td>";
		echo "<td>" . $crby . "</td>";
		echo "<td>" . $upby . "</td>";
		echo "<td>" . $crd . "</td>";
		echo "<td>" . $upd . "</td>";
		
		echo "</tr>";
	}
	?>
	</table>
<script type="text/javascript" language="javascript">
		$(document).ready(function(){
	    	$('#dash-summary').dataTable();
		});
	</script>
</body>
</html>
