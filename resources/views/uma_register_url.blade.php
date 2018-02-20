@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">Directory Participation Consent</div>
				<div class="panel-body">
					<div style="text-align: center;">
						<i class="fa fa-child fa-5x" aria-hidden="true" style="margin:20px;text-align: center;"></i>
					</div>
					<form class="form-horizontal" role="form" method="POST" action="{{ url('/uma_register_url') }}">
						{{ csrf_field() }}
						<div class="form-group has-error" style="text-align: center;">
							<span class="help-block has-error">
								<strong>{{ $errors->first('tryagain') }}</strong>
							</span>
						</div>

						<div class="form-group" style="text-align: center;">
							<h4>By entering your <abbr data-toggle="tooltip" title="Find this URL by logging into your HIE of One authorization server, click on the username on the right upper corner, click on My Information">web address linked to your HIE of One authorization service below:</abbr></h4>
						</div>
						<div class="form-group">
							<ul>
								<li>You will be allowing directory users the potential to access your health information</li>
								<!-- <li>You will be able to make your authorization server identifiable in a patient directory for future physicians using mdNOSH to access your health information</li> -->
								<li>For more information about how your web address identifies you, <abbr data-toggle="tooltip" id="more_info" title="Click here">click here</abbr>
							</ul>
						</div>
						<div class="form_group" id="more_info_div">
							<h4 style="color:yellow;">How this directory will contact your authorization server</h4>
							<ol>
								<li>The directory will then determine if an authorization service (like HIE of One) exists with the URL provided.</li>
								<li>The directory will then make a call to register itself as a client to the authorization service so that physicians who have an account with mdNOSH that you invite can access your health-related resources.</li>
								<li>You will be prompted to accept or deny the registration of this directory to your HIE of One authorization service.</li>
							</ol>
						</div>

						<div class="form-group{{ $errors->has('url') ? ' has-error' : '' }}">
							<label for="url" class="col-md-4 control-label">Web Address (URL):</label>

							<div class="col-md-6">
								<input id="url" type="text" class="form-control" name="url" value="shihjay.xyz" data-toggle="tooltip" title="shihjay.xyz">

								@if ($errors->has('url'))
									<span class="help-block">
										<strong>{{ $errors->first('url') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-btn fa-sign-in"></i> Register
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
		$("#email").focus();
		$('[data-toggle="tooltip"]').tooltip();
		$('#more_info_div').hide();
		$('#more_info').on('click', function(){
			$('#more_info_div').toggle();
		});
	});
</script>
@endsection
