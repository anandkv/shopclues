var qtip = function (source) {

    var userId = $(source).attr('userId');
    var status = $(source).attr('status');
    $(source).qtip({
        content:{
            text: 'Loading...',
            ajax: {
                url: 'orderDetails.php', // URL to the local file
                type: 'POST', // POST or GET
                data: {userRowId : userId, status : status}, // Data to pass along with your request
                success: function(data, status) {
                    var obj = eval(data);
                    var html="<div>";
                    for(x in obj ){
                        html+="<ul class='bookingDetails'>";
                        html+="<li>Order Number:"+obj[x].orderId+"</li>";
                        html+="<li>Customer Name:"+obj[x].customerName+"</li>";
                        html+="<li>Customer Phone:"+obj[x].customerPhone+"</li>";
                        html+="<li><hr></li>";
                        html+="</ul>"
                    }
                    html+="</div>";

                    this.set('content.text', html);
                }
            }
        }
        , position:{my:"top right",
            at:"bottom"}, style:{
            classes:'ui-tooltip-rounded'
        }

    });
}

var createDataTable = function (selector) {

//$(selector).dataTable().fnDestroy();

/* 
	var nCloneTh = document.createElement( 'th' );
    var nCloneTd = document.createElement( 'td' );
	nCloneTd.innerHTML = '<img src="../images/details_open.png">';
    nCloneTd.className = "center";

	 $( selector +' thead tr').each( function () {
        this.insertBefore( nCloneTh, this.childNodes[0] );
	} );
	$(selector+' tbody tr').each( function () {
        this.insertBefore(  nCloneTd.cloneNode( true ), this.childNodes[0] );
    } );
*/
	$(selector).show();

	return $(selector).dataTable({
		"aoColumnDefs":[
			{
				"bSortable":false,
				"aTargets":['no-sort']
			},
			{
				"bVisible":false,
				"aTargets":['no-show']
			}
		],
		"bAutoWidth":false,
	/*	"aaSorting":[
			[0, 'desc']
		],
	*/
		"bJQueryUI":false,
		"bDestroy": true,
		"bRetrieve": true,
		"bFilter": true,
		"sDom": 't<"dt-footer"fpl>',
		"sPaginationType":"full_numbers"
	});
}
function showLoader (){
	$('#ajax_loading_box').modal({
		escClose : false,
			modal : true
	});
}
$(document).ready(function () {
	$('#ajax_loading_box').ajaxStop(function() {
		$.modal.close();
	});
	$('#ajax_loading_box').ajaxStart(function() {
		showLoader();
	});
});
