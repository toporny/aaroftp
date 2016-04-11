<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Aaro</title>

	<link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('/css/throbber.css') }}" type="text/css">

	<!-- Fonts -->
<!-- 	<link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>
 -->
	<script src="{{ asset('/js/vendor.js') }}"></script>

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>
	<nav class="navbar navbar-default" style="margin-bottom: 10px;">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle Navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<ul class="nav nav-tabs">
				  <li role="presentation" class="{{ url('/home') == URL::current() ? 'active' : '' }}"><a href="{{ url('/home') }}">Transactions</a></li>
				  <li role="presentation" class="{{ url('/show_available_products') == URL::current() ? 'active' : '' }}"><a href="{{ url('/show_available_products') }}">Products</a></li>
				  <li role="presentation" class="{{ url('/bucketfile/final_file') == URL::current() ? 'active' : '' }}"><a href="{{ url('/bucketfile/final_file') }}">Bucketfile</a></li>
				  <li role="presentation" class="{{ url('/email_templates/form_submitted') == URL::current() ? 'active' : '' }}"><a href="{{ url('/email_templates') }}">Email Templates</a></li>
				  <li role="presentation" class="{{ url('/activities/statistics') == URL::current() ? 'active' : '' }}"><a href="{{ url('/activities/statistics') }}">Activities</a></li>
				</ul>
			</div>
 
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav navbar-right">
					@if (Auth::guest())
						<li><a href="{{ url('/auth/login') }}">Login</a></li>
						{{-- <li><a href="{{ url('/auth/register') }}">Register</a></li> --}}
					@else
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ Auth::user()->name }} <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
								<li><a href="{{ url('/home') }}">Transactions</a></li>
								<li><a href="{{ url('/show_available_products') }}">Products</a></li>
								<li><a href="{{ url('/bucketfile/final_file') }}">Bucket file</a></li>
								<li><a href="{{ url('/email_templates/form_submitted') }}">Email Templates</a></li>
								<li><a href="{{ url('/settings') }}">Settngs</a></li>
								<li><a href="{{ url('/activities/global_logs') }}">Activities</a></li>
								<li><a href="{{ url('/dbg_do_to') }}">TO DO</a></li>
								<li><a href="{{ url('/auth/logout') }}">Logout</a></li>
							</ul>
						</li>
					@endif
				</ul>
			</div>
 
 		
		</div>
	</nav>

	@yield('content')

</body>
</html>
