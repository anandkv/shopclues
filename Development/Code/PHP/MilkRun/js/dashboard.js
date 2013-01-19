var searchResult;
var searchCriteria = new Object();
var mrTable;
var merchants = new Object();
var orderTable;
var nTr ;
var pickupboy_id;
	
$(document).ready(function () {
	
	function getResult(data,callback){
		if(searchResult!=null){
			searchResult.abort();
		}
		searchResult =  $.get("../api/json/pod.php",data,callback );
	}
	
	$('.date-picker').datepicker(	{ 
			defaultDate: new Date(), 
			dateFormat: 'yy-mm-dd' 
	});
	//.datepicker('setDate' , new Date());

	
	function createMerchantFilter(){
		var data= searchCriteria;
		$('#grid-2 .filter').each(function(){
			searchCriteria[$(this).attr('name')]=$(this).val();;
		});	
		data.action = 'listMerchants';
		getResult(data,listMerchants );
	}
	
	function createFilter(event){
		searchCriteria = new Object();
		$('#filter .filter').each(function(){
			searchCriteria[$(this).attr('name')]=$(this).val();;
		});	
		
		var data= searchCriteria;
		data.action = 'podSummary';

		getResult(data,updateSummary );
		var podTable = null
	}



	function updateSummary (data){
		var pod = $.parseJSON(data);
		if(mrTable!=null)
		mrTable.fnDestroy();
		
		var html="";
		for (x in pod){
			html+="<tr manifestId='"+pod[x].manifest_id+"' pid='"+pod[x].pickupboy_id+"' pickupboy = '"+pod[x].pickupboy+"' class='mri-tr'>";
			html+="<td>"+pod[x].date+"</td>";
			html+="<td>"+pod[x].pickupboy+"</td>"; 
			html+="<td>"+pod[x].manifest_id+"</td>";
			html+="<td class='numeric'>"+pod[x].expected_qty+"</td>";
			html+="<td class='numeric'>"+pod[x].received_qty+"</td>";
			html+="<td>"+pod[x].login_dt+"</td>";
			html+="<td>"+pod[x].logout_dt+"</td>";
			html+="</tr>";
		}
		$('#milkrun-grid').html(html);
		mrTable = createDataTable("#dash-summary");
		$('.mri-tr').die('click');
		$('.mri-tr').live('click', function(){

			nTr = $(this);
			var data= searchCriteria;
			data.action = 'listMerchants';
			searchCriteria.pid = data.pid = $(this).attr('pid');
			$('#pickupboy').html($(this).attr('pickupboy'));
			searchCriteria.manifestId = data.manifestId = $(this).attr('manifestId');
			$.get("../api/json/pod.php",data, listMerchants);

		});

		$('#grid-1').show();
		$( "#grid-1" ).accordion('destroy').accordion({
			collapsible: true
		});
		$("#milkrun-grid").click(function (event) {
			$(mrTable.fnSettings().aoData).each(function () {
				$(this.nTr).removeClass('row_selected');
			});
			$(event.target.parentNode).addClass('row_selected');
		});
	}

	function listMerchants(data){
		try{
		var pod = $.parseJSON(data);
		if(orderTable!=null){
			orderTable.fnDestroy();
		}
		var html="";
		for (x in pod){
			var merchant = pod[x]; 
			merchants[merchant.merchant_id] = merchant;
			html+="<tr merchant_id='"+pod[x].merchant_id+"'>";
			html+="<td class='left'><b>"+pod[x].company_name+"</b><br/>"+pod[x].company_add+"</td>";
			html+="<td class='numeric'>"+pod[x].expected_qty+"</td>";
			html+="<td class='numeric'>"+pod[x].received_qty+"</td>";
			html+="<td>"+pod[x].status+"</td>";
			html+="<td>"+pod[x].close_dt+"</td>";
			html+="</tr>";
		}
		var grid2 = $('#grid-2');

		
		grid2.find('#order-grid').html(html);
		orderTable = createDataTable("#milkrun-orders");

		$('#order-grid tr').die('click');
		
		$("#order-grid tr").live('click' , function (event) {
			$('#order-grid tr').each(function () {
				$(this).removeClass('row_selected');
            });
            $(event.target.parentNode).addClass('row_selected');
			var data= searchCriteria;
			data.action = 'listProducts';

			data.merchant_id = $(this).attr('merchant_id');
			data.pickupboy_id = $(this).attr('pickupboy_id');
			
			
			$('#merchant-name').html(merchants[data.merchant_id].company_name);
			$.get("../api/json/pod.php",data, listProducts);
        });

		grid2.show();
		$(grid2).accordion('destroy').accordion({
			collapsible: true
		});
		$( "#grid-1" ).accordion({
			active: false
		});
		
		}catch(err){
			alert("Errr while loading data\n"+data);
		}
		
	
	}

		function listProducts(data){
		var pod = $.parseJSON(data);
		if(podTable!=null)
		podTable.fnDestroy();
		var html="";
		for (x in pod){
			html+="<tr>";
			html+="<td class='left'>"+pod[x].company_name+"</td>";
			html+="<td class='left'>"+pod[x].order_id+"<br/>"+pod[x].item_id+"</td>";
			html+="<td>"+pod[x].order_status+"</td>";
			html+="<td>"+pod[x].product+"</td>";
			html+="<td class='numeric'>"+pod[x].expected_qty+"</td>";
			html+="<td class='numeric'>"+pod[x].received_qty+"</td>";
			if(pod[x].reasonText==null){
				pod[x].reasonText="NA";
			}
			html+="<td>"+pod[x].reasonText+"</td>";

			html+="</tr>";
		}
		
		var grid3 = $('#milkrun-detail');

		grid3.find('#milkrun-grid2').html(html);
		podTable = createDataTable("#milkrun-detail");

		$( "#grid-1, #grid-2" ).accordion({
			active: false
		});
		$('#grid-3').show().accordion('destroy').accordion({
			collapsible: true
		});

		
};
	var podTable = null

	function toggle(){
		var html = $(this).html();
		var target = $(this).parent().find('.foldable');
		if(html=='-'){
			target.hide();
			html='+'
		}else{
			target.show();
			html='-'
		}
		$(this).html(html);

	}	
	
	$('#filter .filter').each(function(){
		searchCriteria[$(this).attr('name')]=$(this).val();;
	});	

	$('#filter-merchant').on('click', createMerchantFilter);
	$('#search-button').on('click', createFilter);
	$('.toggle-fold').on('click', toggle)
		
	$('#search-button').trigger('click');
	
	$(".filter").keypress(function(event) {
		if (event.which == 13) {
			event.preventDefault();
		   $('#search-button').trigger('click');
		}
	});

		

});
