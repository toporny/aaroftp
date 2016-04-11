@extends('app')

@section('content')

	<div class="row" style="margin:0px;">
		<div class="col-md-12 text-right"> 

			<?php echo $transactions->appends(['status_filter' => $status_filter, 'date_filter' => $date_filter ])->render(); ?>

			<div class="form-inline">
				<input id="text_search" value="{{ $text_search }}" type="text" placeholder="name, email, domain" class="form-control">
				<input type="button" value=">>" class="form-control">
				<a href="{{ url() }}/home?status_filter=waiting" class="btn btn-info" role="button">{{ $counts_descriptions[0]->count_waiting }} </a>
				<a href="{{ url() }}/home?status_filter=installed" class="btn btn-success" role="button">{{ $counts_descriptions[0]->count_installed }} </a>
				<a href="{{ url() }}/home?status_filter=problem" class="btn btn-danger" role="button">{{ $counts_descriptions[0]->count_problem }} </a>
				<a href="{{ url() }}/home?status_filter=cancelled" class="btn btn-primary" role="button">{{ $counts_descriptions[0]->count_cancelled }} </a>
				<a href="{{ url() }}/home?status_filter=all" class="btn btn-default" role="button">{{ $counts_descriptions[0]->count_all }} </a>
				<input id="date_filter" type="text" placeholder="date ordered filter" value="{{ $date_filter }}" class="form-control">
			</div>

		</div>
	</div>


	<div class="row" style="margin:5px;padding:5px">
		<table class="table table-condensed table-bordered break-all">
		<tr>
			<th onclick="sort(this, 'user_firstname')">Name</th>
			<th onclick="sort(this, 'user_email')">Email</th>
			<th onclick="sort(this, 'pname')">Product(s) / code / domain</th>
			<th onclick="sort(this, 'date_ordered')">Ordered / Installed</th>
			<th>Action</th>
		</tr>

		@foreach($items as $transaction)
			<tr id="tr_{{$transaction->id}}" class="@if ($transaction->status  == 'installed') bg-success @endif
				@if ($transaction->status  == 'waiting') bg-info @endif
				@if ($transaction->status  == 'problem') btn-danger @endif
				@if ($transaction->status  == 'cancelled') bg-primary @endif ">
				<td class="col-md-1"><a class="blacklink" title="show all transactions with this name and surname" href="{{ url() }}/home?text_search={{ $transaction->user_firstname }}%20{{ $transaction->user_lastname }}">{{ $transaction->user_firstname }} {{ $transaction->user_lastname }}</a></td>
				<td class="col-md-2"><a class="blacklink" title="show all transactions with this email" href="{{ url() }}/home?text_search={{ $transaction->user_email }}">{{$transaction->user_email}}</a></td>
				<td class="col-md-3">

@if ($transaction->aweber)
<a title="Don't forget to set up aweber" href="#" type="button" class="btn btn-default btn-xs">Aweber</a>
@endif

@if ($transaction->free_product)
<a title="Free product means system does not update '{{ env('AWS_BUCKET') }}' bucket. Click to see all free installations." href="{{ url() }}/activities/free_installations" type="button" class="btn btn-default btn-xs">free product</a>
@endif

@if (!$transaction->ftp_path_ok)
<a title="FTP PATH is not set properly!" href="{{ url() }}/edit_transaction/{{ $transaction->id }}" type="button" class="btn btn-danger btn-xs">FTP path!</a>
@endif
					@if ($transaction->ready_to_install)
						<button onclick="express_install_prepare1 ({{$transaction->id}}) " type="button" class="btn btn-warning btn-xs">Express Install</button>
					@endif
					<a target="_new" href="{{$transaction->website}}/{{$transaction->directory}}">{{$transaction->pname}}</a>
					<b>{{$transaction->uicode}}</b> v. <a target="_new" href="{{$transaction->website}}/admin/?p=products&user=admin&pass=ssmachine
					">{{$transaction->version}}</a><br>
					<a class="blacklink" target="_new" href="{{ $transaction->website }}">{{ $transaction->website }}</a>
				</td>
				<td class="col-md-1">
					<span>{{ str_limit($transaction->date_ordered, 16 , '') }}</span> </br>
					{{ str_limit($transaction->date_installed, 16, '') }} 
					<span
				</td>
				<td class="col-md-1">

					<select onchange="do_action(this, '{{$transaction->id}}' )" class="form-control">
						<option value="0">---</option>
@if ($transaction->ready_to_install)						
						<option value="express_install">[x] express install</option>
@endif
						<option value="install_without_email">[✓] install without email</option>
						<option value="only_send_email">[✓] send email</option>
						<option value="edit">[✓] edit</option>
@if ($auth_user == 'admin')
						<option value="show_transaction_details">[✓] show transaction details</option>
						<option value="remove_from_here">[✓] remove from this list</option>
@endif
						<option value="change_transaction_status">[✓] change status</option>
						<option value="show_logs">[✓] show logs</option>
						<option value="checkfiles_by_net2ftp">[✓] step by step install</option>
					</select>

				</td>
			</tr>
		@endforeach
		</table>
	</div>


<style type="text/css">
.express_padding {
	margin:2px 2px 2px 0px;
	padding:3px;
}

.small_icon  {
    padding-left:20px;
    width: 15px;
    height: 15px;
}


.small_icon.spinner  {
	background:url({{ url() }}/img/spinner.gif) no-repeat;
}

.small_icon.ok {
	background:url({{ url() }}/img/16x16_icon_ok.gif) no-repeat;
}

.small_icon.error {
	background:url({{ url() }}/img/16x16_icon_error.png) no-repeat;
}

</style>



<script type="text/javascript">



function express_install (oPass) {
	console.log('oPass = ',oPass);

}

function show_modal_with_products_list() {

}



function actionModal(id) {
  this.id = id;
  
  this.transactions_data = {};
  
  this.api_query_object = {
  	products_to_install: [123,34,45,33],
  	install_admin: true,
  	change_status: true,
  	send_email: true
  };

  this.start = start;
  this.showModal = showModal;


  

  function showModal() {

	var my_message = "<h4>Select actions for "+ transactions_data.name_surname+":</h4>";
	$.each( transactions_data.product_list, function( index, value ){
		my_message += '<div class="express_padding bg-info">\n';
		my_message += '  <input id="divxpres_id_'+value.id+'" name="xpres_id_['+value.id+']" type="checkbox" checked>\n';
		my_message += '  <span class="small_icon spinner">install '+value.name+'" ('+value.id+') </span>\n';
		my_message += '</div>\n';
	});

	my_message += '<div class="express_padding bg-info"> <input name="xpres_admin" type="checkbox" checked> <span class="small_icon error"> install "SSM-admin.zip"</span></div>\n';
	my_message += '<div class="express_padding bg-info"> <input name="xpres_change_statuses" type="checkbox" checked> <span class="small_icon error"> change statuses to "installed" if all jobs done.</span></div>\n';
	my_message += '<div class="express_padding bg-info"> <input name="xpres_email" type="checkbox" checked> <span class="small_icon ok"> and send <a href="{{ $product_installed_email_template }}"> email </a> to customer with confirmation.</span></div>\n';
	
	bootbox.dialog({
		message: my_message,
		title: 'Express install',
		buttons: {
			start_express: {
				label: "START",
				className: "btn-primary",
				callback: function() {

					console.log('start!');
					prepare_var = {};
					$("input").each( function () {

						if ((this.name.substr(0,9) == 'xpres_id_') && (this.checked)){
							console.log(this.id);
							console.log(this.name);
							console.log(this.value);		
						}

						if (this.checked) {
							// counter++;
							// product_codes += $(this).val() + ',';
						}
					});
					// var oPass = {ids: [1,2,3], admin: true, email:true };
					// express_install(oPass)
					// data = {select_element:element, id: id, var: 'installed' };
					// change_transaction_status('/do_action', data); //TODO: make this to change date_installed and date_updated
				}
			},
			exit: {
				label: "CANCEL",
				className: "btn-warning"
		  	},
		}
	});
  }
 

  function start() {

	bootbox.dialog({
	    message  : "Wait...",
	    timeOut : 9000
	});



	var jqxhr = $.get('{{ url() }}'+"/do_action?action=getDataForExpressInstall&id="+this.id, function() {
	  console.log( "success" );
	})
	.done(function(data) {
		bootbox.hideAll();
		console.log( "second success", data );
		console.warn(jQuery.parseJSON( data ));
		transactions_data = jQuery.parseJSON( data )
		//this.transactions_data = jQuery.parseJSON( data );
		//show_modal_with_products_list(data);
		showModal();

	})
	.fail(function() {
		alert('error');
	})
	.always(function() {
		console.log( "finished" );
	});
  }
}

/*
express install object

*/

function express_install_prepare1 (id) {

	var modal = new actionModal();
	modal.start(id);

	return;
	// $("input[name='prod_id[]']").each( function () {
	// 	if (this.checked) {
	// 		counter++;
	// 		product_codes += $(this).val() + ',';
	// 	}
	// });

	// get all timestamp and email from that ID
	// get all products which belong to that timestamp and email 

//	var my_message = "<h4>Install "+counter+" product/s and additional elements for Frank Moxley</h4>";


}

 

	function sort(current, co) {
		$(current).addClass('sort_active');
	}

	function do_action(select_element, id) {
		
		switch (select_element.value) {

			// case 'uninstall' : {
			// 	bootbox.alert("Do this manually.", function() {
			// 	});
			// 	break;
			// }
			case 'express_install' : {
				express_install_prepare1 (id);
				break;
			}			

			case 'install' : {

				var text_msg  = '<h3>Not yet ready.</h3>';
				text_msg += '<p>Insted use:<br>';
				text_msg += '- <u>install without email</u> (for every record which has the same <b>email</b> and <b>ordered time</b>)<br>';
				text_msg += '- and select <u>only send email</u><br><br>';
				text_msg += 'system will send only one email with all products specified.<br>';
				text_msg += 'Check outbox at supersalesmachine@gmail.com / ssmachinesupport<br>';
				text_msg += 'to see how the email looks like.</p>';

				bootbox.alert(text_msg, function(result) {
				}); 

				// bootbox.confirm("Are you sure? All files on destination server will be overwritten.", function(result) {
				// 	if (result) { 
				// 		data = {select_element: select_element, action: "do_big_install", id: id, email: 1, var: 'not_used' };
				// 		do_install('/do_action', data);
				// 	}
				// }); 
				// break;
				break;
			}


// ===============================================================================================

			case 'only_send_email' : {

				bootbox.dialog({
				    message  : "Wait...",
				    timeOut : 9000
				});

				var jqxhr = $.get('{{ url() }}'+"/do_action?action=only_show_email_preview&id="+id, function() {
				  // console.log( "success" );
				})
				.done(function(data) {
					bootbox.hideAll();

					transactions_data = jQuery.parseJSON( data );

if (transactions_data.status ==-2) {

	bootbox.alert("<h3>Problem</h3>"+ transactions_data.msg, function() {});
}
else {


					bootbox.dialog({
					  message: transactions_data.msg,
					  title: "Are you sure to send this email?",
					  buttons: {
					    send: {
					      label: "Send",
					      className: "btn-success",
					      callback: function() {
					      	if (transactions_data.status == 1) {
								data = {select_element: select_element, action: "only_send_email", id: id };
								do_install('/do_action', data);	      		
					      	}
					      	else alert('error with transactions_data.status!!');
					      }
					    },
					    cancel: {
					      label: "Cancel",
					      className: "btn-info",
					    },
					  }
					});
}
				})
				.fail(function() {
					alert('error');
				})
				.always(function() {
					console.log( "getOnlySendEmailPreview finished" );
				});







/*





				// ?????????
				var message = '<h3>Only send email</h3>';
				message += '<p>Are you sure?</p>';
				message += '<p>(This option sends only one email with list of all products that customer ordered)</p>';
				message += '<p><b>Email looks very similar to this:</b></p>';

				message += '<p>Thank you again for investing in our install service.</p>';
				message += '<p>A fully working version of product/s:</br>';
				message += '<ul>';
				message += '<li>Resell Rights Ninja Series Standard</li>';
				message += '<li>Resell Rights Ninja Series Upgrade</li>';
				message += '<li>Resell Rights Ninja Series Upgrade Aff</li>';
				message += '<li>Silly Newbie Mistakes Bonus</li>';
				message += '<li>Super Sales Machine Sales Funnel</li>';
				message += '</ul>';
				message += 'have/has just been installed on your server.</p>';
				message += '<p>Please log into your admin area to get access to your links:</p>';
				message += '<ul>';
				message += '<li>http://domain1test.byonecar.com/admin</li>';
				message += '<li>user: admin</li>';
				message += '<li>pass: ssmachine</li>';
				message += '</ul>';
				message += '</p>';
				message += '<p>Thanks,<br>';
				message += 'SuperSalesMachine.com Team!</p>';
				message += '<p><font color="red">Please check supersalesmachine@gmail.com/ssmachinesupport outbox!</font></p>';

				bootbox.confirm(message, function(result) {
					if (result) { 
						data = {select_element: select_element, action: "only_send_email", id: id };
						do_install('/do_action', data);
					}
				}); */
				break;
			}

			case 'install_without_email' : {
				bootbox.confirm("<b>install without email</b> </br>Are you sure?</br>All files on destination server will be overwritten.", function(result) {
					if (result) { 
						data = {select_element: select_element, action: "do_big_install", id: id, email: 0, var: 'without_email' };
						do_install('/do_action', data);
					}
				}); 
				break;
			}

			case 'edit' : {
				location.href='{{ url() }}/edit_transaction/'+id;
				break;
			}

			case 'show_logs' : {
				location.href='{{ url() }}/show_logs/'+id;
				break;
			}

			case 'remove_from_here' : {
				bootbox.confirm("Are you really want to remove this transaction from here?<br> If yes please consider to uninstall first.", function(result) {
				if (result) { 
					data = {select_element:select_element, action: "remove_from_here", id: id, var: 'not_used' };
					do_ajax('/do_action', data);
				}
				}); 
				break;
			}

			case 'change_transaction_status' : {
				bootbox.dialog({
				  message: "please set status for transaction",
				  title: "Set status",
				  buttons: {

				    installed: {
				      label: "installed",
				      className: "btn-success",
				      callback: function() {
						data = {select_element:select_element, action: "change_transaction_status", id: id, var: 'installed' };
						do_ajax('/do_action', data);
				      }
				    },

				    waiting: {
				      label: "waiting",
				      className: "btn-info",
				      callback: function() {
				      	data = {select_element:select_element, action: "change_transaction_status", id: id, var: 'waiting' };
				        do_ajax('/do_action', data);
				      }
				    },

				    problem: {
				      label: "problem",
				      className: "btn-danger",
				      callback: function() {
				      	data = {select_element:select_element, action: "change_transaction_status", id: id, var: 'problem' };
				        do_ajax('/do_action', data);
				      }
				    },

				    cancelled: {
				      label: "cancelled",
				      className: "btn-primary",
				      callback: function() {
						data = {select_element:select_element, action: "change_transaction_status", id: id, var: 'cancelled' };
						do_ajax('/do_action', data);
				      }
				    },

				    close_window: {
				      label: "do nothing",
				      className: "btn-default",
				      callback: function() {
/*						data = {select_element:select_element, action: "change_transaction_status", id: id, var: 'cancelled' };
						do_ajax('/do_action', data);*/
				      }
				    }
				  }
				});
				break;
			}

			case 'checkfiles_by_net2ftp' : 
				window.open( '{{ url() }}/manualMode/'+id, '_blank' );			
			break;
				

			case 'show_transaction_details' :
				data = {action: "show_transaction_details", id: id, var: 'cancelled' };
				do_ajax('/do_action', data);
			break;
		}
	}

 

function do_install (path, data) {

	$( data.select_element ).after( '<div class="throbber-loader"></div>' );

	var pass_data = {
		action: data.action,
		id: data.id,
		email: data.email
	};

	if (data.email == 0) {
		pass_data.email = 0;
	}

	$.ajax({
		method: "GET",
		url: path,
		contentType: "application/json",
		async: true,
		data: pass_data
	})
	.done(function( msg ) {

		try {
			obj = jQuery.parseJSON(msg);
			if (obj[obj.length-1].status == 1) {
				$("#tr_"+data.id).removeClass().addClass('success');
			}
			else if (obj[obj.length-1].status == -55) { // very bad!
				bootbox.alert("<p>"+obj[obj.length-1].msg+"</p>", function() {
				});
			}
			else if (obj[obj.length-1].status == -1) {
				$("#tr_"+data.id).removeClass().addClass('btn-danger');
				bootbox.alert("<h3>ErrorStatus=-1 - problem with installation.</h3><p>Please check logs</p>", function() {
				});
			} else  {
				$("#tr_"+data.id).removeClass().addClass('btn-danger');
				bootbox.alert("<h3>Status unknown problem with installation.</h3><p>Please check logs</p>", function() {
				});				
			}

		}
		catch(err) {
			$("#tr_"+data.id).removeClass().addClass('btn-danger');
			bootbox.alert("<h3>problem with installation.</h3><p>Please check logs</p>", function() {
			});
		}

		$('.throbber-loader').remove();

	})
	.fail(function() {
		$('.throbber-loader').remove();
		$("#tr_"+data.id).removeClass().addClass('btn-danger');
		bootbox.alert("<h3>problem with installation.</h3><p>Please check logs</p>", function() {
		});
	})
	.always(function(data) {
		$('.throbber-loader').remove();
	});	
}



function do_ajax (path, data) {

	$( data.select_element ).after( '<div class="throbber-loader"></div>' );

	var pass_data = {
		action: data.action,
		id: data.id,
		var: data.var
	};

	$.ajax({
	  method: "GET",
	  url: path,
	  contentType: "application/json",
	  async: true,
	  data: pass_data
	})
	.done(function( msg ) {
		var msg_parsed = JSON.parse(msg);
		if (msg_parsed.status == 1) {
			if (data.action == 'change_transaction_status') {
				var tmp = { installed:'success', waiting: 'info', problem:'btn-danger', cancelled:'btn-primary'};
				console.log(tmp[data.var]);
				$("#tr_"+data.id).removeClass().addClass(tmp[data.var]);
			}
			if (data.action == 'show_transaction_details') {
				bootbox.alert("<h3>Transaction details</h3>"+ msg_parsed.msg, function() {
				});
			}
			if (data.action == 'remove_from_here') {
				bootbox.alert("<h3>Removed </h3>"+ msg_parsed.msg, function() {
					$("#tr_"+data.id).remove();
				});
			}	
		}
		else 
		{
			console.log('problem with respond = ', msg);
			bootbox.alert("<h3>Problem </h3>"+ msg_parsed.msg, function() {
			});
		}

		$('.throbber-loader').remove();

	})
	.fail(function() {
		$('.throbber-loader').remove();
		
		$("#tr_"+data.id).removeClass().addClass('btn-danger');
		console.log('PROBLEM with getting path: '+path, data);
	})
	.always(function(data) {
		$('.throbber-loader').remove();
	});

}

$(document).ready(function() {

	$('#text_search').change(function() {
		location.href='{{ url() }}/home?text_search='+$('#text_search').val();
	});

	$('#date_filter').change(function() {
		location.href='{{ url() }}/home?date_filter='+$('#date_filter').val();
	});

});


$('#date_filter').datepicker({
    format: "yyyy-mm-dd",
    keyboardNavigation: false,
    forceParse: false,
    autoclose: true,
    todayHighlight: true,
    toggleActive: true
});


</script>


@endsection
