@extends('app')

@section('content')

<div class="container-fluid">
	<div class="row">
		<form class="form-horizontal" role="form" method="POST" action="{{ url('/save_transaction') }}">
	
			<div class="col-md-6">
				@if ($form['updated'])
				<p style="padding:10px" class="btn-success">Record Updated</p>
				@endif
				<div class="panel panel-default">

					<div class="panel-heading" style="position:relative">
						<span>Edit transaction record part 1</span>
					</div>
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

						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						<input type="hidden" name="id" value="{{ $form['id'] }}">

						<div class="form-group">
							<label class="col-md-4 control-label">user_firstname</label>
							<div class="col-md-8">
								{!! Form::text('user_firstname', $form['user_firstname'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">user_lastname</label>
							<div class="col-md-8">
								{!! Form::text('user_lastname', $form['user_lastname'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">user_email</label>
							<div class="col-md-8">
								{!! Form::text('user_email', $form['user_email'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">customer_support_email</label>
							<div class="col-md-8">
								{!! Form::text('customer_support_email', $form['customer_support_email'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">paypal_email</label>
							<div class="col-md-8">
								{!! Form::text('paypal_email', $form['paypal_email'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">paypal_api_username</label>
							<div class="col-md-8">
								{!! Form::text('paypal_api_username', $form['paypal_api_username'], array('class'=>'form-control', 'maxlength'=>128)) !!}
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">paypal_api_password</label>
							<div class="col-md-8">
								{!! Form::text('paypal_api_password', $form['paypal_api_password'], array('class'=>'form-control', 'maxlength'=>128)) !!}
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">paypal_api_signature</label>
							<div class="col-md-8">
								{!! Form::text('paypal_api_signature', $form['paypal_api_signature'], array('class'=>'form-control', 'maxlength'=>128)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">clickbank_id</label>
							<div class="col-md-8">
								{!! Form::text('clickbank_id', $form['clickbank_id'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">jvzoo_id</label>
							<div class="col-md-8">
								{!! Form::text('jvzoo_id', $form['jvzoo_id'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>


						<div class="form-group">
							<label class="col-md-4 control-label">aweber_username</label>
							<div class="col-md-8">
								{!! Form::text('aweber_username', $form['aweber_username'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>


						<div class="form-group">
							<label class="col-md-4 control-label">aweber_password</label>
							<div class="col-md-8">
								{!! Form::text('aweber_password', $form['aweber_password'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">pname</label>
							<div class="col-md-8">
								{!! Form::text('pname', $form['pname'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">pcode</label>
							<div class="col-md-8">
								{!! Form::text('pcode', $form['pcode'], array('class'=>'form-control', 'maxlength'=>16)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">uicode</label>
							<div class="col-md-8">
								{!! Form::text('uicode', $form['uicode'], array('class'=>'form-control', 'maxlength'=>16)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">version</label>
							<div class="col-md-8">
								{!! Form::text('version', $form['version'], array('class'=>'form-control', 'maxlength'=>16)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">txt_file_url</label>
							<div class="col-md-8">
								{!! Form::text('txt_file_url', $form['txt_file_url'], array('class'=>'form-control', 'maxlength'=>255)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">ftp_host</label>
							<div class="col-md-8">
								{!! Form::text('ftp_host', $form['ftp_host'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">ftp_username</label>
							<div class="col-md-8">
								{!! Form::text('ftp_username', $form['ftp_username'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">ftp_password</label>
							<div class="col-md-8">
								{!! Form::text('ftp_password', $form['ftp_password'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">ftp_dir</label>
							<div class="col-md-8">
								{!! Form::text('ftp_dir', $form['ftp_dir'], array('class'=>'form-control', 'maxlength'=>128)) !!}
							</div>
						</div>


						<div class="form-group">
							<label class="col-md-4 control-label">website</label>
							<div class="col-md-8">
								{!! Form::text('website', $form['website'], array('class'=>'form-control', 'maxlength'=>256)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">transaction_id</label>
							<div class="col-md-8">
								{!! Form::text('transaction_id', $form['transaction_id'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>


					</div>
				</div>
			</div>



			<div class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-heading" style="position:relative">
						<span>Edit transaction record part 2</span>
					</div>
					<div class="panel-body">

						<div class="form-group">
							<label class="col-md-4 control-label">awlist1</label>
							<div class="col-md-8">
								{!! Form::text('awlist1', $form['awlist1'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">awlist2</label>
							<div class="col-md-8">
								{!! Form::text('awlist2', $form['awlist2'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">grlist1</label>
							<div class="col-md-8">
								{!! Form::text('grlist1', $form['grlist1'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">grlist2</label>
							<div class="col-md-8">
								{!! Form::text('grlist2', $form['grlist2'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">list1title</label>
							<div class="col-md-8">
								{!! Form::text('list1title', $form['list1title'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">list1info</label>
							<div class="col-md-8">
								{!! Form::text('list1info', $form['list1info'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">list2title</label>
							<div class="col-md-8">
								{!! Form::text('list2title', $form['list2title'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">list2info</label>
							<div class="col-md-8">
								{!! Form::text('list2info', $form['list2info'], array('class'=>'form-control', 'maxlength'=>64)) !!}
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">comments</label>
							<div class="col-md-8">
								{!! Form::text('comments', $form['comments'], array('class'=>'form-control', 'maxlength'=>1024)) !!}
							</div>
						</div>
				
						<div class="form-group">
							<label class="col-md-4 control-label">statuses / features</label>
							<div class="col-md-8">
	@foreach($checkboxes as $key => $item)
								<nobr><span style="margin-right:20px">{!! Form::checkbox('statuses_features['.$key.']', $item, $item) !!} {{ $key }}</span></nobr>
	@endforeach
							</div>
						</div>


						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">SAVE CHANGES</button>
								<button type="button" onclick="window.location = '{{url()}}/home';" class="btn btn-warning">BACK</button>
							</div>
						</div>


						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
	<b>email</b> - user email notification</br>
	<b>paypal</b> - user uses: Paypal API Username, Password, Signature, ID</br>
	<b>autoinstall_started</b> - true if system tried to autoinstall</br>
	<b>autoinstall</b> - autoinstall mode. System tries to autoinstall if:
	<ul>
	<li>ftp_path_ok=true</li>
	<li>payment_confirmed=true</li>
	<li>autoinstall_error=false</li>
	<li>autoinstall_done=false</li>
	</ul>

	<b>ftp_path_ok</b> - system knows ftp_dir is set properly.</br>
	<b>payment_confirmed</b> - true if payment is confirmed.</br>
	<b>autoinstall_pizfile_uploaded</b> - autoinstalation status.</br>
	<b>autoinstall_pizfile_unpacked</b> - autoinstalation status.</br>
	<b>autoinstall_error</b> - autoinstalation status.</br>
	<b>autoinstall_done</b> - autoinstalation status.</br>
							</div>
						</div>


					</div>
				</div>
			</div>


		</form>








	</div>
</div>

@endsection
