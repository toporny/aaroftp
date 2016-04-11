@extends('app')

@section('content')

<div class="container-fluid">
	<div class="row">
	
		<div class="col-sm-12 col-sm-offset-0">

			<div class="panel panel-default">

				<div class="panel-heading" style="position:relative">
@if ($auth_user == 'admin')
					<a href="{{ url() }}/bucketfile" class="btn btn-primary" role="button">TEMPLATE & PROTECTED FILE</a>
					<a href="{{ url() }}/bucketfile/generated_domains" class="btn btn-success" role="button">GENERATED DOMAINS</a>
@endif					
					<a href="{{ url() }}/bucketfile/final_file" class="btn btn-primary" role="button">FINAL FILE</a>
				</div>

				<div class="panel-body">

					<div class="form-group">

						<div class="col-sm-10">
							<b>Note:</b> Only domains from purchased (not free) products are visible here.
							<h4>Those domains are generated from all transactions where <b>status</b> = 'installed'</h4>
<pre>
@foreach ($transaction_domains as $item)
	{!! $item->domain !!}
@endforeach
</pre>

						</div>
					</div>

 				</div>
			</div>
		</div>
	</div>
</div>

@endsection
