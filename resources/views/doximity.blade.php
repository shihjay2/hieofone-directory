@extends('layouts.app')

@section('view.stylesheet')
	<link rel="stylesheet" href="{{ asset('assets/css/toastr.min.css') }}">
	<style>
	html {
		position: relative;
		min-height: 100%;
	}
	body {
	/* Margin bottom by footer height */
		margin-bottom: 60px;
	}
	.footer {
		position: absolute;
		bottom: 0;
		width: 100%;
		/* Set the fixed height of the footer here */
		height: 60px;
		background-color: #f5f5f5;
	}
	.container .text-muted {
		margin: 20px 0;
	}
	.footer > .container {
		padding-right: 15px;
		padding-left: 15px;
	}
	</style>
@endsection

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">Get Credentials from Doximity</div>
				<div class="panel-body">
					<div style="text-align: center;">
						<div style="text-align: center;">
							<i class="fa fa-child fa-5x" aria-hidden="true" style="margin:20px;text-align: center;"></i>
							@if ($errors->has('tryagain'))
								<div class="form-group has-error">
									<span class="help-block has-error">
										<strong>{{ $errors->first('tryagain') }}</strong>
									</span>
								</div>
							@endif
						</div>
					</div>
					@if (isset($start))
						<div class="form-group" id="doximity" doximity="start">
					@else
						<div class="form-group" id="doximity" doximity="uport">
					@endif
						<div class="col-md-6 col-md-offset-3">
							<!-- <button type="button" class="btn btn-primary btn-block" id="connectUportBtn" onclick="loginBtnClick()"> -->
								<!-- <img src="{{ asset('assets/uport-logo-white.svg') }}" height="25" width="25" style="margin-right:5px"></img> Login with uPort -->
							<!-- </button> -->
							<!-- <button type="button" class="btn btn-primary btn-block" id="connectUportBtn1">Add NPI credential to uPort</button> -->
							<!-- <button type="button" class="btn btn-primary btn-block" id="connectUportBtn1" onclick="uportConnect()">Connect uPort</button> -->
							<!-- <button type="button" class="btn btn-primary btn-block" id="connectUportBtn2" onclick="sendEther()">Send Ether</button> -->
							<a class="btn btn-primary btn-block" href="{{ route('doximity') }}">
								<i class="fa fa-btn fa-openid"></i> Verify with Doximity
							</a>

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal" id="modal1" role="dialog">
	<div class="modal-dialog">
	  <!-- Modal content-->
		<div class="modal-content">
			<div id="modal1_header" class="modal-header">Add NPI credential to uPort?</div>
			<div id="modal1_body" class="modal-body" style="height:30vh;overflow-y:auto;">
				<p>This will simulate adding a verified credential to your existing uPort.</p>
				<p>After the simulated NPI credential is added, click on Login with uPort</p>
				<p>This will enable you to write a prescription.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal" onClick="attest()"><i class="fa fa-btn fa-check"></i> Proceed</button>
				<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-btn fa-times"></i> Close</button>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="modal2" role="dialog">
	<div class="modal-dialog">
	  <!-- Modal content-->
		<div class="modal-content">
			<div id="modal1_header" class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4>Doximity Credentials to uPort</h4>
			</div>
			<div id="modal1_body" class="modal-body" style="height:30vh;overflow-y:auto;">
				<p>You should have successfully added Doximity credentials to your uPort</p>
				<p>You can verify this by clicking on Verifications in your uPort App with a verification coming from HIE of One with NPI and Speciality claims added.</p>
				<!-- <p><a href="https://shihjay.xyz/nosh">Click here to access Alice's Health Record again</a></p> -->
				<p>Problems adding your credentials?</p>
				<p><a href="{{ route('doximity_start') }}">Try Again</a></p>
				<p><a href="#" id="close_modal2">Finish and Close</a></p>
			</div>
		</div>
	</div>
</div>
@endsection

@section('view.scripts')
<script src="{{ asset('assets/js/web3.js') }}"></script>
<script src="{{ asset('assets/js/uport-connect.js') }}"></script>
<script src="{{ asset('assets/js/toastr.min.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('[data-toggle="tooltip"]').tooltip();
		var start = $('#doximity').attr('doximity');
		if (start == 'uport') {
			attest();
		}
		$('#close_modal2').click(function() {
			$('#modal2').modal('hide');
			return false;
		});
	});
	// Setup
	const Connect = window.uportconnect.Connect;
	const appName = 'Doximity Credentials';
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
			var uport_url = '<?php echo route("login_uport"); ?>';
			var uport_data = 'name=' + credentials.name + '&uport=' + credentials.address;
			if (typeof credentials.NPI !== 'undefined') {
				uport_data += '&npi=' + credentials.NPI;
			}
			if (typeof credentials.email !== 'undefined') {
				uport_data += '&email=' + credentials.email;
			}
			$.ajax({
				type: "POST",
				url: uport_url,
				data: uport_data,
				dataType: 'json',
				beforeSend: function(request) {
					return request.setRequestHeader("X-CSRF-Token", $("meta[name='csrf-token']").attr('content'));
				},
				success: function(data){
					if (data.message !== 'OK') {
						toastr.error(data.message);
						// console.log(data);
					} else {
						window.location = data.url;
					}
				}
			});
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
			  claim: { "NPI": "{{ $npi }}", "Specialty": "{{ $specialty }}" },
			  exp: new Date().getTime() + 30 * 24 * 60 * 60 * 1000
			})
			$('#modal2').modal('show');
		});
	}
</script>
@endsection
