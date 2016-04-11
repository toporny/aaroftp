@extends('app')

@section('content')

<div class="container-fluid">
	<div class="row">
	
		<div class="col-sm-12 col-sm-offset-0">

			<div class="panel panel-default">

				<div class="panel-body">
 
<div class="alert alert-success" role="alert">
	<b>Autonomus mode</b> - If it's ON then system installs all paid transactions with 24h delay. 
</div>


Autonomous mode is:


<form class="form-horizontal" role="form" method="POST" action="{{ url('/autonomous_mode/edit') }}">

	<input type="hidden" name="_token" value="{{ csrf_token() }}">

	<div class="radio">
	  <label>
	    <input type="radio" name="AutonomousRadios" id="optionsRadios1" value="on" checked>
	    ON
	  </label>
	</div>
	<div class="radio">
	  <label>
	    <input type="radio" name="AutonomousRadios" id="optionsRadios2" value="off">
	    OFF
	  </label>
	</div>
	<button style="margin-top:10px" type="submit" class="btn btn-default">Submit</button>
</form>



<p style="background:red; color:white ;  padding:5px; margin-top:10px;">
	This is not yet ready!
	Firstly I need to figure out how to catch the moment when customer pays money.  	
</p>




				</div>

			</div>
		</div>
	</div>
</div>

@endsection
