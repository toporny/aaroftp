@extends('app')

@section('content')

<script>
	function copyToClipboard(aha) {
		var $temp = $("<input>");
		$("body").append($temp);
		$temp.val(aha).select();
		document.execCommand("copy");
		$temp.remove();
	}
</script>

<div class="container-fluid">
	<div class="row">
	
		<div class="col-sm-12 col-sm-offset-0">
 
			<div class="panel panel-default">

				<div class="panel-heading" style="position:relative">
					<a href="{{ url() }}/email_templates/form_submitted" class="btn btn-primary" role="button">SUBMIT FORM NOTIFICATION</a>
					<a href="{{ url() }}/email_templates/product_installed" class="btn btn-success" role="button">PRODUCT INSTALLED CONFIRMATION</a>
				</div>

				<div class="panel-body">
					<div class="form-group">
@if ($auth_user == 'admin')					
						<div class="col-sm-6 success" style="font-family:monospace;">
							<p class="{{ $flash_message[0] }} container-fluid">PRODUCT INSTALLED CONFIRMATION TEMPLATE {{ $flash_message[1] }}</p>
							<form class="form-horizontal" role="form" method="POST" action="{{ url('/email_templates/update_instalation_confirmation') }}">
								<input type="hidden" name="_token" value="{{ csrf_token() }}">
								<div style="border: 1px solid #e5e5e5; padding: 10px; margin-bottom:10px">
									<b>Available variables (click to copy to clipboard):</b><br>
									<span onclick="copyToClipboard('[%first_name%]')"><nobr>[%first_name%],</nobr> </span>
									<span onclick="copyToClipboard('[%last_name%]')"><nobr>[%last_name%],</nobr> </span>
									<span onclick="copyToClipboard('[%user_admin_url%]')"><nobr>[%user_admin_url%],</nobr> </span>
									<span onclick="copyToClipboard('[%products_list%]')"><nobr>[%products_list%],</nobr> </span>
									<span onclick="copyToClipboard('[%letter_s%]')"><nobr>[%letter_s%],</nobr> </span>
									<span onclick="copyToClipboard('[%is_are%]')"><nobr>[%is_are%],</nobr> </span>
								</div>

								{!! Form::textarea('email_template', $email_template, array('style'=>'height:400px', 'id'=>'email_template', 'class'=>'form-control' )) !!}

								<div class="form-group panel">
									<div class="col-sm-6 col-sm-offset-4">

										<button type="submit" class="btn btn-primary">UPDATE</button>

										<a class="btn btn-warning" href="{{url()}}/home" role="button">BACK</a>
									</div>
								</div>
							</form>
						</div>
@endif

						<div class="col-sm-6">
						
							<pre> <b>Example 1 with only one product:</b> </pre>
							<div style="border: 1px solid #cccccc; border-radius: 4px; padding:5px" class="bg-warning">
								{!! $example_template1 !!}
							</div>
					
							<pre style="margin-top:20px"> <b>Example 2 with few products:</b> </pre>
							<div style="border: 1px solid #cccccc; border-radius: 4px; padding:5px" class="bg-warning">
								{!! $example_template2 !!}
							</div>

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection
