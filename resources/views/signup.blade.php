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
					<div id="uport_indicator" style="text-align: center;display:none;">
						<i class="fa fa-spinner fa-spin fa-pulse fa-2x fa-fw"></i><span id="modaltext" style="margin:10px">Loading uPort...</span><br><br>
					</div>
					<form class="form-horizontal" role="form" method="POST" action="{{ url('/signup') }}" id="signup_form">
						{{ csrf_field() }}

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-2">
								<p>For Clinician access to consenting Trustee health records, you'll need a smartphone with the <a href="https://uport.me" target="_blank">uPort app</a> installed first.
								<div>
									<a href="https://itunes.apple.com/us/app/uport-id/id1123434510?mt=8" target="_blank">
										<img class="auth-app-badge" src="{{ asset('assets/AppleApp.svg') }}" alt="Apple App Logo" style="padding:6%">
									</a>
									<a href='https://play.google.com/store/apps/details?id=com.uportMobile&pcampaignid=MKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1' target="_blank"><img alt='Get it on Google Play' src="{{ asset('assets/google-play-badge.png') }}"/ style="height:60px;"></a>
								</div>
								<!-- <a href="https://uport.me" target="_blank"><img class="img-responsive" src="{{ asset('assets/uPortApp.png') }}"></img></a> -->
								<br><p>Then add your NPI to your uPort app through the Doximity credential verification site</p>
                                <button type="button" class="btn btn-primary btn-block" id="connectUportBtn1"><i class="fa fa-btn fa-plus"></i> Add Doximity Clinician Verification</button>
								<br><p>After you register with the uPort app, the form fields will then populate with your information.<p>
                                <button type="button" class="btn btn-primary btn-block" id="connectUportBtn" onclick="loginBtnClick()">
                                    <img src="{{ asset('assets/uport-logo-white.svg') }}" height="25" width="25" style="margin-right:5px"></img>  Check your uPort Credentials
                                </button>
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
						<div class="signup_form" style="display:none">
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

							<!-- <div class="form-group{{ $errors->has('specialty') ? ' has-error' : '' }}">
								<label for="specialty" class="col-md-4 control-label">Specialty</label>

								<div class="col-md-6">
									<input id="specialty" class="form-control" name="specialty" value="{{ old('specialty') }}" readonly>

									@if ($errors->has('specialty'))
										<span class="help-block">
											<strong>{{ $errors->first('specialty') }}</strong>
										</span>
									@endif
								</div>
							</div> -->

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
						</div>

						<div class="alert alert-danger">
							<p>Clinician Note: This information will be logged by the patient's Trustee when you use uPort to sign-in as a clinician.  For your privacy, HIE of One does not store any clinician information.  Your activity is logged with the specific patient's Trustee and timestamped on a blockchain in case of dispute.</p>
						</div>

						<div class="alert alert-danger">
							<p>Clinician Note: The patient's Trustee is patient-controlled and may not be available to you in case of dispute.  HIE of One recommends you retain your own records for legal purposes.  You can do that in your existing system or by installing your own NOSH practice management system (coming soon).</p>
						</div>

						<!-- <div class="form-group">
							<div class="col-md-8 col-md-offset-2">
								<button type="submit" class="btn btn-primary btn-block">
									<i class="fa fa-btn fa-sign-in"></i> Sign Up!
								</button>
							</div>
						</div> -->
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
				<p>We're demonstrating the addition of a verified credential to a blockchain identity by using Doximity. Anyone with a Doximity sign in is able to add this credential.</p>
				<p>Please review Doximity's user verification policies before trusting this credential for any particular purpose.</p>
				<!-- <p>This will simulate adding a verified credential to your existing uPort.</p>
				<p>Clicking proceed with add a simulated NPI number</p>
				<p>Clicking on Get from Doximity will demonstrate how you can get a verified credential if you have an existing Doximity account</p>
				<p>After the credential is added, click on Login with uPort</p>
				<p>This will enable you to write a prescription.</p> -->
			</div>
			<div class="modal-footer">
				<!-- <button type="button" class="btn btn-default" data-dismiss="modal" onClick="attest()"><i class="fa fa-btn fa-check"></i> Proceed</button> -->
				<a href="{{ route('doximity_start') }}" target="_blank" class="btn btn-default" id="doximity_modal"><i class="fa fa-btn fa-hand-o-right"></i> Get from Doximity</a>
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
		$('.signup_form').hide();
	});
    // Setup
	const Connect = window.uportconnect;
	const appName = 'HIE of One Directory';
	const uport = new Connect(appName, {
		network: 'rinkeby'
	});

	const loginBtnClick = () => {
		$("#uport_indicator").show();
		uport.requestDisclosure({
			requested: ['name', 'email', 'NPI'],
			notifications: true // We want this if we want to recieve credentials
	  	});
		uport.onResponse('disclosureReq').then((res) => {
            var parsed = NameParse.parse(res.payload.name);
            $('#last_name').val(parsed.lastName);
            $('#first_name').val(parsed.firstName);
			$('#uport_id').val(res.payload.did);
            if (typeof res.payload.NPI !== 'undefined' && res.payload.NPI !== '') {
                $('#npi').val(res.payload.NPI);
            } else {
				$('#npi').closest('.form-group').addClass('has-error');
                $('#npi').parent().append('<span class="help-block">NPI required</span>');
			}
            if (typeof res.payload.email !== 'undefined' && res.payload.email !== '') {
				$('#email').val(res.payload.email);
			} else {
				$('#email').closest('.form-group').addClass('has-error');
                $('#email').parent().append('<span class="help-block">E-mail address required</span>');
			}
			// if (typeof credentials.Specialty !== 'undefined' && credentials.Specialty !== '') {
            //     $('#specialty').val(credentials.Specialty);
            // } else {
			// 	$('#specialty').closest('.form-group').addClass('has-error');
            //     $('#specialty').parent().append('<span class="help-block">Speciality required</span>');
			// }
			$('.signup_form').show();
			$("#uport_indicator").hide();
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
