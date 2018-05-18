@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">Setup the HIE of One Directory Server</div>
				<div class="panel-body">
					<div style="text-align: center;">
					  <i class="fa fa-child fa-5x" aria-hidden="true" style="margin:20px;text-align: center;"></i>
					</div>
					<form class="form-horizontal" role="form" method="POST" action="{{ url('/install') }}">
						{{ csrf_field() }}

						<div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
							<label for="username" class="col-md-4 control-label">Administrator Username</label>

							<div class="col-md-6">
								<input id="username" type="username" class="form-control" name="username" value="{{ old('username') }}">

								@if ($errors->has('username'))
									<span class="help-block">
										<strong>{{ $errors->first('username') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
							<label for="password" class="col-md-4 control-label">Password</label>

							<div class="col-md-6">
								<input id="password" type="password" class="form-control" name="password">

								@if ($errors->has('password'))
									<span class="help-block">
										<strong>{{ $errors->first('password') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('confirm_password') ? ' has-error' : '' }}">
							<label for="confirm_password" class="col-md-4 control-label">Confirm Password</label>

							<div class="col-md-6">
								<input id="confirm_password" type="password" class="form-control" name="confirm_password">

								@if ($errors->has('confirm_password'))
									<span class="help-block">
										<strong>{{ $errors->first('confirm_password') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('org_name') ? ' has-error' : '' }}">
							<label for="org_name" class="col-md-4 control-label">Organization Name</label>

							<div class="col-md-6">
								<input id="org_name" class="form-control" name="org_name" value="{{ old('org_name') }}">

								@if ($errors->has('org_name'))
									<span class="help-block">
										<strong>{{ $errors->first('org_name') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('last_name') ? ' has-error' : '' }}">
							<label for="last_name" class="col-md-4 control-label">Last Name</label>

							<div class="col-md-6">
								<input id="last_name" class="form-control" name="last_name" value="{{ old('last_name') }}">

								@if ($errors->has('last_name'))
									<span class="help-block">
										<strong>{{ $errors->first('last_name') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('first_name') ? ' has-error' : '' }}">
							<label for="first_name" class="col-md-4 control-label">First Name</label>

							<div class="col-md-6">
								<input id="first_name" class="form-control" name="first_name" value="{{ old('first_name') }}">

								@if ($errors->has('first_name'))
									<span class="help-block">
										<strong>{{ $errors->first('first_name') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
							<label for="email" class="col-md-4 control-label">E-Mail Address</label>

							<div class="col-md-6">
								<input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}">

								@if ($errors->has('email'))
									<span class="help-block">
										<strong>{{ $errors->first('email') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-btn fa-sign-in"></i> Install
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('view.scripts')
<script type="text/javascript">
	$(document).ready(function() {
		$("#username").focus();
	});
</script>
@endsection
