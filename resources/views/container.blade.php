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
				<div class="panel-heading">Get your Keys to your New Container</div>
				<div class="panel-body">
					<p>These are your SSH (Secure Shell) Keys to your container in case you need them.  Please store both these keys in a secure location in your computer, tablet, or smartphone.</p>
					<div class="col-md-8 col-md-offset-2">
						<a href="{{ $privatekey }}" class="btn btn-primary btn-block">
							<i class="fa fa-btn fa-key"></i> Private Key
						</a>
						<a href="{{ $publickey }}" class="btn btn-primary btn-block">
							<i class="fa fa-btn fa-key"></i> Public Key
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
