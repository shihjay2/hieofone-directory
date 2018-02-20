@extends('layouts.app')

@section('view.stylesheet')
	<link rel="stylesheet" href="{{ asset('assets/css/toastr.min.css') }}">
@endsection

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Welcome</div>
				<div class="panel-body">
					<p>Welome to the Directory for {{ $name }}.</p>
					<p>{{ $text }}</p>
				</div>
				<div class="panel-heading">Add yourself to the Directory Listing</div>
				<div class="panel-body">
					<p>Register using your HIE of One by chosing one of the following methods:</p>
					<div class="col-md-8 col-md-offset-2">
						<button type="button" class="btn btn-primary btn-block" id="copy_url" url-val="{{ url('/') }}">
							<i class="fa fa-btn fa-copy"></i> Copy URL of this Directory
						</button>
						<a href="{{ url('/uma_register_url') }}" class="btn btn-primary btn-block">
							<i class="fa fa-btn fa-hand-o-right"></i> or Enter the Web Address (URL) associated with your HIE of One
						</a>
						<a href="{{ url('/uma_register') }}" class="btn btn-primary btn-block">
							<i class="fa fa-btn fa-hand-o-right"></i> or Enter E-mail associated with your HIE of One
						</a>
					</div>
				</div>
				<div class="panel-heading">Are you a healthcare provider?</div>
				<div class="panel-body">
					<div class="col-md-8 col-md-offset-2">
						<a href="{{ url('/signup') }}" class="btn btn-primary btn-block">Sign Up</a>
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
		$("#copy_url").css('cursor', 'pointer').on('click', function(e){
			var copy = $(this).attr('url-val');
			var $temp = $("<input>");
			$("body").append($temp);
			$temp.val(copy).select();
			document.execCommand("copy");
			$temp.remove();
			toastr.success('Directory URL copied');
			e.preventDefault();
		});
	});
</script>
@endsection
