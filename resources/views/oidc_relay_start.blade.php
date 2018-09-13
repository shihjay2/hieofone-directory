@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="container-fluid panel-container">
						<div class="col-xs-6 col-md-9 text-left">
							<h4 class="panel-title" style="height:35px;display:table-cell !important;vertical-align:middle;">Proceed to {{ $type }}?</h4>
						</div>
						<div class="col-xs-3 text-right">
							@if (isset($back))
								{!! $back !!}
							@endif
						</div>
					</div>
				</div>
				<div class="panel-body">
					<div style="text-align: center;">
						<p>
							By clicking on Proceed, you understand that the application with the URL of {!! $url !!} will obtain and store any information about you once you have successfully authenticated with {{ $type }}.
						</p>
					</div>
					<form class="form-horizontal" role="form" method="POST" action="{!! $post !!}">
						<div class="form-group">
							<div class="col-md-6 col-md-offset-3">
								<button type="submit" class="btn btn-success btn-block" name="submit" value="allow">
									<i class="fa fa-btn fa-check"></i> Proceed
								</button>
								<button type="submit" class="btn btn-danger btn-block" name="submit" value="deny">
									<i class="fa fa-btn fa-times"></i> Cancel
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
	});
</script>
@endsection
