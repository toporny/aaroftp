@extends('app')

@section('content')

	<div class="row" style="margin:0px 0px 0px 5px;padding:0px 0px 0px 5px">
		<div class="col-md-12">
			<button  style="margin-left:10px;" onclick="javascript:refreshProduct()" id="refreshProducts" type="button" class="btn btn-primary">Refresh products list</button>
			<-- this button checks <a target="_new" href="{{ env ('URL_WITH_ALL_PRODUCT_LIST') }}">{{ env ('URL_WITH_ALL_PRODUCT_LIST') }}</a>
			for new  products ( I'mean new and update txt files)..
			<span id="spinner" style="float:left; display:none" class="spinner">
			    Loading...
			</span>
		</div>
	</div>

	<div class="row" style="margin:5px;padding:5px">
		<table class="table table-hover table-condensed table-bordered">
		<tr id="qwe">
			<th>Product Name</th>
			<th>uicode</th>
			<th>Source path</th>
			<th>Install time</th>
			<th>version</th>
			<th>action</th>
		</tr>

		@foreach($products as $product)
			<tr id="row_{{$product->id}}" class="active">
				<td class="col-md-3">{{$product->pname}}</td>	<!-- <tr class="danger"> -->
				<td class="col-md-1"><label><input type="checkbox" value="{{$product->uicode}}"name="prod_id[]"> {{$product->uicode}}</label></td>
				<td class="col-md-4">{{$product->txt_file_url}}</td>	<!-- <tr class="info"> -->
				<td class="col-md-2">{{$product->begin_install}}</td>	<!-- <tr class="info"> -->
				<td class="col-md-1">{{$product->version}}
@if ($product->zip_not_exist)
<button onclick="show_zip_is_missing('{{$product->txt_file_url}}')" type="button" class="btn btn-danger btn-xs">ZIP missing</button>
@endif
				</td>
				<td class="col-md-1">

					<select onchange="do_action(this.value, {{$product->id}})" class="form-control">
						<option value="0">- select -</option>
						<option value="show_txt">show txt file</option>
						<option value="remove_from_here">Remove from list  </option>
 					</select>
				</td>

			</tr>
		@endforeach
		</table>
	</div>


	<div class="row" style="margin:0px 5px 5px 0px;padding:0px">
		<div class="col-md-12">
			<button onclick="createUrlForInstallForm()" type="button" class="btn btn-success">Create URL for install form</button>
		</div>
	</div>

<!-- Scripts -->
 

<script style="text/javascript">

	var final_url = '';
	var number_of_products = 0;

	function getMainUrlAndCounter() {
		url = '{{ $url }}';
		var product_codes = '';
		number_of_products = 0;
		$("input[name='prod_id[]']").each( function () {
			if (this.checked) {
				number_of_products++;
				product_codes += $(this).val() + ',';
			}
		});

		if (number_of_products == 0) {
			alert('Select something first!');
			return false;
		}

		final_url = url + '/?uicode=' + product_codes.substring(0, product_codes.length - 1);
 		return number_of_products;
	};


	function getUrl() {
		var rest = '';
		($("#chkbox_with_email_id").prop('checked'))  ? rest += '&email=1' : rest += '';
		($("#chkbox_with_paypal_id").prop('checked')) ? rest += '&paypal=1' : rest += '';
		($("#chkbox_with_aweber_id").prop('checked')) ? rest += '&aweber=1' : rest += '';
		($("#chkbox_with_autoinstall_id").prop('checked'))  ? rest += '&autoinstall=1' : rest += '';
		if ($("#chkbox_with_free_product").prop('checked')) {
			console.log(final_url);
			if(number_of_products>1) {
				alert('(products list not allowed) only one free product per install form can be set.');
				$("#chkbox_with_free_product").removeAttr('checked');
			} else {
				rest += '&free_product=1';
			}
			
		}
		var URL = '<a href="'+final_url+rest+'">'+final_url+rest+'</a>';
		$('#final_url_id').html(URL);
	}



function createUrlForInstallForm() {


 	var number_of_products = getMainUrlAndCounter();
	if (number_of_products === false) return false;

	var my_message = '';

	my_message += '<div class="checkbox"><label><input onclick="getUrl()" id="chkbox_with_email_id" name="with_email" type="checkbox" checked>with email notification</label></div>';
	my_message += '<div class="checkbox"><label><input onclick="getUrl()" id="chkbox_with_paypal_id" name="with_paypal" type="checkbox">with paypal</label></div>';
	my_message += '<div class="checkbox"><label><input onclick="getUrl()" id="chkbox_with_aweber_id" name="with_paypal" type="checkbox">with aweber</label></div>';
	my_message += '<div class="checkbox"><label><input onclick="getUrl()" id="chkbox_with_autoinstall_id" name="with_autoinstall" type="checkbox" checked>with autoinstall</label></div>';
	my_message += '<div class="checkbox"><label><input onclick="getUrl()" id="chkbox_with_free_product" name="with_free_product" type="checkbox">with free_product</label></div>';
	my_message += '<span class="break-all" id="final_url_id"></span>';
	bootbox.dialog({
		message: my_message,
		title: "URLs for install "+number_of_products+" products by one form:",
		buttons: {

				gotourl: {
					label: "GO TO URL",
					className: "btn-success",
					// final_url+rest+
					callback: function() {
						document.location = $('#final_url_id').find('a')[0].href;
					}
				},

				installed: {
					label: "CANCEL",
					className: "btn-warning",
				},

		}
	});
	getUrl();

}


function refreshProduct() {

	console.log('Refresh products...');
	$('#spinner').show();
	$.ajax({
	  method: "GET",
	  url: "/refresh_products_list",
	  contentType: "application/json",
	  async: true,
	  data: { 
	  		}
	})
	.done(function( msg ) {
		console.log('Refresh products done');
		//$('#error_message_content').html('');
		var msg_parsed  =JSON.parse(msg);
		if (msg_parsed.status == 1) {
			bootbox.alert(msg_parsed.msg, function() {
				window.location.reload();
			});
		}
		else 
		{
			bootbox.alert("<h3>Problem with refresh products</h3>"+ msg_parsed.msg, function() {
			  //msg_parsed.msg;
			});
		}

		$('#spinner').hide();
	})
	.fail(function(data) {
		$('#spinner').hide();
		bootbox.alert("<h3>Problem with refresh products</h3>"+ msg_parsed.msg, function() {
		  //msg_parsed.msg;
		});
		console.log( "Problem with refresh products.", data );
	})
	.always(function(data) {
		console.log( "complete",data );
	});
 
 

}
 





 function do_action (action, product_id) {

	if (action == 0) return;

	// if (action.substr(0,7) == 'install') { // do wawalenia i przerobienia
	// 	var javascript_redirect = '{{ url() }}/install/'+product_id+'/'+action.substr(8);
	// 	console.log('javascript_redirect', javascript_redirect);
	// 	window.location.href = javascript_redirect;
	// 	return;
	// }

	console.log(product_id);
	$('#spinner').show();

	$.ajax({
	  method: "GET",
	  url: "/product_action",
	  contentType: "application/json",
	  async: true,
	  data: { 
	  			action: action,
			  	product_id: product_id
	  		}
	})
	.done(function( msg ) {
		console.log('action products done');
		var msg_parsed = JSON.parse(msg);
		if (msg_parsed.status == 1) {
			bootbox.alert(msg_parsed.msg, function() {
				if (action == 'remove_from_here') {
					$('#row_'+product_id).remove();
				}
			});
		}
		else 
		{
			bootbox.alert("<h3>Problem with refresh products</h3>"+ msg_parsed.msg, function() {
				//msg_parsed.msg;
			});
		}

		$('#spinner').hide();

	})
	.fail(function(data) {
		$('#spinner').hide();
		bootbox.alert("<h3>Problem with "+action+"</h3>"+ msg_parsed.msg, function() {
		  //msg_parsed.msg;
		});
		console.log( "Problem with refresh products.", data );
	})
	.always(function(data) {
		$('#spinner').hide();
		console.log( "json .always(function(data)) complete", data );
	});

}


	function show_zip_is_missing(err) {

		var file1 = err;
		var file2 = err.replace(/\.txt/,'.zip');

		var message = '<span style="color:#00AA00">'+file1 + '</span></br>';
		message += '<span style="color:#FF0000">'+file2 + '</span>';

		bootbox.dialog({
			message: message,
			title: '<span style="color:red">ZIP</span> file is missing',
			buttons: {
				installed: {
					label: "CLOSE",
					className: "btn-warning",
				},
			}
		});
	}

</script>
@endsection
