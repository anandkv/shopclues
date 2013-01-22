var searchResult;
var searchCriteria = new Object();
var mrTable;
var merchants = new Object();
var orderTable;
var nTr ;
var podTable = null;
	
$(document).ready(function () {


	$('.date-picker').datepicker(	{ 
			defaultDate: new Date(), 
			dateFormat: 'yy-mm-dd' 
		}).datepicker('setDate' , new Date());
	 var xDate = new Date();
	 with(xDate){
	    setMonth(getMonth()-1);
	    //setDate(1);
	    }
	$('#from_date').datepicker(	{ 
		defaultDate:xDate, 
		dateFormat: 'yy-mm-dd' 
	}).datepicker('setDate' , xDate);

	function getResult(data,callback){
		if(searchResult!=null){
			searchResult.abort();
		}
		searchResult =  $.get("../api/json/pod.php",data,callback );
	}

	
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
		data.action = 'MilkRunSummary';

		getResult(data,updateSummary );
		var podTable = null
	}


var milkrun;
	function updateSummary (data){
		milkrun = $.parseJSON(data);
		if(mrTable!=null){
			mrTable.fnDestroy();
		}
		
		var html="";
		for (x in milkrun){
			var pickupboy = milkrun[x].pickupboy;
			html+="<tr manifestId='"+pickupboy.manifest_id+"' pid='"+pickupboy.pickupboy_id+"' class='mri-tr'>";
			html+="<td>"+pickupboy.date+"</td>";
			html+="<td>"+pickupboy.pickupboy_id+"</td>";
			html+="<td>"+pickupboy.manifest_id+"</td>";
			html+="<td>"+pickupboy.expected_qty+"</td>";
			html+="<td>"+pickupboy.received_qty+"</td>";
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
			/*
			$('#pb-name').html(data.pid);
			searchCriteria.manifestId = data.manifestId = $(this).attr('manifestId');
			$.get("../api/json/pod.php",data, listMerchants);
			*/
			listMerchants($(this).attr('pid'));

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

	function listProducts(data){
		var pod = $.parseJSON(data);
		if(podTable!=null)
		podTable.fnDestroy();
		var html="";
		for (x in pod){
			html+="<tr>";
			html+="<td class='left'>"+pod[x].company_name+"</td>";
			html+="<td>"+pod[x].order_id+"<br/>"+pod[x].item_id+"</td>";
			html+="<td>"+pod[x].order_status+"</td>";
			html+="<td>"+pod[x].product+"</td>";
			html+="<td>"+pod[x].expected_qty+"</td>";
			html+="<td>"+pod[x].received_qty+"</td>";
			if(pod[x].reasonText==null){
				pod[x].reasonText="NA";
			}
			html+="<td>"+pod[x].reasonText+"</td>";

			html+="</tr>";
		}
		
		var grid3 = $('#milkrun-detail');

		grid3.find('#milkrun-grid2').html(html);
		podTable = createDataTable("#milkrun-detail");

		$( "#grid-1" ).accordion({
			active: false
		});
		$('#grid-3').show().accordion('destroy').accordion({
			collapsible: true
		});
		
	};

	function listMerchants(pid){
		var html="";	
		var pod = milkrun[pid].merchants;

		for (x in pod){
			var merchant = pod[x]; 
			merchants[merchant.merchant_id] = merchant;
			html+="<tr merchant_id='"+pod[x].merchant_id+"'>";
			html+="<td class='left'><b>"+pod[x].company_name+"</b><br/>"+pod[x].company_add+"</td>";
			html+="<td>"+pod[x].expected_qty+"</td>";
			html+="<td>"+pod[x].received_qty+"</td>";
			html+="<td>"+pod[x].status+"</td>";
			html+="</tr>";
		}
		
		var grid2 = $('#grid-2').clone();
		grid2.find('#order-grid').html(html);
		//orderTable = createDataTable("#milkrun-orders");

		$('#order-grid tr').die('click');
		
		$("#order-grid tr").live('click' , function (event) {
			$('#order-grid tr').each(function () {
				$(this).removeClass('row_selected');
            });
            $(event.target.parentNode).addClass('row_selected');
			var data= searchCriteria;
			data.action = 'listProducts';

			data.merchant_id = $(this).attr('merchant_id');
			$('#merchant-name').html(merchants[data.merchant_id].company_name);
			$.get("../api/json/pod.php",data, listProducts);
        });
		/*$( "#grid-2" ).accordion('destroy').accordion({
			collapsible: true
		});*/

		grid2.show();
		$('#extra').remove();
		nTr.after("<tr id='extra'><td colspan='"+nTr.children('td').size()+"'>"+grid2.html()+"</td></tr>");
		//orderTable = createDataTable("#milkrun-orders");
		$( "#grid-1" ).accordion('destroy').accordion({
			collapsible: true
		});
	}

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
	
	/*
	var data = searchCriteria;
	data.action = 'MilkRunSummary';
	$.get("../api/json/pod.php",data,updateSummary );
	
*/

//$('#search-button').trigger('click');
	
$(".filter").keypress(function(event) {
    if (event.which == 13) {
        event.preventDefault();
       $('#search-button').trigger('click');
    }
});


		

});
