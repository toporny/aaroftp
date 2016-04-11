@extends('app')

@section('content')

<div class="container-fluid">
	<div class="row">
	
		<div class="col-sm-12 col-sm-offset-0">

			<div class="panel panel-default">

				<div class="panel-heading" style="position:relative">
					<span style="padding-right:10px"><?php echo $transactions->render(); ?> </span>
					<a href="{{ url() }}/activities/global_logs" class="btn btn-primary" role="button">GLOBAL LOGS</a>
					<a href="{{ url() }}/activities/install" class="btn btn-success" role="button">INSTALL ACTIVITIES</a>
					<a href="{{ url() }}/activities/user_logs" class="btn btn-primary" role="button">USER LOGS</a>
					<a href="{{ url() }}/activities/statistics" class="btn btn-primary" role="button">STATISTICS</a>
					<a href="{{ url() }}/activities/free_installations" class="btn btn-primary" role="button">FREE INSTALLATIONS</a>
				</div>

				<div class="panel-body">
					<div class="table-responsive">
					  <table class="table table-condensed">
						<tr>
							<th>system user</th>
							<th>trans id</th>
							<th>status</th>
							<th>user email</th>
							<th>pname</th>
							<th>date ordered</th>
							<th>date installed</th>
							<th>days delay</th>
						</tr>

						@foreach($transactions as $item)
						<tr>
							<td>{{ $item->user_name }}</td>
							<td><a title="show logs" href="{{ url() }}/manualMode/{{ $item->transaction_id }}">{{ $item->transaction_id }}</a></td>
							<td><a title="show logs" href="{{ url() }}/show_logs/{{ $item->transaction_id }}">{{ $item->status }}</a></td>
							<td><a title="show all transactions with this email" href="{{ url() }}/home?text_search={{ $item->user_email }}">{{ $item->user_email }}</a></td>
							<td>{{ $item->pname }}</td>
							<td><a title="show all transactions with this date" href="{{ url() }}/home?date_filter={{ str_limit($item->date_ordered,10, '') }}">{{ str_limit($item->date_ordered,16,'') }}</a></td>
							<td>{{ str_limit($item->date_installed, 16, '') }}</td>
							<td>{{ $item->days_delay }}</td>
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
