<?php
$display = "none";
$messageToDisplay = "";
//Should not connect to DB from here, use cscart spefic functions
mysql_connect("localhost","root","root");
mysql_select_db('scdb'); 

$submitvalue="SUBMIT";
if(isset($_POST['insert_fee_group']) && isset($_GET['edit'])){
	$query = "update clues_billing_fee_group set 
	Name = '".$_POST['name']."',From_date = '".$_POST['from_date']."',To_date = '".$_POST['to_date']."' 
	where fee_group_id = ".$_GET['edit'];
	$result=mysql_query($query);
	unset($_GET['edit']);
	unset($_POST);
	$messageToDisplay = "Fee Group Updated Successfully ";
}else if(isset($_GET['edit'])){
	$display = "block";
	$query = "select * from clues_billing_fee_group where fee_group_id = ".$_GET['edit'];
	$result=mysql_query($query);
	if($line = mysql_fetch_assoc($result)){
		foreach ($line as $key => $value){
			$_POST[$key] = $value;
		}
	}
}else if(isset($_POST['insert_fee_group'])){
	
	$query=mysql_query("insert into clues_billing_fee_group(Name,From_date,To_date)values('$_POST[name]','$_POST[from_date]','$_POST[to_date]')");
	$result=mysql_query($query);
}

mysql_connect("localhost","root","root");
mysql_select_db('scdb');
$fee_group="select * from clues_billing_fee_group";
$fee_groupresult=mysql_query($fee_group);



?>
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
<h1>Billing Fee Group</h1>
<h3><?php echo $messageToDisplay;?></h3>
<br /><div style="text-align: right; padding-right: 50px; " >

<button
	onclick="window.location='fee_config.php'">Fee Config</button>
<?php if(!isset($_GET['edit'])){?>
<button
	onclick="document.getElementById('register_block').style.display = 'block';this.style.display = 'none';">Add
New Fee Group</button>
<?php }?>
</div>

<div id="register_block" style="display:<?php
echo $display;
?>;border:2px solid #999">
<form  method="post" action="">
<table>
	<tr>
		<td><label>Name</label></td>
		<td><input type="text" name="name"
			value="<?php
			echo @$_POST ['name'];
			?>"
			title="Name for fee group" /><br />
		<p><?php
		echo @$_POST ['Err_name'];
		?></p>
		</td>
	</tr>
	<tr>
		<td><label>fromdate</label></td>
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
		<td><label>todate</label></td>
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
		<td><label>&nbsp;&nbsp;</label></td>
		<td><input type="submit" name="insert_fee_group"
			value="Save Fee group" /></td>
	</tr>
	

</table>

</form>
</div>
<table style="border: 2px solid black;" cellspacing="0"
	class='dashboard' id='dash-summary'>
	<?php
	echo "<thead><tr>";
	
	echo "<td>Name</td>";
	echo "<td>fromdate</td>";
	echo "<td>todate</td>";
	echo "</tr></thead>";
	while ($row = mysql_fetch_array($fee_groupresult)) { 
		echo "<tr>";
		echo "<td><a href=\"?edit=".$row['fee_group_id']."\">" . $row['name'] ."</a></td>";
		echo "<td>" . $row['from_date'] . "</td>";
		echo "<td>" . $row['to_date']. "</td>";
		
		
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
