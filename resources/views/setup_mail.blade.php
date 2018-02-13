@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">Setup the Mail Service</div>
				<div class="panel-body">
					<div style="text-align: center;">
					  <i class="fa fa-envelope fa-5x" aria-hidden="true" style="margin:20px;text-align: center;"></i>
					</div>
					<form class="form-horizontal" role="form" method="POST" action="{{ url('/setup_mail') }}">
						{{ csrf_field() }}

						<div class="form-group{{ $errors->has('mail_type') ? ' has-error' : '' }}">
							<label for="mail_type" class="col-md-4 control-label">Mail Service</label>

							<div class="col-md-6">
								<select id="mail_type" class="form-control" name="mail_type" value="{{ old('mail_type') }}">
									<option value="">None</option>
									<option value="gmail">Google Gmail</option>
									<option value="mailgun">Mailgun</option>
									<option value="sparkpost">SparkPost</option>
									<option value="ses">Amazon SES</option>
									<option value="unique">Custom</option>
								</select>
								@if ($errors->has('mail_type'))
									<span class="help-block">
										<strong>{{ $errors->first('mail_type') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="mail_form gmail unique form-group{{ $errors->has('mail_username') ? ' has-error' : '' }}">
							<label for="mail_username" class="col-md-4 control-label">Username</label>

							<div class="col-md-6">
								<input id="mail_username" type="username" class="form-control" name="mail_username" value="{{ old('mail_username') }}">

								@if ($errors->has('mail_username'))
									<span class="help-block">
										<strong>{{ $errors->first('mail_username') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="mail_form unique form-group{{ $errors->has('mail_password') ? ' has-error' : '' }}">
							<label for="mail_password" class="col-md-4 control-label">Password</label>

							<div class="col-md-6">
								<input id="mail_password" type="password" class="form-control" name="mail_password">

								@if ($errors->has('mail_password'))
									<span class="help-block">
										<strong>{{ $errors->first('mail_password') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="mail_form gmail form-group{{ $errors->has('google_client_id') ? ' has-error' : '' }}">
							<label for="google_client_id" class="col-md-4 control-label">Google Client ID</label>

							<div class="col-md-6">
								<input id="google_client_id" class="form-control" name="google_client_id" value="{{ old('google_client_id') }}">

								@if ($errors->has('google_client_id'))
									<span class="help-block">
										<strong>{{ $errors->first('google_client_id') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="mail_form gmail form-group{{ $errors->has('google_client_secret') ? ' has-error' : '' }}">
							<label for="google_client_secret" class="col-md-4 control-label">Google Client Secret</label>

							<div class="col-md-6">
								<input id="google_client_secret" class="form-control" name="google_client_secret" value="{{ old('google_client_secret') }}">

								@if ($errors->has('google_client_secret'))
									<span class="help-block">
										<strong>{{ $errors->first('google_client_secret') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="mail_form mailgun form-group{{ $errors->has('mailgun_domain') ? ' has-error' : '' }}">
							<label for="mailgun_domain" class="col-md-4 control-label">Mailgun Domain</label>

							<div class="col-md-6">
								<input id="mailgun_domain" class="form-control" name="mailgun_domain" value="{{ old('mailgun_domain') }}">

								@if ($errors->has('mailgun_domain'))
									<span class="help-block">
										<strong>{{ $errors->first('mailgun_domain') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="mail_form mailgun form-group{{ $errors->has('mailgun_secret') ? ' has-error' : '' }}">
							<label for="mailgun_secret" class="col-md-4 control-label">Mailgun Secret</label>

							<div class="col-md-6">
								<input id="mailgun_secret" class="form-control" name="mailgun_secret" value="{{ old('mailgun_secret') }}">

								@if ($errors->has('mailgun_secret'))
									<span class="help-block">
										<strong>{{ $errors->first('mailgun_secret') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="mail_form sparkpost form-group{{ $errors->has('sparkpost_secret') ? ' has-error' : '' }}">
							<label for="sparkpost_secret" class="col-md-4 control-label">SparkPost Secret</label>

							<div class="col-md-6">
								<input id="sparkpost_secret" class="form-control" name="sparkpost_secret" value="{{ old('sparkpost_secret') }}">

								@if ($errors->has('sparkpost_secret'))
									<span class="help-block">
										<strong>{{ $errors->first('sparkpost_secret') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="mail_form ses form-group{{ $errors->has('ses_key') ? ' has-error' : '' }}">
							<label for="ses_key" class="col-md-4 control-label">Amazon SES Key</label>

							<div class="col-md-6">
								<input id="ses_key" class="form-control" name="ses_key" value="{{ old('ses_key') }}">

								@if ($errors->has('ses_key'))
									<span class="help-block">
										<strong>{{ $errors->first('ses_key') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="mail_form ses form-group{{ $errors->has('ses_secret') ? ' has-error' : '' }}">
							<label for="ses_secret" class="col-md-4 control-label">Amazon SES Secret</label>

							<div class="col-md-6">
								<input id="ses_secret" class="form-control" name="ses_secret" value="{{ old('ses_secret') }}">

								@if ($errors->has('ses_secret'))
									<span class="help-block">
										<strong>{{ $errors->first('ses_secret') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="mail_form unique form-group{{ $errors->has('mail_host') ? ' has-error' : '' }}">
							<label for="mail_host" class="col-md-4 control-label">Mail Host URL</label>

							<div class="col-md-6">
								<input id="mail_host" class="form-control" name="mail_host" value="{{ old('mail_host') }}">

								@if ($errors->has('mail_host'))
									<span class="help-block">
										<strong>{{ $errors->first('mail_host') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="mail_form unique form-group{{ $errors->has('mail_port') ? ' has-error' : '' }}">
							<label for="mail_port" class="col-md-4 control-label">Port</label>

							<div class="col-md-6">
								<input id="mail_port" class="form-control" name="mail_port" value="{{ old('mail_port') }}">

								@if ($errors->has('mail_port'))
									<span class="help-block">
										<strong>{{ $errors->first('mail_port') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="mail_form unique form-group{{ $errors->has('mail_encryption') ? ' has-error' : '' }}">
							<label for="mail_encryption" class="col-md-4 control-label">Encryption</label>

							<div class="col-md-6">
								<select id="mail_encryption" class="form-control" name="mail_encryption" value="{{ old('mail_encryption') }}">
									<option value="">None</option>
									<option value="SSL">SSL</option>
									<option value="TLS">TLS</option>
								</select>

								@if ($errors->has('mail_encryption'))
									<span class="help-block">
										<strong>{{ $errors->first('mail_encryption') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-btn fa-sign-in"></i> Save
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
		$("#mail_type").focus();
		$(".mail_form").hide();
		$("#mail_type").change(function(){
			var mail_type = $("#mail_type").val();
			$(".mail_form").hide();
			$("." + mail_type).show();
		});
	});
</script>
@endsection
