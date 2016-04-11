@extends('app')

@section('content')

@if ($modal != '')
  @include('statusmodal', $modal)
@endif					


<div class="container-fluid">
	<div class="row">
	
		<div class="col-sm-12 col-sm-offset-0">
 
			<div class="panel panel-default">

				<div class="panel-heading" style="position:relative">
@if ($auth_user == 'admin')
					<a href="{{ url() }}/bucketfile" class="btn btn-success" role="button">TEMPLATE & PROTECTED FILE</a>
					<a href="{{ url() }}/bucketfile/generated_domains" class="btn btn-primary" role="button">GENERATED DOMAINS</a>
@endif					
					<a href="{{ url() }}/bucketfile/final_file" class="btn btn-primary" role="button">FINAL FILE</a>
				</div>

				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="{{ url('/bucketfile/update') }}">


<!-- problem
 -->
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						<div class="form-group">
							<div class="col-sm-5">
								<p><b>Bucket template file.</b> At this moment system can use max {{ count($bucket_names) }} buckets:<br>
@foreach($bucket_names as $index => $item)<b>[{{ $index }}]</b> {{ env('AWS_BUCKET'.($index) ) }}<br>
@endforeach</p>
<pre>
{!! $template_file !!}
</pre>
							</div>

 
							<div class="col-sm-7">
								<pre><b>Protected domains and bucket connected. (bucketname can be changed only in database)</b>  <br>
<table>
@foreach ($protected_domains as $domain)
<tr><td>{{ $domain->domain }}</td><td>{{ env('AWS_BUCKET'.($domain->bucket_name_index)) }}</td></tr>
@endforeach
</table>

<input class="form-control" place_holder="type domain here" type="text" name="domain" style="width:400px; margin-right: 10px; float:left;"> <button type="submit" class="btn btn-primary" name="add_domain">ADD DOMAIN</button> <button type="submit" class="btn btn-primary" name="remove_domain">REMOVE DOMAIN</button></pre>

							</div>

						</div>

					</form>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection
