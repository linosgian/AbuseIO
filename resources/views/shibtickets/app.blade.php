<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<title>{{ Config::get('app.name') }}</title>
		<!-- Bootstrap core css -->
		<link rel="stylesheet" type="text/css" href="{{ asset('/css/bootstrap.min.css') }}"/>

		<!-- dataTables css for bootstrap -->
		<link rel="stylesheet" type="text/css" href="{{ asset('/css/dataTables.bootstrap.min.css') }}"/>

		<!-- Localization flags -->
		<link rel="stylesheet" type="text/css" href="{{ asset('/css/flag-icon-min.css') }}">

		<!-- Custom css -->
		<link rel="stylesheet" type="text/css" href="{{ asset('/css/custom.css') }}">
	</head>
	<body class="ash">
		<div class="container">
			<div class="jumbotron">
				<div class="media">
					<div class="media-left">
						<img class="logo-grnet img-responsive img-inline" src="/ash/logo/{{ $brand->id  }}" alt='{{ $brand->company_name }}' />
					</div>
					<div class="media-body">
						<h1>{{ trans('ash.title') }}</h1>
						<h2>{{ $brand->company_name }}</h2>
					</div>
				</div>
			</div>

		<div class="container">
			@if (Session::has('message'))
				<div class="alert alert-info alert-dismissible">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<p>{{ Session::get('message') }}</p>
				</div>
			@endif
			@yield('content')
		</div>
		<!-- Bootstrap Javascript ---------------------------->
		<script type="text/javascript" src="{{ asset('/js/jquery.min.js') }}"></script>
		<script type="text/javascript" src="{{ asset('/js/bootstrap.min.js') }}"></script>
		<script type="text/javascript" src="{{ asset('/js/jquery.dataTables.min.js') }}"></script>
		<script type="text/javascript" src="{{ asset('/js/dataTables.bootstrap.min.js') }}"></script>
		@yield('extrajs')
		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="{{ asset('/js/html5shiv.min.js') }}"></script>
			<script src="{{ asset('/js/respond.min.js') }}"></script>
		<![endif]-->
	</body>
</html>
