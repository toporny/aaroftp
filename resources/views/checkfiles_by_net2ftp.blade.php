@extends('app')

@section('content')

<script style="text/javascript">

function showMeTheMessage (element, kind, message) {
	switch (kind) {
		case 'info' :
		console.log('info');
			$(element).parent().find("p.manualModeMessage").css( "color", "blue" ).html(message);
		break;
		case 'success' :
			$(element).parent().find("p.manualModeMessage").css( "color", "#02A918" ).html('<b>'+message+'</b>');
		break;
		case 'danger' :
			$(element).parent().find("p.manualModeMessage").css( "color", "red" ).html(message);
		break; 
		case 'spinner' :
			$(element).parent().find("p.manualModeMessage").css( "color", "blue" ).html('<img src="/img/spinner.gif"> '+message);
		break;
		default: 
			$(element).parent().find("p.manualModeMessage").css( "color", "initial" ).html('');
		break;
		
	}

}


// =========================================================


// TODO: the same change_status procedure is in Transactions

function change_status (element, id)  {
	bootbox.dialog({
	  message: "Please set status for transaction",
	  title: "Set status",
	  buttons: {
	    installed: {
	      label: "installed",
	      className: "btn-success",
	      callback: function() {
			data = {select_element:element, id: id, var: 'installed' };
			change_transaction_status('/do_action', data); //TODO: make this to change date_installed and date_updated
	      }
	    },

	    waiting: {
	      label: "waiting",
	      className: "btn-info",
	      callback: function() {
	      	data = {select_element:element, id: id, var: 'waiting' };
			change_transaction_status('/do_action', data); // it changes 
	      }
	    },

	    problem: {
	      label: "problem",
	      className: "btn-danger",
	      callback: function() {
	      	data = {select_element:element, id: id, var: 'problem' };
			change_transaction_status('/do_action', data);
	      }
	    },

	    cancelled: {
	      label: "cancelled",
	      className: "btn-primary",
	      callback: function() {
			data = {select_element:element, id: id, var: 'cancelled' };
			change_transaction_status('/do_action', data);
	      }
	    },
	    close_window: {
	      label: "do nothing",
	      className: "btn-default",
	      callback: function() {
	      	// do nothing
	      }
	    }
	  }
	});
}

function showAskAndSendEmail (element, id) { 

	if( $(element).is(':checked')) {
		showMeTheMessage(element, 'spinner', 'please wait...');
		var jqxhr = $.get('{{ url() }}'+"/do_action?action=only_show_email_preview&id="+id, function() {
		  // console.log( "success" );
		})
		.done(function(data) {
			bootbox.hideAll();

			transactions_data = jQuery.parseJSON( data );

			if (transactions_data.status ==-2) {
				bootbox.alert("<h3>Problem</h3>"+ transactions_data.msg, function() {});
				showMeTheMessage(element);
				$(element).attr('checked', false);				
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

							var pass_data = {
								action: 'only_send_email',
								id: id 
							};

							$.ajax({
								method: "GET",
								url: '{{url()}}'+'/do_action',
								contentType: "application/json",
								async: true,
								data: pass_data
							})
							.done(function( msg ) {

								try {
									obj = jQuery.parseJSON(msg);
									if (obj[obj.length-1].status == 1) {
										showMeTheMessage(element, 'success', 'Email sent. Check gmail {{ env('MAIL_USERNAME') }}\'s SENT folder.' );
									}
									else  {
										$(element).attr('checked', false);
										showMeTheMessage(element, 'danger', 'Failed. Check the logs.');
									}

								}
								catch(err) {
									$(element).attr('checked', false);
									showMeTheMessage(element, 'danger', 'Failed. Check the logs.');
								}
							})
							.fail(function() {
								$(element).attr('checked', false);
								showMeTheMessage(element, 'danger', 'Failed. Check the logs.');
							})
							.always(function(data) {
							});	

				      	}
				      	else alert('error with transactions_data.status!!');
				      }
				    },
				    cancel: {
				      label: "Cancel",
				      className: "btn-info",
				      callback: function() {
				      	showMeTheMessage(element);
						$(element).attr('checked', false);
				      }
				    },
				  }
				});
			  }
		})
		.fail(function() {
			showMeTheMessage(element, 'danger', 'Error with ajax query.');
			$(element).attr('checked', false);				
		})
		.always(function() {
			console.log( "getOnlySendEmailPreview finished" );
		});

	} else {
		showMeTheMessage(element); // remove text
	}

}



// =========================================================

function change_transaction_status (path, data) {
	showMeTheMessage(data.select_element, 'spinner', 'Wait...');

	var pass_data = {
		action: 'change_transaction_status',
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
			showMeTheMessage(data.select_element, 'success', 'Changing status to: "'+ (data.var)+'"... Done!' );
		}
		else 
		{
			showMeTheMessage(data.select_element, 'danger', 'Failed');
		}

	})
	.fail(function() {
		showMeTheMessage(data.select_element, 'danger', 'Failed');
	})
	.always(function(data) {
		//  $('.throbber-loader').remove();
	});


}

// =========================================================

function do_ajax(element, action){

	var global_errors = {
		'upload_product'     : 'Global Err. Problem with sending zipped product to FTP server. Check Logs.',
		'upload_admin'       : 'Global Err. Problem with sending zipped admin to FTP server. Check Logs.',
		'upload_downloader'  : 'Global Err. Problem with sending downloader.php to FTP server. Check Logs.',
		'unzip_product'      : 'Global Err. Problem with unzipping product on customer server. Is downloader.php there? Check Logs.',
		'unzip_admin'        : 'Global Err. Problem with unzipping admin on customer server. Is downloader.php there? Check Logs.',
		'remove_downloader'  : 'Global Err. Problem with remove downloader. Check Logs.',
		'change_user_file'   : 'Global Err. Problem with changing "admin/user.php". Please make a sure "downloader.php" and unpacked "SSM-admin.zip" are there. Check logs. ',
		'send_email'         : 'Global Err. Problem with sending email with instalation confirmation to customer. Check logs. '
	}

	switch (action) {
		case 'upload_product'    : url = "/ManualDoFTPactionAjaxRequest/{{ $id }}"; break; 
		case 'upload_admin'      : url = "/ManualDoFTPactionAjaxRequest/{{ $id }}"; break;
		case 'upload_downloader' : url = "/ManualDoFTPactionAjaxRequest/{{ $id }}"; break;
		case 'unzip_product'     : url = "/ManualDownloaderAjaxRequest/{{ $id }}"; break; 
		case 'unzip_admin'       : url = "/ManualDownloaderAjaxRequest/{{ $id }}"; break; 
		case 'remove_downloader' : url = "/ManualDoFTPactionAjaxRequest/{{ $id }}"; break; 
		case 'change_user_file'  : url = "/ManualDownloaderAjaxRequest/{{ $id }}"; break;
		case 'send_email'        : url = "/ManualDoFTPactionAjaxRequest/{{ $id }}"; break; 
		default: alert ('action not defined!');
	}

	$.ajax({
	  method: "GET",
	  url: url,
	  contentType: "application/json",
	  async: true,
	  data: {
	  			action: action
			}
	})
	.done(function( msg ) {

		var msg_parsed = JSON.parse(msg);

		// in this place I nedd to check only LAST element.
		console.log('msg_parsed', msg_parsed);
		var last_element = msg_parsed.slice(-1).pop();

		console.log('last_element', last_element);

		if (last_element.status == 1) {
			var message =  'Done!';
			showMeTheMessage(element, 'success', message);
		}
		else {

			if (msg_parsed.constructor === Array) {
				error_message = last_element.msg;
				showMeTheMessage(element, 'danger', error_message);
			}
			else {
				console.warn('show global error');
				error_message = global_errors[action];
				showMeTheMessage(element, 'danger', error_message);
			}
		}
	})
	.fail(function(jqXHR, textStatus ) {
		var error_message = global_errors[action];
		showMeTheMessage(element, 'danger', error_message);
		console.warn( error_message, jqXHR, textStatus );
	})
	.always(function() {
	});
 
}

function doManualModeAction(element, action) {

	if( $(element).is(':checked')) {
		showMeTheMessage(element, 'spinner', 'please wait...');
		do_ajax(element, action);
	} else {
		showMeTheMessage(element); // remove text
	}
}

</script>

<div class="row" style="margin:0px;" id="checkfiles_by_net2ftp">

	<div class="col-md-6 text-left"> 

		<div class="panel panel-default">
			<div class="panel-heading">Check files by FTP client: <a href="{{ $website }}"><b>{{ $website }}</b></a>

			</div>

			<div class="panel-body">


				<form id="LoginForm2" action="http://alltic.home.pl/aaron/net2ftp/index.php" method="post" onsubmit="return CheckInput(this);">
					<fieldset>
						<div style="margin-top: 10px;">
							<label>FTP server</label>
							<input type="text" name="ftpserver" value="" class="form-poshytip" title="Example: ftp.server.com, 192.123.45.67">
							<input type="text" name="ftpserverport" value="21" style=" width: 45px;" maxlength="5">
						</div>
						<div style="margin-top: 10px;">
							<label>Username</label>
							<input type="text" name="username" value="" class="form-poshytip" title="Enter your username">
							<input name="anonymous" value="1" onclick="do_anonymous(form);" type="checkbox" style="position: absolute; left: 330px; top: 85px;">
							<div style="position: absolute; left: 380px; top: 85px;">Anonymous</div>
						</div>
						<div style="margin-top: 10px;">
							<label>Password</label>
							<input type="text" name="password" value="" class="form-poshytip" title="Enter your password">
							<input name="passivemode" value="yes" type="checkbox" style="position: absolute; left: 330px; top: 130px;">
							<div style="position: absolute; left: 380px; top: 130px;">Passive mode</div>
						</div>
						<div style="margin-top: 10px;">
							<label>Initial directory (FTP_DIR)</label>
							<input type="text" name="directory" value="" class="form-poshytip" title="Enter the initial directory">
							<input name="sslconnect" value="yes" type="checkbox" style="position: absolute; left: 330px; top: 175px;">
							<div style="position: absolute; left: 380px; top: 175px;">SSL</div>
						</div>
						<div style="margin-top: 10px;">
							<label>Language</label>
								<select name="language" id="language" onchange="document.forms['LoginForm'].state.value='login'; document.forms['LoginForm'].submit();" style="width:120px;" class="input_select">
								<option value="ar">Arabic</option>
								<option value="ar-utf">Arabic UTF-8</option>
								<option value="zh">Simplified Chinese</option>
								<option value="tc">Traditional Chinese</option>
								<option value="cs">Czech</option>
								<option value="da">Danish UTF-8</option>
								<option value="nl">Dutch</option>
								<option value="en" selected="selected">English</option>
								<option value="en-utf">English UTF-8</option>
								<option value="fr">French</option>
								<option value="de">German</option>
								<option value="fi">Finnish</option>
								<option value="he">Hebrew</option>
								<option value="hu">Hungarian</option>
								<option value="hu-utf">Hungarian UTF-8</option>
								<option value="it">Italian</option>
								<option value="ja">Japanese</option>
								<option value="pl">Polish</option>
								<option value="pt">Portugese</option>
								<option value="ru">Russian</option>
								<option value="es">Spanish</option>
								<option value="sv">Swedish</option>
								<option value="tr">Turkish</option>
								<option value="ua">Ukrainian</option>
								<option value="vi">Vietnamese</option>
								</select>
										</div>
										<div style="margin-top: 10px;">
											<label>Skin</label>
								<select name="skin" id="skin" style="width:120px;" class="input_select">
								<option value="shinra" selected="selected">Shinra</option>
								<option value="iphone">iPhone</option>
								</select>
										</div>
										<div style="margin-top: 10px;">
											<label>FTP mode</label>
								<select name="ftpmode" id="ftpmode" style="width:120px;" class="input_select">
								<option value="binary" selected="selected">Binary</option>
								<option value="automatic">Automatic</option>
								</select>
						</div>

@if (!$ftp_path_ok)
	<p style="color:red"><b>FTP PATH</b> problem detected. Please set <a href="{{ url() }}/edit_transaction/{{ $id }}">FTP_DIR</a> first. If FTP_DIR is already set and problem still exists go to web2ftp and try find and change <code>".htaccess"</code> filename  to <code>".temp_htaccess"</code> and try again. Don't forget to restore original name (<code>.htaccess</code>) later !</p>
@endif

						
						<input class="btn btn-warning" type="submit" id="LoginButton2" name="Login" value="Login via web2ftp" alt="Login">
					</fieldset>
					<input type="hidden" name="state" value="browse">
					<input type="hidden" name="state2" value="main">
				</form>

			</div>
		</div>



		<div class="panel panel-default">
			<div class="panel-heading"><b>Details</b>
			</div>
			<div class="panel-body">
				<p class="bg-success" style="padding:5px">
					<b>{{ $pname }}</b><br>
 					{{ $zip_file }}
				</p>

				<p class="bg-info" style="padding:5px">
					<b>Admin</b><br>
					{{ $admin_path }}
				</p>
			</div>
		</div>

	</div>





	<div class="col-md-6 text-left">

		<div class="panel panel-default">
			<div class="panel-heading">
				Product: <b>{{ $pname }}</b> Merchant: <b>{{ $user_firstname }} {{ $user_lastname }} </b>
			</div>

			<table class="table table-hover table-condensed table-bordered">
			@foreach($transactionsBelong as $transactionBelong)
			<tr class="{{$transactionBelong->class}}">
			  <td @if ($transactionBelong->id == $id)
				style = "font-size:20px; font-weight:bold;"
				@endif
				>
				{{$transactionBelong->id}}
			  </td>

			  <td>
			  	<a href="{{ url('manualMode/')}}/{{$transactionBelong->id}}"
			  	   class="btn btn-warning btn-xs"
			  	   role="button">{{$transactionBelong->pname}}
			  	</a> - <i>{{$transactionBelong->status}}</i>
			  	- <a class="btn btn-default btn-xs" href="{{ url('show_logs/')}}/{{$transactionBelong->id}}">Show logs</a>
			  	<a class="btn btn-default btn-xs" href="{{ url('edit_transaction/')}}/{{$transactionBelong->id}}">Edit data</a>
			  	</br>
			  	{{$transactionBelong->zip_file}}
			  </td>
			</tr>
			@endforeach
			</table>


			<div class="panel-body">

				<p><b>Steps:</b></p>
				
				<div class="checkbox">
				  <label>
				    <input type="checkbox" value="" onclick="javascript:doManualModeAction(this, 'upload_downloader');">
				   	[✓] Put <i>"downloader.php"</i> file to FTP server.
					<p class="manualModeMessage"></p>
				  </label>
				</div>

				<div class="checkbox">
				  <label>
				    <input type="checkbox" value="" onclick="javascript:doManualModeAction(this, 'upload_product');">
				   	[✓] Put <i>"{{ $pname }}"</i> zip file to FTP server.
					<p class="manualModeMessage"></p>
				  </label>
				</div>

				<div class="checkbox">
				  <label>
				    <input type="checkbox" value="" onclick="javascript:doManualModeAction(this, 'unzip_product');">
				   	[✓] Let <i>"downloader.php"</i> unpack "<i>{{ $pname }}</i> on remote server"
					<p class="manualModeMessage"></p>
				  </label>
				</div>

				<div class="checkbox">
				  <label>
				    <input type="checkbox" value="" onclick="javascript:doManualModeAction(this, 'upload_admin');">
				   	[✓] Put <i>"SSM-admin.zip"</i> file to FTP server.
					<p class="manualModeMessage"></p>
				  </label>
				</div>

				<div class="checkbox">
				  <label>
				    <input type="checkbox" value="" onclick="javascript:doManualModeAction(this, 'unzip_admin');">
				   	[✓] Let <i>"downloader.php"</i> unpack <i>SSM-admin.zip</i> on remote server
					<p class="manualModeMessage"></p>
				  </label>
				</div>

				<div class="checkbox">
				  <label>
					<!-- done done done done done done done done done done done done done done done done done done -->
				    <input type="checkbox" value="" onclick="javascript:doManualModeAction(this, 'change_user_file');">
				   	[✓] Let <i>"downloader.php"</i> to configure "admin/user.php" on remote server. 
				   	<p class="manualModeMessage"></p>
				  </label>
				</div>

				<div class="checkbox">
				  <label>
				    <input type="checkbox" value=""  onclick="javascript:doManualModeAction(this, 'remove_downloader');">
				   	[✓] Remove <i>"downloader.php"</i> from FTP server
				   	<p class="manualModeMessage"></p>
				  </label>
				</div>

				<div class="checkbox">
				  <label>
				    <input type="checkbox" value=""  onclick="javascript:change_status(this, {{ $id }});">
				   	[✓] Change status
				   	<p class="manualModeMessage"></p>
				  </label>
				</div>

				<div class="checkbox">
				  <label>
				    <input type="checkbox" value=""  onclick="javascript:showAskAndSendEmail(this, {{ $id }} );">
				   	[✓] Send email to customer
				   	<p class="manualModeMessage"></p>
				  </label>
				</div>
			</div>
		</div>

	</div>

</div>

<script type="text/javascript">
 
$(document).ready(function() {
	$('#LoginForm2 input[name=ftpserver]'). val('{{ $ftpserver }}');
	$('#LoginForm2 input[name=username]').  val('{{ $username }}');
	$('#LoginForm2 input[name=password]').  val('{{ $password }}');
	$('#LoginForm2 input[name=directory]'). val('{{ $directory }}');
});


</script>



@endsection
