@extends('app')

@section('content')

<div class="container-fluid">
	<div class="row">
	
		<div class="col-sm-12 col-sm-offset-0">

			<div class="panel panel-default">

				<div class="panel-heading" style="position:relative">
					<span style="padding-right:10px"><?php echo $transactions->render(); ?> </span>
					<a href="{{ url() }}/activities/global_logs" class="btn btn-success" role="button">GLOBAL LOGS</a>
					<a href="{{ url() }}/activities/install" class="btn btn-primary" role="button">INSTALL ACTIVITIES</a>
					<a href="{{ url() }}/activities/user_logs" class="btn btn-primary" role="button">USER LOGS</a>
					<a href="{{ url() }}/activities/statistics" class="btn btn-primary" role="button">STATISTICS</a>
					<a href="{{ url() }}/activities/free_installations" class="btn btn-primary" role="button">FREE INSTALLATIONS</a>
				</div>

				<div class="panel-body">
					<div class="table-responsive">
					  <table class="table table-condensed">
						<tr>
							<th>timestamp</th>
							<th>status</th>
							<th>msg</th>
						</tr>

						@foreach($transactions as $item)
						<tr>
							<td><nobr>{{ str_limit($item->timestamp, 16, '') }}</nobr></td>
							<td>{{ $item->status }}</td>
							<td>{{ str_limit($item->msg, 2048, '..') }}</td>
						</tr>
						@endforeach

					  </table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


 

@endsection
