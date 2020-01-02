@extends('layouts.app')

@section('view.stylesheet')
	<link rel="stylesheet" href="{{ asset('assets/css/toastr.min.css') }}">
@endsection

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-6 col-md-offset-3">
			<div class="panel panel-default">
				<div class="panel-heading"><span style="font-size:large">Support</span></div>
				<div class="panel-body" style="text-align: center;">
					<p>Welome to the Directory for {{ $name }}.</p>
					<p>To start, please <a href="http://bit.ly/TrusteeForum" target="_blank">see this document</a> for any general questions about Trustee.</p>
					<p>If you are a visitor and do not have a Trustee Authorizaion Server with us and have a specific question, please fill out this form and we will get back to you.</p>
					<p>If you have a Trustee Authorization Server with us, please <a href="{{ url('/') . '/tickets' }}">click here</a> to file a ticket. (You'll need to login first when you click on the link.)</p>
					@if (isset($message_action))
						<div class="alert alert-danger">
							<strong>{!! $message_action !!}</strong>
						</div>
					@endif
					<form class="form-horizontal" role="form" method="POST" action="{{ url('/support') }}">
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

						<div class="form-group{{ $errors->has('message_text') ? ' has-error' : '' }}">
							<label for="message_text" class="col-md-4 control-label">Message</label>

							<div class="col-md-6">
								<textarea id="message_text" class="form-control" name="message_text" value="{{ old('message_text') }}"></textarea>

								@if ($errors->has('message_text'))
									<span class="help-block">
										<strong>{{ $errors->first('message_text') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-btn fa-download"></i> Submit
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="complete_modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Support Response...</h4>
      		</div>
      		<div class="modal-body">
        		<p>{!! $complete !!}</p>
      		</div>
      		<div class="modal-footer">
        		<a href="{{ route('welcome0') }}" class="btn btn-default">Got It!</a>
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
		var complete = '{!! $complete !!}';
		if (complete !== 'no') {
			$('#complete_modal').modal('show');
		}
	});
</script>
@endsection
