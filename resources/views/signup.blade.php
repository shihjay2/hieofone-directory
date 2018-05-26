@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">Sign up to the {{ $name }} Directory</div>
				<div class="panel-body">
					<div style="text-align: center;">
					  <i class="fa fa-child fa-5x" aria-hidden="true" style="margin:20px;text-align: center;"></i>
					</div>
					<form class="form-horizontal" role="form" method="POST" action="{{ url('/signup') }}" id="signup_form" style="display:none;">
						{{ csrf_field() }}

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-2">
                                <button type="button" class="btn btn-primary btn-block" id="connectUportBtn" onclick="loginBtnClick()">
                                    <img src="{{ asset('assets/uport-logo-white.svg') }}" height="25" width="25" style="margin-right:5px"></img> Obtain credentials with uPort
                                </button>
                                <button type="button" class="btn btn-primary btn-block" id="connectUportBtn1"><i class="fa fa-btn fa-plus"></i> Add Doximity Clinician Verification</button>
                            </div>
                        </div>

						<!-- <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
							<label for="username" class="col-md-4 control-label">Username</label>

							<div class="col-md-6">
								<input id="username" type="username" class="form-control" name="username" value="{{ old('username') }}">

								@if ($errors->has('username'))
									<span class="help-block">
										<strong>{{ $errors->first('username') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
							<label for="password" class="col-md-4 control-label">Password</label>

							<div class="col-md-6">
								<input id="password" type="password" class="form-control" name="password">

								@if ($errors->has('password'))
									<span class="help-block">
										<strong>{{ $errors->first('password') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('confirm_password') ? ' has-error' : '' }}">
							<label for="confirm_password" class="col-md-4 control-label">Confirm Password</label>

							<div class="col-md-6">
								<input id="confirm_password" type="password" class="form-control" name="confirm_password">

								@if ($errors->has('confirm_password'))
									<span class="help-block">
										<strong>{{ $errors->first('confirm_password') }}</strong>
									</span>
								@endif
							</div>
						</div> -->

						<div class="form-group{{ $errors->has('first_name') ? ' has-error' : '' }}">
							<label for="first_name" class="col-md-4 control-label">First Name</label>

							<div class="col-md-6">
								<input id="first_name" class="form-control" name="first_name" value="{{ old('first_name') }}" readonly>

								@if ($errors->has('first_name'))
									<span class="help-block">
										<strong>{{ $errors->first('first_name') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('last_name') ? ' has-error' : '' }}">
							<label for="last_name" class="col-md-4 control-label">Last Name</label>

							<div class="col-md-6">
								<input id="last_name" class="form-control" name="last_name" value="{{ old('last_name') }}" readonly>

								@if ($errors->has('last_name'))
									<span class="help-block">
										<strong>{{ $errors->first('last_name') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('npi') ? ' has-error' : '' }}">
							<label for="npi" class="col-md-4 control-label">NPI</label>

							<div class="col-md-6">
								<input id="npi" class="form-control" name="npi" value="{{ old('npi') }}" readonly>

								@if ($errors->has('npi'))
									<span class="help-block">
										<strong>{{ $errors->first('npi') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
							<label for="email" class="col-md-4 control-label">E-Mail Address</label>

							<div class="col-md-6">
								<input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" readonly>

								@if ($errors->has('email'))
									<span class="help-block">
										<strong>{{ $errors->first('email') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('uport_id') ? ' has-error' : '' }}">
							<label for="uport_id" class="col-md-4 control-label">uPort Address</label>

							<div class="col-md-6">
								<input id="uport_id" class="form-control" name="uport_id" value="{{ old('uport_id') }}" readonly>

								@if ($errors->has('uport_id'))
									<span class="help-block">
										<strong>{{ $errors->first('uport_id') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-btn fa-sign-in"></i> Sign Up!
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="modal1" role="dialog">
	<div class="modal-dialog">
	  <!-- Modal content-->
		<div class="modal-content">
			<div id="modal1_header" class="modal-header">Add clinician credential to uPort from Doximity?</div>
			<div id="modal1_body" class="modal-body" style="height:30vh;overflow-y:auto;">
				<p>This will simulate adding a verified credential to your existing uPort.</p>
				<p>Clicking proceed with add a simulated NPI number</p>
				<p>Clicking on Get from Doximity will demonstrate how you can get a verified credential if you have an existing Doximity account</p>
				<p>After the credential is added, click on Login with uPort</p>
				<p>This will enable you to write a prescription.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal" onClick="attest()"><i class="fa fa-btn fa-check"></i> Proceed</button>
				<a href="https://cloud.noshchartingsystem.com/doximity/" target="_blank" class="btn btn-default" id="doximity_modal"><i class="fa fa-btn fa-hand-o-right"></i> Get from Doximity</a>
				<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-btn fa-times"></i> Close</button>
			  </div>
		</div>
	</div>
</div>
@endsection

@section('view.scripts')
<script src="{{ asset('assets/js/web3.js') }}"></script>
<script src="{{ asset('assets/js/uport-connect.js') }}"></script>
<script src="{{ asset('assets/js/toastr.min.js') }}"></script>
<script src="{{ asset('assets/js/browser-shims.js') }}"></script>
<script src="{{ asset('assets/js/parse-names.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("#username").focus();
        $('[data-toggle="tooltip"]').tooltip();
		$("#connectUportBtn1").click(function(){
            $('#modal1').modal('show');
        });
		$('#doximity_modal').click(function(){
			$('#modal1').modal('hide');
		});
	});
    // Setup
	const Connect = window.uportconnect.Connect;
	const appName = 'hieofone';
	const connect = new Connect(appName, {
		'clientId': '2ohNU4wT7Y7YqJ5kLMw2of1bdCnuFB1tZmr',
		'signer': window.uportconnect.SimpleSigner('9d3aef4e1e1a80877fe501151f9372de2e34cb2744e875c5e1b1af5a73f4eb7e'),
		'network': 'rinkeby'
	});
	const web3 = connect.getWeb3();

	const loginBtnClick = () => {
		connect.requestCredentials({
	      requested: ['name', 'phone', 'country', 'email', 'description', 'NPI'],
	      notifications: true // We want this if we want to recieve credentials
	    }).then((credentials) => {
			console.log(credentials);
            var parsed = NameParse.parse(credentials.name);
            $('#last_name').val(parsed.lastName);
            $('#first_name').val(parsed.firstName);
			$('#uport_id').val(credentials.address);
            if (typeof credentials.NPI !== 'undefined') {
                $('#npi').val(credentials.NPI);
            }
            if (typeof credentials.email !== 'undefined') {
				$('#email').val(credentials.email);
			}
			$('#signup_form').show();
		}, console.err);
	};

	let globalState = {
		uportId: "",
		txHash: "",
		sendToAddr: "0x687422eea2cb73b5d3e242ba5456b782919afc85",
		sendToVal: "5"
	};

	const uportConnect = function () {
		web3.eth.getCoinbase((error, address) => {
			if (error) { throw error; }
			console.log(address);
			globalState.uportId = address;
		});
	};

	const sendEther = () => {
		const value = parseFloat(globalState.sendToVal) * 1.0e18;
		const gasPrice = 100000000000;
		const gas = 500000;
		web3.eth.sendTransaction(
			{
				from: globalState.uportId,
				to: globalState.sendToAddr,
				value: value,
				gasPrice: gasPrice,
				gas: gas
			},
			(error, txHash) => {
				if (error) { throw error; }
				globalState.txHash = txHash;
				console.log(txHash);
			}
		);
	};

	const attest = () => {
		connect.requestCredentials({
	      requested: ['name', 'phone', 'country', 'email', 'description'],
	      notifications: true // We want this if we want to recieve credentials
	    }).then((credentials) => {
			console.log(credentials);
			connect.attestCredentials({
			  sub: credentials.address,
			  claim: { "NPI": "1023005410" },
			  exp: new Date().getTime() + 30 * 24 * 60 * 60 * 1000
			})
		});
	}
</script>
@endsection
