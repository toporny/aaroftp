<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>AARON FTP</title>

	<link href="{{ asset('/css/app.css') }}" rel="stylesheet">
	<link href="{{ asset('/css/loader.css') }}" rel="stylesheet">

	<!-- Fonts -->
<!-- 	<link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>
 -->
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>

<style type="text/css">
.form-group { margin-bottom:5px;}
</style>


<body>

<div> <!--  class="container-fluid" -->
	<div class="row">
		<div class="col-sm-12">
			<div class="panel panel-default">
 

				<h2 class="text-center" id="elementid">Please fill in the form below</h2>
 
				<div class="panel-body">
					@if (count($errors) > 0)
						<div class="alert alert-danger">
							<strong>Whoops!</strong> There were some problems with your input.<br><br>
							<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif

					<form data-parsley-validate id="userform" class="form-horizontal" role="form" method="POST" action="{{ url('/') }}">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						<input type="hidden" name="uicode" value="{{ $uicode }}">
						<input type="hidden" id="statuses_features_id" name="statuses_features" value="{{ $statuses_features }}">
						<input type="hidden" name="http_referer" value="{{ $http_referer }}">

						<div class="form-group first_name_group">
								<label class="col-sm-6 control-label">First Name: <span id="first_name_help" class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
							<div class="col-sm-6">
								<input required id="id_first_name" type="text" class="form-control" name="first_name" value="{{ old('first_name') }}">
							</div>
						</div>

						<div class="form-group last_name_group">
							<label class="col-sm-6 control-label">Last Name: <span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
							<div class="col-sm-6">
								<input required id="id_last_name" type="text" class="form-control" name="last_name" value="{{ old('last_name') }}">
							</div>
						</div>

						<div class="form-group contact_email_group">
							<label class="col-sm-6 control-label">Contact Email (so we can notify you): <span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
							<div class="col-sm-6">
								<input required id="id_contact_email" type="email" class="form-control" name="contact_email" value="{{ old('contact_email') }}">
							</div>
						</div>

				<!--
						<div class="form-group transaction_id_group">
							<label class="col-sm-6 control-label">Transaction ID <span class="s">*</span></label>
							<div class="col-sm-6">
								<input type="text" class="form-control" name="transaction_id" value="{{ old('transaction_id') }}">
							</div>
						</div>

						<div class="form-group pen_name_group">
							<label class="col-sm-6 control-label">Pen Name (optional) <span class="s">*</span></label>
							<div class="col-sm-6">
								<input type="text" class="form-control" name="pen_name" value="{{ old('pen_name') }}">
							</div>
						</div>
				-->
						<div class="form-group customer_support_email_group">
							<label class="col-sm-6 control-label">Customer Support Email / URL: <span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
							<div class="col-sm-6">
								<input required id="id_customer_support_email" type="text" class="form-control" name="customer_support_email" value="{{ old('customer_support_email') }}">
							</div>
						</div>
				<!--
						<div class="form-group customer_support_url_group">
							<label class="col-sm-6 control-label">Customer Support URL<br>(optional) <span class="s">*</span></label>
							<div class="col-sm-6">
								<input type="text" class="form-control" name="customer_support_url" value="{{ old('customer_support_url') }}">
							</div>
						</div>
				-->

						<div class="form-group paypal_email_group">
							<label class="col-sm-6 control-label">PayPal Email: <span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
							<div class="col-sm-6">
								<input required id="id_paypal_email" type="email" class="form-control" name="paypal_email" value="{{ old('paypal_email') }}">
							</div>
						</div>

				@if ($paypal_mode)
					<div class="form-group paypal_api_username_group">
						<label class="col-sm-6 control-label" style="line-height:17px; padding-top:0px">Paypal API Username:<span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
						<div class="col-sm-6">
							<input required id="id_paypal_api_username" type="text" class="form-control" name="paypal_api_username" value="{{ old('paypal_api_username') }}">
						</div>
					</div>
					<div class="form-group paypal_api_password_group">
						<label class="col-sm-6 control-label" style="line-height:17px; padding-top:0px">Paypal API Password:<span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
						<div class="col-sm-6">
							<input required id="id_paypal_api_password" type="text" class="form-control" name="paypal_api_password" value="{{ old('paypal_api_password') }}">
						</div>
					</div>
					<div class="form-group paypal_api_signature_group">
						<label class="col-sm-6 control-label" style="line-height:17px; padding-top:0px">Paypal API Signature:<span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
						<div class="col-sm-6">
							<input required id="id_paypal_api_signature" type="text" class="form-control" name="paypal_api_signature" value="{{ old('paypal_api_signature') }}">
						</div>
					</div>
				<!-- 	<div class="form-group paypal_api_id_group">
						<label class="col-sm-6 control-label" style="line-height:17px; padding-top:0px">Paypal API ID:<span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
						<div class="col-sm-6">
							<input required id="id_paypal_api_id" type="text" class="form-control" name="paypal_api_id" value="{{ old('paypal_api_id') }}">
						</div>
					</div> -->
				@endif


				@if ($aweber_mode)
						<div class="form-group aweber_username_group">
							<label class="col-sm-6 control-label">Aweber Username: <span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
							<div class="col-sm-6">
								<input required id="id_aweber_username" type="text" class="form-control" name="aweber_username" value="{{ old('aweber_username') }}">
							</div>
						</div>
						<div class="form-group aweber_password_group">
							<label class="col-sm-6 control-label">Aweber Password: <span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
							<div class="col-sm-6">
								<input required id="id_aweber_password" type="text" class="form-control" name="aweber_password" value="{{ old('aweber_password') }}">
							</div>
						</div>
				@endif


						<div class="form-group clickbank_id_group">
							<label class="col-sm-6 control-label">ClickBank ID: <span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
							<div class="col-sm-6">
								<input required id="id_clickbank_id" type="text" class="form-control" name="clickbank_id" value="{{ old('clickbank_id') }}">
							</div>
						</div>

						<div class="form-group jvzoo_id_group">
							<label class="col-sm-6 control-label">JVZoo ID: <span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
							<div class="col-sm-6">
								<input required id="id_jvzoo_id" type="text" data-parsley-type="integer" class="form-control" name="jvzoo_id" value="{{ old('jvzoo_id') }}">
							</div>
						</div>

						<div class="form-group ftp_host_group">
							<label class="col-sm-6 control-label">FTP Server: <span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
							<div class="col-sm-6">
								<input required placeholder="ftp.domain.com" maxlength="64" id="id_ftp_host" type="text" class="form-control" name="ftp_host" value="{{ old('ftp_host') }}">
							</div>
						</div>

						<div class="form-group ftp_username_group">
							<label class="col-sm-6 control-label">FTP Username: <span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
							<div class="col-sm-6">
								<input required maxlength="64" id="id_ftp_username" type="text" class="form-control" name="ftp_username" value="{{ old('ftp_username') }}">
							</div>
						</div>

						<div class="form-group ftp_password_group">
							<label class="col-sm-6 control-label">FTP Password: <span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
							<div class="col-sm-6">
								<input required maxlength="64" id="id_ftp_password" type="text" class="form-control" name="ftp_password" value="{{ old('ftp_password') }}">
							</div>
						</div>

						<div class="form-group ftp_dir_group" id="ftp_path_id" style="display:none">
							<label class="col-sm-6 control-label">FTP Directory Path To Website: <span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
							<div class="col-sm-6">
								<input placeholder="/" maxlength="128" id="id_ftp_dir" type="text" class="form-control" name="ftp_dir" value="{{ old('ftp_dir') }}">
							</div>
						</div>

						<div class="form-group url_customers_website_group">
							<label class="col-sm-6 control-label">URL to website (include http://www....) <span class="round_question_mark glyphicon glyphicon-question-sign"></span></label>
							<div class="col-sm-6">
								<input required placeholder="http://www.domain.com" maxlength="128" id="id_url_customers_website" type="text" class="form-control" name="url_customers_website" value="{{ old('url_customers_website') }}">
							</div>
						</div>

						<div class="form-group" id="error_bar" style="display:none">
							<div class="col">
								<div id="error_message_content" class="alert alert-danger" role="alert">
								   undefined error
								</div>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-6 control-label">
								Please confirm that your details are correct by clicking the test button first.<br>
							</label>
							<div class="col-sm-6">
								<button id="testFTP" type="button" class="btn btn-primary btn-lg">TEST FTP</button>
								<button disabled="disabled" id="submitButton" type="submit" class="btn btn-success btn-lg">SUBMIT</button>
								<span id="spinner" style = "float:right; display:none" class="spinner">
								    Loading...
								</span>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

<!-- Modal Start here-->
<div class="modal fade bs-example-modal-sm" id="myPleaseWait" tabindex="-1"
    role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    Please Wait...
                 </h4>
            </div>
            <div class="modal-body">
                <div class="progress">
                    <div class="progress-bar progress-bar-info
                    progress-bar-striped active"
                    style="width: 100%">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal ends Here -->




</div>

	<!-- Scripts -->
	<script src="{{ asset('/js/vendor.js') }}"></script>


<script type="text/javascript">



$('#testFTP').click( function() {
	if (!$('#userform').parsley().validate()) return;

	console.log('test FTP...');
	$('#spinner').show();
	$.ajax({
	  method: "GET",
	  url: "/testftp",
	  contentType: "application/json",
	  async: true,
	  data: {	server_ip:      	   $('#id_ftp_host').val(),
	  			ftp_username:   	   $('#id_ftp_username').val(),
	  			ftp_password:   	   $('#id_ftp_password').val(),
	  			ftp_dir:        	   $('#id_ftp_dir').val(),
	  			url_customers_website: $('#id_url_customers_website').val(),
			}
	})
	.done(function( msg ) {

		console.log('force hide #error_bar');

		$('#error_bar').hide();
	
		console.log('now: JSON.parse(msg) ...');
		var msg_parsed = JSON.parse(msg);
		if (msg_parsed.status == 1) {

			console.log('JSON parsed OK!');
			console.log('let me fill #id_ftp_dir');
			$('#ftp_path_id').show(function() {
				$('#id_ftp_dir').val(msg_parsed.ftp_dir);
			});

			// make all fields readonly
			$('input').each(
			    function(index){  
			        if (($(this).attr('type') == 'text') || ($(this).attr('type') == 'email') ) {
			        	$(this).prop("readonly", true);
			        }
			    }
			);



			$('#statuses_features_id').attr('value', $('#statuses_features_id').val()+',ftp_path_ok');
			$('#submitButton').removeAttr('disabled');
			bootbox.dialog({
			  message: "Seems all FTP data are correct.",
			  backdrop: true,
			  title: "Success",
			  buttons: {
				ok: {
					label: "NEXT",
					className: "btn-primary",
					callback: function() {
						$('#myPleaseWait').modal('show');
						$('#userform').submit();
					}
				},
				cancel: {
					label: "CANCEL",
					className: "btn-warning",
					callback: function() {
						$('#submitButton').attr('disabled', 'disabled');
						$('input').each(
							function(index){  
								if (($(this).attr('type') == 'text') || ($(this).attr('type') == 'email') ) {
									$(this).prop("readonly", false);
								}
							}
						);						
					}
				},

			  }
			});
		}  // end msg_parsed.status == -1

		else if (msg_parsed.status == -5) {  // very bad! -5 means wrong FTP path! 
			$('#ftp_path_id').show();

			//destroy parsley
			$('#userform').parsley().destroy(); // destroy form 
			//set required attribute on input to true
			$('#id_ftp_dir').attr('data-parsley-required', 'true');
			//reinitialize parsley
			$('#userform').parsley(); // initialize form once again
			$('#userform').parsley().validate();

			var msg = "<h3>Problem</h3>System can't recognize your <b>URL to website</b> or <b>FTP Directory Path to Website</b> </br>";
			msg += "It is ussually '/' or '/public_html' or '/yourdomain' ..</br>";
			msg += "Its should indicate to your Website. Please type <nobr>[FTP Directory Path to Website]</nobr> and <nobr>[URL to website]</nobr> carefully.</br>";
			bootbox.alert(msg, function() {
			});

		}
		else if (msg_parsed.status == -6) {  // very bad! -6 means ftp_path user typed is wrong
				bootbox.dialog({
				  message: "<h3>Problem with FTP</h3>"+ msg_parsed.msg,
 
				  buttons: {
				    correct_form: {
				      label: "CORRECT FORM",
				      className: "btn-primary ",
				      callback: function() {
				      	bootbox.hideAll();

				      }
				    },

				    continue_anyway: {
				      label: "SUBMIT FORM ANYWAY",
				      className: "btn-warning",
				      callback: function() {
				      	bootbox.hideAll();
						$('#testFTP').attr('disabled', 'disabled');
						$('#submitButton').removeAttr('disabled');
				      }
				    },
				  }
				});

			// bootbox.alert("<h3>Problem with FTP</h3>"+ msg_parsed.msg, function() {

			// });
		}

		else 
		{
			$('#submitButton').removeAttr('disabled');
			console.warn('msg_parsed.status = ',msg_parsed.status);
			bootbox.alert("<h3>Problem with FTP</h3>"+ msg_parsed.msg, function() {
			});
		}
		$('#spinner').hide();

	})
	.fail(function(data) {
		$('#spinner').hide();
		$('#error_bar').show();

		bootbox.alert("<h3>Problem with FTP</h3>"+ msg_parsed.msg, function() {

		});		
		console.log( "Problem with uploading downloader file on customer's server.",data );
	})
	.always(function(data) {

	});
 
});

@if (env('APP_DEBUG') == true )

		$('#id_first_name').  		        val('Marek');
		$('#id_last_name').  		        val('Szyszko');
		$('#id_contact_email').  		    val('toporny@gmail.com');
		$('#id_paypal_email').     	   	    val('toporny@gmail.com');
		$('#id_customer_support_email').    val('toporny@gmail.com');
		$('#id_clickbank_id').     	   	    val('34333');
		$('#id_jvzoo_id').     	            val('23443');
		$('#id_aweber_username').           val('aweber_test_username');
		$('#id_aweber_password').           val('aweber_test_password');

		// $('#id_ftp_host').     	   	        val('localhost');
		// $('#id_ftp_username'). 	 		    val('zenek');
		// $('#id_ftp_password').  		    val('zenek99');
		// // $('#id_ftp_dir').		            val('/');
		// $('#id_url_customers_website').     val('http://aarontestmac.com');

		$('#id_ftp_host').     	   	        val('alltic.home.pl');
		$('#id_ftp_username'). 	 		    val('aaron2test@aarondev.enbe.pl');
		$('#id_ftp_password').  		    val('aaron2test99');
		$('#id_ftp_dir').		            val('/');
		$('#id_url_customers_website').     val('http://aaron2test.enbe.pl');

@endif


$(document).ready(function() {

	var explanations = {
		'first_name_group'             : ['First Name', '<p>This is your first name that will be displayed on all your sales pages and broadcast emails. If you do not want to display your real name you can use a nickname or pen name instead.</p>'],
		'last_name_group'              : ['Last Name', '<p>This is your last name that will be displayed on all your sales pages. If you do not want to display your real surname you can use a nickname or pen name instead.</p>'],
		'contact_email_group'          : ['Contact Email', '<p>This is the email we will use to contact you after the install is complete. We do not use this email for marketing.</p>'],
		// 'transaction_id_group'      : ['title 4', '<p>description about transaction_id_group</p>'],
		// 'pen_name_group'            : ['title 5', '<p>description about pen_name_group</p>'],
		'customer_support_email_group' : ['Customer Support Email', '<p>Your customer support email or support URL will be displayed on the footer of every page in your mini-sites and on the thank you pages so customers know how to contact you. Remember, we\'ll provide you with all the access links to your products so you\'ll be able to answer the majority of your customer\'s questions.</p>'],
		'customer_support_url_group'   : ['Customer Support Url', '<p>Your customer support email or support URL will be displayed on the footer of every page in your mini-sites and on the thank you pages so customers know how to contact you. Remember, we\'ll provide you with all the access links to your products so you\'ll be able to answer the majority of your customer\'s questions.</p>'],
		'paypal_email_group'           : ['Paypal Email', '<p>This is the PayPal email address that you use to receive payments. This will be coded into every payment button in your system. To ensure that your customers are redirected to the correct page after payment, please turn on "Auto Complete" in your PayPal settings. To register for a PayPal account please go to http://www.paypal.com</p>'],
		'aweber_username'              : ['Aweber Username', '<p>This is the Aweber Username.'],
		'aweber_password'              : ['Aweber Username', '<p>This is the Aweber Password.'],
		'paypal_api_username_group'    : ['Paypal API Username', '<p>description about paypal_api_username_group</p>'], // ???? 
		'paypal_api_password_group'    : ['Paypal API Password', '<p>description about paypal_api_password_group</p>'], // ???? 
		'paypal_api_signature_group'   : ['Paypal API Signature', '<p>description about paypal_api_signature_group</p>'], // ???? 
		// 'paypal_api_id_group'          : ['Paypal API ID', '<p>description about paypal_api_id_group</p>'], // ???? 
		'clickbank_id_group'           : ['Clickbank ID', '<p>This is your alphanumeric nickname that you use to log into your ClickBank affiliate account. Your ClickBank ID is embedded into all your download pages to maximize your earnings. To register for a ClickBank account please go to http://www.clickbank.com</p>'],
		'jvzoo_id_group'               : ['Jvzoo ID', '<p>This is your JVZoo numeric affiliate ID. To get your ID, register for an account at http://www.jvzoo.com then once logged in, go to my account > my account and locate "Your Affililiate ID". The number is what you need which is usually 6 digits.</p>'],
		'ftp_host_group'               : ['FTP Server', '<p>Your FTP Server is your domain name with ftp in front. Example ftp.domain.com</p><p>If you haven\'t already please register with a reliable webhost that gives you full control of your domain names with the latest version of cPanel. We recommend you use <a href="http://www.aaronlikes.com/bluehost">Bluehost</a>.</p>'],
		'ftp_username_group'           : ['FTP Username', '<p>This is your FTP username you use to log into your server. Copy and paste in your username into this text field to ensure accuracy.</p>'],
		'ftp_password_group'           : ['FTP Password', '<p>This is the FTP password you created to log into your server. Copy and paste in your password into this text field to ensure accuracy.</p>'],
		'ftp_dir_group'                : ['FTP Directory Path To Website', '<p>The value you enter in \'FTP Directory Path To Website:\' will depend on where your FTP login details take us.</p><p>If your FTP login details take us to exactly the right folder on your server where your new/add-on domain is located then enter a single forward slash /</p><p>If however you are providing full access to your server, then enter /public_html/ followed by the folder name that gives us access to your domain.</p>'],
		'url_customers_website_group'  : ['URL to website', '<p>Enter your full website link (including http://www.) Our system will check to see that your website URL corresponds with your FTP server so that our team can get to work.</p>']
	}


	$('span.round_question_mark').tooltip({placement: 'top', title: 'Required. Click to more help.', animation: true});

	$("span.round_question_mark").click(function() {
		var tmp = $(this).parent().parent(); 
		if (tmp.hasClass("first_name_group")) showHelp(explanations, 'first_name_group');
		if (tmp.hasClass("last_name_group")) showHelp(explanations, 'last_name_group');
		if (tmp.hasClass("contact_email_group")) showHelp(explanations, 'contact_email_group');
		//if (tmp.hasClass("transaction_id_group")) showHelp(explanations, 'transaction_id_group');
		// if (tmp.hasClass("pen_name_group")) showHelp(explanations, 'pen_name_group');
		if (tmp.hasClass("customer_support_email_group")) showHelp(explanations, 'customer_support_email_group');
		if (tmp.hasClass("customer_support_url_group")) showHelp(explanations, 'customer_support_url_group');
		if (tmp.hasClass("paypal_email_group")) showHelp(explanations, 'paypal_email_group');
		if (tmp.hasClass("paypal_api_username_group")) showHelp(explanations, 'paypal_api_username_group');
		if (tmp.hasClass("paypal_api_password_group")) showHelp(explanations, 'paypal_api_password_group');
		if (tmp.hasClass("paypal_api_signature_group")) showHelp(explanations, 'paypal_api_signature_group');
		//if (tmp.hasClass("paypal_api_id_group")) showHelp(explanations, 'paypal_api_id_group');
		if (tmp.hasClass("clickbank_id_group")) showHelp(explanations, 'clickbank_id_group');
		if (tmp.hasClass("jvzoo_id_group")) showHelp(explanations, 'jvzoo_id_group');
		if (tmp.hasClass("ftp_host_group")) showHelp(explanations, 'ftp_host_group');
		if (tmp.hasClass("ftp_username_group")) showHelp(explanations, 'ftp_username_group');
		if (tmp.hasClass("ftp_password_group")) showHelp(explanations, 'ftp_password_group');
		if (tmp.hasClass("ftp_dir_group")) showHelp(explanations, 'ftp_dir_group');
		if (tmp.hasClass("url_customers_website_group")) showHelp(explanations, 'url_customers_website_group');
	});
});


function showHelp(explanations, index) {
	//explanations['first_name_group'][1]
	bootbox.dialog({
		message: explanations[index][1],
		title: explanations[index][0],
		onEscape: function() {
			//alert(123);
		},
		buttons: {
			installed: {
				label: "CLOSE",
				className: "btn-success",
			},
		}
	});
}


fillTheFormByMessage = function(event) {
	document.getElementById("id_first_name").value = event.data.id_first_name;
	document.getElementById("id_last_name").value = event.data.id_last_name;
	document.getElementById("id_contact_email").value = event.data.id_contact_email;
	document.getElementById("id_customer_support_email").value = event.data.id_customer_support_email;
	document.getElementById("id_paypal_email").value = event.data.id_paypal_email;
	document.getElementById("id_clickbank_id").value = event.data.id_clickbank_id;
	document.getElementById("id_jvzoo_id").value = event.data.id_jvzoo_id;
	document.getElementById("id_ftp_host").value = event.data.id_ftp_host;
	document.getElementById("id_ftp_username").value = event.data.id_ftp_username;
	document.getElementById("id_ftp_password").value = event.data.id_ftp_password;
	document.getElementById("id_url_customers_website").value = event.data.id_url_customers_website;
}

window.addEventListener('message', fillTheFormByMessage, false);


</script>

</body>
</html>
