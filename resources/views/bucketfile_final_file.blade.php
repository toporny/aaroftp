@extends('app')

@section('content')

<div class="container-fluid">
	<div class="row">
	
		<div class="col-sm-12 col-sm-offset-0">

			<div class="panel panel-default">

				<div class="panel-heading" style="position:relative">
@if ($auth_user == 'admin')
					<a href="{{ url() }}/bucketfile" class="btn btn-primary" role="button">TEMPLATE & PROTECTED FILE</a>
					<a href="{{ url() }}/bucketfile/generated_domains" class="btn btn-primary" role="button">GENERATED DOMAINS</a>
@endif					
					<a href="{{ url() }}/bucketfile/final_file" class="btn btn-success" role="button">FINAL FILE</a>
				</div>
				<div class="panel-body">

						<b>This is final bucket file.</b> Template + protected domains + generated domains

@if ($also_waiting_status == 0)
						where status=<b>'installed'</b>. Click <a href="{{ url() }}/bucketfile/final_file?waiting_status=1" class="btn btn-warning btn-xs" role="button">HERE</a> to add also 'waiting' statuses.  <u>Note:</u> Only domains from purchased (not free) products are visible here.
@else
						with status=<b>'installed'</b> or <b>'waiting'</b>.  Click <a href="{{ url() }}/bucketfile/final_file?waiting_status=0" class="btn btn-warning btn-xs" role="button">HERE</a> to see only 'installed' statuses.
						<p>Click <button onclick="updateS3bucket();" class="btn btn-danger btn-xs">HERE</button> to send this generated policy files 
						<b>/ @foreach($final_files as $index => $item)	{{ env('AWS_BUCKET'.($index) ) }} / @endforeach </b> to to Amazon S3</br>
						Last Amazon S3 bucket policy udated time: <b>{{ $last_s3_bucket_policy_udated_time }}</b> <u>Note:</u> Only domains from purchased (not free) products are visible here.</p>
@endif
					</p>

					<form class="form-horizontal" role="form" method="POST" action="{{ url('/save_transaction') }}">

						<input type="hidden" name="_token" value="{{ csrf_token() }}">

						<div class="form-group">
							<div class="col-sm-6">

@foreach($final_files as $index => $item)
	<p class="btn-info container-fluid">BUCKET POLICY FILE ({{ env('AWS_BUCKET'.($index) ) }})</p>
	{!! Form::textarea('item', $item, array('id'=>'template_file', 'style'=>'height:300px', 'class'=>'form-control' )) !!}
@endforeach

							</div>

							<div class="col-sm-6">
							<p class="btn-info container-fluid">List of all domains already defined in AMAZON S3 BUCKETS POLICY ({{ $last_s3_bucket_policy_udated_time }})</p>
								<pre>
{{ $last_known_bucket_policy }}
								</pre>
							</div>
		
						</div>

					</form>
				<p><a href="{{ $show_me_bucket_htacces_link}}">{{ $show_me_bucket_htacces_link }}</a> <-- this file is always accessible. (protected domain + generated domains with status='installed' or 'waiting') <p>

				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
function updateS3bucket() {
	bootbox.dialog({
	  message: 'Are you really want to update S3 bucket/s:<ul>@foreach($final_files as $index => $item)<li>{{ env('AWS_BUCKET'.($index) ) }}</li>@endforeach</ul>policy?',
	  title: "Question",
	  backdrop: "static",
      onEscape: function () {
        bootbox.hideAll();
      },	  
	  buttons: {
	    update: {
	      label: "YES",
	      className: "btn-success",
	      callback: function() {
			bootbox.hideAll();
			bootbox.alert("<h3>Wait...</h3> Don't close this window", function() {});
			$.ajax({
			  method: "GET",
			  url: '{{ url() }}/bucketfile/update_bucket_ajax',
			  contentType: "application/json"
			  //,data: pass_data
			})
			.done(function( data ) {
				bootbox.hideAll();
				var msg_parsed = JSON.parse(data);
				if (msg_parsed.status == 1) {
					bootbox.alert("<h3>Success</h3> Seems everything is OK.", function() {});	
				}
				else
				{
					console.log('Something went wrong. Bucket policy was NOT UPDATED.')
					bootbox.alert('<h3 class="btn-danger container-fluid">Problem</h3> Something went wrong. Please check is it everything all right with bucket policy on S3 amazon server.</br>'+msg_parsed.msg, function() {});
				}
			})
			.fail(function() {
				bootbox.hideAll();
				bootbox.alert('<h3 class="btn-danger container-fluid">Problem</h3> Something went wrong. Check the global logs.', function() {});
			})
			.always(function(data) {
			});
		  }
		},
		cancel: {
		   label: "Cancel",
		  className: "btn-info",
		},
	  }
	});
}	

</script>


@endsection
