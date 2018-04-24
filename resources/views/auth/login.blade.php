@extends('auth.app')

@section('content')
<div class="container-fluid">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">Login with <b>your organization credentials</b></div>
				<div class="panel-body">
					<div class="form-group">
						<div class="col-md-6 col-md-offset-4">
							<img src="https://rz.uni-greifswald.de/fileadmin/_processed_/8/5/csm_shibboleth_logo_b607b8e8ff.png" alt="Shibboleth logo" width="35%" height="35%">
							<!-- Redirects user to /shib/*, where /shib/* is the protected resource by Shibboleth so user will login through IdP  -->
							&nbsp;&nbsp;<a class="btn btn-link" href="{{ url('/shib/tickets') }}">
								<button class="btn btn-primary">Shib Login</button></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">Login</div>
				<div class="panel-body">
					@if (count($errors) > 0)
						<div class="alert alert-danger">
							{!! trans('login.warning.whoops') !!}<br><br>
							<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif

					<form class="form-horizontal" role="form" method="POST" action="{{ url('/auth/login') }}">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">

						<div class="form-group">
							<label class="col-md-4 control-label">{{ trans('login.caption.email_address') }}</label>
							<div class="col-md-6">
								<input type="email" class="form-control" name="email" value="{{ old('email') }}">
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">{{ trans('login.caption.password') }}</label>
							<div class="col-md-6">
								<input type="password" class="form-control" name="password">
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<div class="checkbox">
									<label>
										<input type="checkbox" name="remember"> {{ trans('login.caption.remember_me') }}
									</label>
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">{{ trans('login.btn.login') }}</button>

								<a class="btn btn-link" href="{{ url('/password/email') }}">{{ trans('login.link.forgot_your_password') }}</a>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
