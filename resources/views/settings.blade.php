@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">Directory Settings</div>
				<div class="panel-body">
					<div style="text-align: center;">
					  <i class="fa fa-child fa-5x" aria-hidden="true" style="margin:20px;text-align: center;"></i>
					</div>
					<form class="form-horizontal" role="form" method="POST" action="{{ url('/settings') }}">
						{{ csrf_field() }}

						<div class="form-group{{ $errors->has('last_name') ? ' has-error' : '' }}">
							<label for="last_name" class="col-md-4 control-label">Organization Name</label>

							<div class="col-md-6">
								<input id="last_name" class="form-control" name="last_name" value="{{ old('last_name', $last_name) }}">

								@if ($errors->has('last_name'))
									<span class="help-block">
										<strong>{{ $errors->first('last_name') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('homepage') ? ' has-error' : '' }}">
							<label for="homepage" class="col-md-4 control-label">Home Page</label>

							<div class="col-md-6">
								<select id="homepage" class="form-control" name="homepage" value="{{ old('homepage', $homepage) }}">
									<option value="0">Patient Registration</option>
									<option value="1">Search Page</option>
								</select>
								@if ($errors->has('homepage'))
									<span class="help-block">
										<strong>{{ $errors->first('homepage') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
							<label for="description" class="col-md-4 control-label">Description</label>

							<div class="col-md-6">
								<textarea id="description" cols="50" rows="10" class="form-control" name="description" value="{{ old('description', $description) }}"></textarea>

								@if ($errors->has('description'))
									<span class="help-block">
										<strong>{{ $errors->first('description') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('condition') ? ' has-error' : '' }}">
							<label for="condition" class="col-md-4 control-label">Common Patient Condition</label>

							<div class="col-md-6">
								<input id="condition" class="form-control" name="condition" value="{{ old('condition', $condition) }}">

								@if ($errors->has('condition'))
									<span class="help-block">
										<strong>{{ $errors->first('condition') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-btn fa-save"></i> Save
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
