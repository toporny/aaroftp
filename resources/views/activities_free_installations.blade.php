@extends('app')

@section('content')

<div class="container-fluid">
	<div class="row">
	
		<div class="col-sm-12 col-sm-offset-0">

			<div class="panel panel-default">

				<div class="panel-heading" style="position:relative">
					<span style="padding-right:10px"><?php echo $free_installations->render(); ?> </span>
					<a href="{{ url() }}/activities/global_logs" class="btn btn-primary" role="button">GLOBAL LOGS</a>
					<a href="{{ url() }}/activities/install" class="btn btn-primary" role="button">INSTALL ACTIVITIES</a>
					<a href="{{ url() }}/activities/user_logs" class="btn btn-primary" role="button">USER LOGS</a>
					<a href="{{ url() }}/activities/statistics" class="btn btn-primary" role="button">STATISTICS</a>
					<a href="{{ url() }}/activities/free_installations" class="btn btn-success" role="button">FREE INSTALLATIONS</a>
				</div>

				<div class="panel-body">
					<div class="table-responsive">
					  <table class="table table-condensed">
						<tr>
							<th class="col-md-2">Date ordered</th>
							<th class="col-md-2">Name</th>
							<th class="col-md-2">Email</th>
							<th>Product</th>
						</tr>

						@foreach($free_installations as $item)
						<tr>
							<td class="col-md-2"><nobr>{{ str_limit($item->date_ordered, 16, '') }}</nobr></td>
							<td class="col-md-2"><a title="show all transactions with this name and surname" href="{{ url() }}/home?text_search={{ $item->user_firstname }}%20{{ $item->user_lastname }}">{{ $item->user_firstname }} {{ $item->user_lastname }}</a></td>
							<td class="col-md-2"><a title="show all transactions with this email" href="{{ url() }}/home?text_search={{ $item->user_email }}">{{ $item->user_email }}</a></td>
							<td>{{ $item->pname }}</td>
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
