<?php
   	include_once 'functions.php';
	$connection = connectDb();
	/**/
   if(isset($_POST['insert_fee_configs'])) {
		$error = true;	
	   	if(strtotime($_POST['from_date']) >= strtotime($_POST['to_date'])) {
				$_POST['Err_to_date'] = "Please Enter a greater date";
				$error = false;
			}
			if(empty($_POST['order_ids'])) {
				$_POST['Err_order_ids'] = "Please Enter order ids excluded once";
				$error = false;
			}
			if(empty($_POST['order_ids_a'])) {
				$_POST['Err_order_ids_a'] = "Please Enter order ids to be exclude always";
				$error = false;
			}
			
			if($error) {
				
				$merchantsArray = $_POST['selected_comp'];
				$exOrdersAlArray = explode(",", $_POST['order_ids_a']);
				$exOrdersONArray = explode(",", $_POST['order_ids']);
				
				
				$feeConfigCode = $_POST['fee_need_applay'];
				$merchantsIdsNeedToProccess = implode(",", $merchantsArray);
				$merchantProccess = $_POST['ex_in_m'];
				$startDate = $_POST['from_date'];
				$endDate = $_POST['to_date'];
				$runStatus = $_POST['run_status'];
				$exOrdersAl = implode(",", $exOrdersAlArray);
				$exOrdersOn = implode(",", $exOrdersONArray);
				$runType = $_POST['run_status'];
				
				$procedureQuery = "CALL clues_billing_fee_sp('".$feeConfigCode."','".$merchantsIdsNeedToProccess."','".$merchantProccess."','".$startDate."','".$endDate."','".$exOrdersAl."','".$exOrdersOn."','".$runType."')";
				mysql_query($procedureQuery) or die(mysql_error() . "abc");
				$DBErrorMessage = "Successfully Updated";
				
				if($merchantProccess === 'e'){
					$mar = " company_id not in (".$merchantsIdsNeedToProccess.") and ";
				}else {
					$mar = " company_id in (".$merchantsIdsNeedToProccess.") and ";
				}
				$queryOrderIds = "";
				$queryOrderIds = "'".implode("','",$exOrdersAlArray)."','".implode("','",$exOrdersONArray)."'";
				$selectBillingFee = "SELECT * FROM  clues_billing_fee_details WHERE  $mar order_id not in ($queryOrderIds) and fee_code = '".$_POST['fee_need_applay']."' and billing_cycle  between ".$_POST['from_date']." and ".$_POST['to_date'];
				$billResult = mysql_query($selectBillingFee);
				$csvBilling = array();
				$display = "";
				$display = "<table class='dashboard' id='dash-summary'><thead><tr>";	
				$numFields = mysql_num_fields($billResult);
				for ($i = 0; $i < $numFields; $i++) {
		    		$meta = mysql_fetch_field($billResult, $i);
					$colArray[] = $meta->name;
				}
				
				$display .= "<th>".implode('</th><th>', $colArray)."</th>";
				$display .= "</thead></tr>";
				
				while ($billResultRow = mysql_fetch_assoc($billResult)) {
					$display .= '<tr><td>'.implode('</td><td>', $billResultRow).'</td></tr>';
				}
				$display .= "</table>";
			
		 		/*$display = "";
				$numFields = mysql_num_fields($billResult);
				for ($i = 0; $i < $numFields; $i++) {
		    		$meta = mysql_fetch_field($billResult, $i);
					$colArray[] = $meta->name;
				}
				
				$display .= '"';
				$display .= implode('","', $colArray);
				$display .= '"';
				$display .= "\n";
				
				while ($billResultRow = mysql_fetch_assoc($billResult)) {
					$display .= '"';
					$display .= implode('","', $billResultRow);
					$display .= '"';
					$display .= "\n";	
				}
				header("cache-control: private");
		 		header('content-Disposition:attachment;filename= '.$_POST['from_date']."-".$_POST['to_date']."-billing.csv");
		 		header('content-type: text/csv;charset:UTF-8');
		 		header('content-length: '.strlen($display));
		 		header('content-Transfer-Encoding:binary');
		 		ob_clean();
		 		flush();
		 		echo $display;
		 		closeDb($connection);
		 		die();/**/
				
			}/**/
   }
   
   
   $selectFee_configs = "SELECT name,code FROM clues_billing_fee_config WHERE fee_status = 'A'";
   $feeCongigName = getresult($selectFee_configs);
   
   $selectMerchant = "SELECT company_id,company FROM cscart_companies LIMIT 0,10";
   $resultMercahnt = mysql_query($selectMerchant);
   ?>
<!DOCTYPE HTML5>
<html>
	<head>
		<link rel="stylesheet" href="css/demoTable.css" media="screen"/>
		<link rel="stylesheet" href="css/jquery-ui-css.css" media="screen"/>
		<link rel="stylesheet" href="css/style.css" media="screen"/>
		<script type="text/javascript" src="js/jquery.js"></script>
		<script type="text/javascript" src="js/jquery-ui.js"></script>
		<script type="text/javascript" src="js/jquery-dataTables.js"></script>
		<script type="text/javascript" src="js/common.js"></script>
		<script type="text/javascript" src="js/dashboard.js"></script>
		<script type="text/javascript" src="js/simpleModel.js"></script>
		<script type="text/javascript" src="js/modenizer.js"></script>
		<style type="text/css">
			p {
					color:red;
				}
			</style>
			<script type="text/javascript" language="javascript">
				function getMerchants(fee_config_fulls) {
					var fullFilmentIds = fee_config_fulls.value;
					$.ajax({
						type:'GET',			
						url: 'getMerchants.php?m_full_ids='+fullFilmentIds,
						success: function(data) {
						//	alert(data);
							document.getElementById('merchants').innerHTML = data;
						}
						});
				}
				function getselected(list) {
					var multiSelect = document.getElementById('company');
					var lenMultiSelect = multiSelect.length;
					var i = 0;
					$optionDisplay = "";
					for(i=0;i<lenMultiSelect;i++) {
						if(multiSelect[i].selected) {undefined
							if(document.forms.sp_form.selected_comp[i] == undefined)
								$optionDisplay += "<option value=\""+multiSelect[i].value+"\" SELECTED>"+multiSelect[i].innerHTML+"</option>";
						}
					}
					document.getElementById('selected_comp').innerHTML = document.getElementById('selected_comp').innerHTML+$optionDisplay;
					return false;
				}
				function removeselected(list) {
					var multiSelect = document.getElementById('selected_comp');
					var lenMultiSelect = multiSelect.length;
					var i = 0;
					$optionDisplay = "";
					for(i=0;i<lenMultiSelect;i++) {
						if(!multiSelect[i].selected) {
							$optionDisplay += "<option value=\""+multiSelect[i].value+"\">"+multiSelect[i].innerHTML+"</option>";
						}
					}
					document.getElementById('selected_comp').innerHTML = $optionDisplay;
					return false;
				}
			</script>
	</head>
<body>
	<form method="post" name="sp_form">
		<table>
			<caption><?php echo @$DBErrorMessage;?></caption>
			<tr>
				<td><label>Select Type of Fee</label></td>		
				<td>
					<select name="fee_need_applay" onchange="getMerchants(this);">
							<option value="">Select fee Type to Applay</option>
						<?php foreach($feeCongigName as $keyFee => $valueFee) {?>
								<option value="<?php echo $valueFee['code'];?>"><?php echo $valueFee['name'];?></option>
						<?php }?>
					</select>
					<br/>
				<p><?php echo @$_POST['Err_order_ids'];?></p></td>
			</tr>
			<tr id="merchants">
			</tr>
			<tr>
				<td><label>Merchant Run Type</label></td>		
				<td>
					<input type="radio" name="ex_in_m"  value="e" <?php echo @($_POST['ex_in_m'] === 'e') ? 'CHECKED' : 'CHECKED';?>/>Exclude<br/>
					<input type="radio" name="ex_in_m"  value="i" <?php echo @($_POST['ex_in_m'] === 'i') ? 'CHECKED' : '';?>/>Include<br/>
					<p><?php echo @$_POST['Err_to_date'];?></p>
				</td>
			</tr>
			<tr>
				<td><label>Order Ids excluded Once</label></td>		
				<td><textarea name="order_ids"  id ="order_ids_ex"/></textarea><br/>
				<p><?php echo @$_POST['Err_order_ids'];?></p></td>
			</tr>
			<tr>
				<td><label>Order Ids excluded Always</label></td>		
				<td><textarea name="order_ids_a"  id ="order_ids_ex_a"/></textarea><br/>
				<p><?php echo @$_POST['Err_order_ids_a'];?></p></td>
			</tr>

			<tr>
				<td><label>From Date</label></td>		
				<td><input type="text" name="from_date" class="filter" value="2012-12-15" readonly/><br/>
				<p><?php echo @$_POST['Err_from_date'];?></p></td>
			</tr>
			<tr>
				<td><label>To Date</label></td>		
				<td><input type="text" name="to_date" class="filter date-picker" id ="to_date" value="<?php echo @$_POST['to_date'];?>"/><br/>
				<p><?php echo @$_POST['Err_to_date'];?></p></td>
			</tr>
			<tr>
				<td><label>Run Status</label></td>		
				<td>
					<input type="radio" name="run_status"  value="t" <?php echo @($_POST['run_status'] === 't') ? 'CHECKED' : 'CHECKED';?>/>Test Run<br/>
					<input type="radio" name="run_status"  value="f" <?php echo @($_POST['run_status'] === 'f') ? 'CHECKED' : '';?>/>Final Run<br/>
					<p><?php echo @$_POST['Err_to_date'];?></p>
				</td>
			</tr>
			<tr>
				<td><label>&nbsp;&nbsp;</label></td>		
				<td><input type="submit" name="insert_fee_configs" value="Update Billing" /></td>
			</tr>
		</table>
	</form>
	<?php echo  @$display;?>
	<script type="text/javascript" langauage="javascript">
		createDataTable('#dash-summary');
	</script> 
</body>
</html>
<?php closeDb($connection);?>
