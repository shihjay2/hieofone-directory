@extends('layouts.app')

@section('view.stylesheet')
	<link rel="stylesheet" href="{{ asset('assets/css/toastr.min.css') }}">
@endsection

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-6 col-md-offset-3">
			<div class="panel panel-default">
				<div class="panel-heading">Licensed Providers</div>
				<div class="panel-body">
					<div class="col-md-8 col-md-offset-2">
						<a href="{{ url('/signup') }}" class="btn btn-primary btn-block"><i class="fa fa-btn fa-user-plus"></i> Sign Up</a>
					</div>
					<div class="col-md-8 col-md-offset-2">
						<a href="{{ url('/login') }}" class="btn btn-primary btn-block"><i class="fa fa-btn fa-sign-in"></i> Login</a>
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
