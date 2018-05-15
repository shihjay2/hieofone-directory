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
					<div class="col-md-10 col-md-offset-1">
						<form class="input-group form" border="0" id="search_patient_form" role="search" action="{{ url('/search_welcome') }}" method="POST" style="margin-bottom:0px;" data-nosh-target="search_patient_results">
							<input type="text" class="form-control search" id="search_field" name="search_field" placeholder="Enter search term" style="margin-bottom:0px;" autocomplete="off">
							<input type="hidden" name="type" value="div">
							<span class="input-group-btn">
								<button type="submit" class="btn btn-md" id="search_patient_submit" name="submit" value="Go"><i class="glyphicon glyphicon-search"></i></button>
							</span>
						</form>
						<div class="list-group" id="search_patient_results"></div>
						<div class="col-md-8 col-md-offset-2">
							<a href="{{ url('/metadata/medication') }}" class="btn btn-primary btn-block">List only patients who are sharing medication data</a>
						</div>
					</div>
					<div class="col-md-10 col-md-offset-1">
						<br><br><br>
						{!! $search !!}
					</div>
				</div>
				<div class="panel-heading">Get your own Trustee Patient Container</div>
				<div class="panel-body">
					<div class="col-md-8 col-md-offset-2">
						<a href="{{ route('patients', ['yes']) }}" class="btn btn-primary btn-block">Get Started!</a>
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
