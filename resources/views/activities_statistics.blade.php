@extends('app')

@section('content')

<div class="container-fluid">
	<div class="row">
	
		<div class="col-sm-12 col-sm-offset-0">

			<div class="panel panel-default">

				<div class="panel-heading" style="position:relative">
					<a href="{{ url() }}/activities/global_logs" class="btn btn-primary" role="button">GLOBAL LOGS</a>
					<a href="{{ url() }}/activities/install" class="btn btn-primary" role="button">INSTALL ACTIVITIES</a>
					<a href="{{ url() }}/activities/user_logs" class="btn btn-primary" role="button">USER LOGS</a>
					<a href="{{ url() }}/activities/statistics" class="btn btn-success" role="button">STATISTICS</a>
					<a href="{{ url() }}/activities/free_installations" class="btn btn-primary" role="button">FREE INSTALLATIONS</a>
				</div>

<div class="row">


	<div class="col-sm-5 text-left"> 
		<div class="panel-body">
			<p class="bg-success padding container-fluid">How many form submits in last 30 days</p>
			<div class="table-responsive">
			  <table class="table table-condensed">
				<tr>
					<th>No</th>
					<th>Date Ordered</th>
					<th>Name Surname</th>
					<th>Products</th>
				</tr>
				@foreach($new_customers_last_30_days as $index => $item)
				<tr>
					<td><b>{{ $index+1 }}.</b></td>
					<td><a href="{{ url() }}/home?date_filter={{ str_limit($item->date_ordered,10, '') }}">{{ str_limit($item->date_ordered,10, '') }}</a>{{ substr($item->date_ordered, 10,9) }}</td>
					<td><a href="{{ url() }}/home?text_search={{ $item->user_email }}">{{ $item->user_firstname }} {{ $item->user_lastname }}</td>
					<td>{{ $item->amount_of_products }}</td>
				</tr>
				@endforeach
			  </table>
			</div>
		</div>
	</div>



	<div class="col-sm-5 text-left"> 
		<div class="panel-body">
			<p class="bg-success padding container-fluid">How many form submits in this month</p>
			<div class="table-responsive">
			  <table class="table table-condensed">
				<tr>
					<th>No</th>
					<th>Date Ordered</th>
					<th>Name Surname</th>
					<th>Products</th>
				</tr>
				@foreach($new_customers_this_month as $index => $item)
				<tr>
					<td><b>{{ $index+1 }}.</b></td>
					<td><a href="{{ url() }}/home?date_filter={{ str_limit($item->date_ordered,10, '') }}">{{ str_limit($item->date_ordered,10, '') }}</a>{{ substr($item->date_ordered, 10,9) }}</td>
					<td><a href="{{ url() }}/home?text_search={{ $item->user_email }}">{{ $item->user_firstname }} {{ $item->user_lastname }}</td>
					<td>{{ $item->amount_of_products }}</td>
				</tr>
				@endforeach
			  </table>
			</div>
		</div>
	</div>


	<div class="col-sm-2 text-left"> 
		<div class="panel-body">
			<p class="bg-success padding container-fluid">How many form submits in past</p>
			<div class="table-responsive">
			  <table class="table table-condensed">
				@foreach($new_customers_past_months as $item)
				<tr>
					<td>{{ $item->date_ordered }}</td>
					<td>{{ $item->count }}</td>
				</tr>
				@endforeach
			  </table>
			</div>
		</div>
	</div>


</div>

			</div>
		</div>
	</div>
</div>


 

@endsection
