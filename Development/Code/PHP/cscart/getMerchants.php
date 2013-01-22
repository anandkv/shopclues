<td><label>Merchants</label></td>
<td>
<?php 
	include_once 'functions.php';
	$connection = connectDb();
		$getFullFill = "SELECT fulfillment_ids FROM clues_billing_fee_config WHERE code = '".$_GET['m_full_ids']."'";
	   	$fullID = getresult($getFullFill);
		$selectMerchant = "SELECT company_id,company FROM cscart_companies WHERE fulfillment_id in (".$fullID[0]['fulfillment_ids'].")";
   		$resultMercahnt = getresult($selectMerchant);
   		$display = "";
   		$display .= '<select name="company[]" id="company" size="10" multiple>';
   		foreach ($resultMercahnt as $mKey => $mValue) {
   			$display .= '<option value="'.$mValue['company_id'].'">'.$mValue['company'].'</option>';
   		}
   		$display .= '	</select>
   						<button onclick="getselected();return false;" onsubmit="return false;" style="position:relative;top:-100px;width:200px;">Move Selected</button>
   						<button onclick="removeselected();return false;" onsubmit="return false;" style="position:relative;top:-50px;left:-204px;width:200px;">Remove Selected</button>
   						<select name="selected_comp[]"  id="selected_comp"  size="10" multiple width="200" style="position:relative;left:-200px;">
   						</select>';
   		echo $display;
	closeDb($connection);
?>
</td>