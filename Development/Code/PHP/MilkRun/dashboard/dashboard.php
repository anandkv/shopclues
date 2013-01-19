<div id='filter'  class='criteria widget'>
	<span><select name="region" class="filter">
		<option value=""> --Select Region-- </option>
		<option value="2">Delhi & NCR</option>
	</select></span>
	<span><input type="text" name="fromDate" class="filter date-picker" placeholder="From Date"/></span>
	<span><input type="text" name="toDate" class="filter date-picker" placeholder="To Date"/></span>
	<span><input type="text" name="manifestId" class='filter' placeholder="Manifest ID"/></span>
	<span><input type="text" name="pbName"  class='filter' placeholder="Pickup Boy Name"/></span>
	<span><input type="text" name="mName" class='filter' placeholder="Merchant Name"/></span>
	<!--
		<span><input type="text" name="mAddress" class='filter' placeholder="Merchant Address"/></span>
		<span><input type="text" name="mCity" class='filter' placeholder="Merchant City"/></span>
	-->
	<span>
		<div style="padding-left:20px;" class="cm-button-main">
          <button id='search-button'>Search Manifest</button>
		</div>
	</span>
</div>
<!--<div>&nbsp;</div> -->
<div id='grid-1' class='hidden '>
	<h3>MilkRun Summary</h3>
	<div class='table-wrapper'>
		<table class='dashboard' id='dash-summary'>
			<thead>
				<tr>
					<th>Manifest Date</th>
					<th>Pickup Boy</th>
					<th>Manifest ID</th>
					<th class='no-sort'>Expected Item Quantity</th>
					<th class='no-sort'>Received Quantity</th>
					<th class='no-sort'>Login Time</th>
					<th class='no-sort'>Logout Time</th>
				</tr>
			</thead>
			<tbody id='milkrun-grid'>
			</tbody>
		</table>
	</div>
</div>
<!--<div>&nbsp;</div> -->
<div id='grid-2' class='hidden'>
	<h3 id='merchant-heading'>MilkRun Summary for <span id='pickupboy'></span></h3>
	<div class='table-wrapper'>
	<div class='criteria widget'>
		<span><input type="text" name="orderId" class="filter" placeholder="Order ID"/></span>
		<span><input type="text" name="productId" class="filter hidden" placeholder="Product ID"/></span>
		<span>
			<div style="padding-left:20px;" class="cm-button-main">
			  <button id='filter-merchant'>Search</buttion>
			</div>
		</span>
	</div>

	<table class='dashboard hidden' id='milkrun-orders'>
		<thead>
			<tr>
				<th class='no-sort'>Merchant Name/Address</th>
				<th class='no-sort'>Expected</th>
				<th class='no-sort'>Received</th>
				<th class='no-sort'>Status</th>	
				<th class='no-sort'>Status Time</th>	
			</tr>
		</thead>
		<tbody id='order-grid'>
		</tbody>
	</table>
</div></div>
<!--<div>&nbsp;</div> -->
<div id='grid-3' class='hidden'>
	<h3>MilkRun Details for <span id='merchant-name'></span></h3>
	<div class='table-wrapper'>
	<table class='dashboard hidden' id='milkrun-detail'>
		<thead>
			<tr>
				<th>Merchant</th>
				<th>Order ID<br>Item ID</th>
				<th>Order Status</th>
				<th>Product</th>
				<th class='no-sort'>Expected</th>
				<th class='no-sort'>Received</th>
				<th class='no-sort'>Reason</th>
			</tr>
		</thead>
		<tbody id='milkrun-grid2'>
		</tbody>
	</table>
</div>
</div>

	
