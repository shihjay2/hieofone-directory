@extends('layouts.app')

@section('view.stylesheet')
	<link rel="stylesheet" href="{{ asset('assets/css/toastr.min.css') }}">
@endsection

@section('content')
<div id="patient_start" class="container">
	<div class="row">
		<div class="col-md-6 col-md-offset-3">
			<div class="panel panel-default">
				<div class="panel-heading">Patients</div>
				<div class="panel-body" style="text-align: center;">
					<p>Welome to the Directory for {{ $name }}.  Do you have an existing Trustee authorization server?</p>
					<button type="button" class="btn btn-primary btn-block" id="no">
						<i class="fa fa-btn"></i> No, I'm new here
					</button>
					<button type="button" class="btn btn-primary btn-block" id="yes">
						<i class="fa fa-btn"></i> Yes
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="container_no" class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Create your own Trustee Patient Container</div>
				<div class="panel-body">
					<p style="text-align: center;">Enter your e-mail address to get your own Trustee Patient Container.  You will be receiving a confirmation e-mail for further instructions.</p>
					<div class="alert alert-danger" style="text-align: center;"><i class="fa fa-btn fa-exclamation-triangle"></i> Trustee Containers are in Beta Status.  Created containers are for testing only.</div>
					<div style="text-align: center;">
						<i class="fa fa-child fa-5x" aria-hidden="true" style="margin:20px;text-align: center;"></i>
					</div>
					<form class="form-horizontal" role="form" method="POST" action="{{ url('/container_create') }}">
						{{ csrf_field() }}
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
									<i class="fa fa-btn fa-download"></i> Create a Container for Me
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="container_yes" class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Add yourself to the Directory Listing</div>
				<div class="panel-body">
					<p style="text-align: center;">Register using your HIE of One by chosing one of the following methods:</p>
					<div class="col-md-8 col-md-offset-2">
						<button type="button" class="btn btn-primary btn-block" id="copy_url" url-val="{{ url('/') }}">
							<i class="fa fa-btn fa-copy"></i> Copy URL of this Directory
						</button>
						<a href="{{ url('/uma_register_url') }}" class="btn btn-primary btn-block">
							<i class="fa fa-btn fa-hand-o-right"></i> or Enter the Web Address (URL) associated with your Trustee Container
						</a>
						<a href="{{ url('/uma_register') }}" class="btn btn-primary btn-block">
							<i class="fa fa-btn fa-hand-o-right"></i> or Enter E-mail associated with your Trustee Container
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('view.scripts')
<script src="{{ asset('assets/js/toastr.min.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function() {
		toastr.options = {
            'closeButton': true,
            'debug': false,
            'newestOnTop': true,
            'progressBar': true,
            'positionClass': 'toast-bottom-full-width',
            'preventDuplicates': false,
            'showDuration': '300',
            'hideDuration': '1000',
            'timeOut': '5000',
            'extendedTimeOut': '1000',
            'showEasing': 'swing',
            'hideEasing': 'linear',
            'showMethod': 'fadeIn',
            'hideMethod': 'fadeOut'
        };
		var create = '{{ $create }}';
		if (create == 'no') {
			$("#container_no").hide();
			$("#container_yes").hide();
		} else {
			$("#container_no").show();
			$("#patient_start").hide();
			$("#container_yes").hide();
		}
		$("#no").click(function(){
			$("#container_no").show();
			$("#patient_start").hide();
		});
		$("#yes").click(function(){
			$("#container_yes").show();
			$("#patient_start").hide();
		});
	});
</script>
@endsection
