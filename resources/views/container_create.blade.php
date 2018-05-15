@extends('layouts.app')

@section('view.stylesheet')
	<link rel="stylesheet" href="{{ asset('assets/css/toastr.min.css') }}">
@endsection

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Create a Trustee Patient Container</div>
				<div class="panel-body">
					<p style="text-align: center;">Enter the information about a Trustee Patient Container to a patient.  They will be receiving a confirmation e-mail for further instructions.</p>
					<div style="text-align: center;">
					  <i class="fa fa-child fa-5x" aria-hidden="true" style="margin:20px;text-align: center;"></i>
					</div>
					<form class="form-horizontal" role="form" method="POST" action="{{ route('container_create', ['complete']) }}">
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

						<div class="form-group{{ $errors->has('url') ? ' has-error' : '' }}">
							<label for="url" class="col-md-4 control-label">URL of Container</label>

							<div class="col-md-6">
								<input id="url" class="form-control" name="url" value="{{ old('url') }}">

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
									<i class="fa fa-btn fa-sign-in"></i> Send
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
	});
</script>
@endsection
