<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        @if (isset($thread))
            {{ $thread->title }} -
        @endif
        @if (isset($category))
            {{ $category->title }} -
        @endif
        {{ trans('forum::general.home_title') }}
    </title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">

    <style>
    body {
        /* padding: 30px 0; */
        font-family: 'Lato';
    }

    textarea {
        min-height: 200px;
    }

    .deleted {
        opacity: 0.65;
    }
    </style>
</head>
<body id="app-layout">
    <nav class="navbar navbar-default navbar-static-top">
		<div class="container">
			<div class="navbar-header">

				<!-- Collapsed Hamburger -->
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
					<span class="sr-only">Toggle Navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>

				<!-- Branding Image -->
				<a class="navbar-brand" href="{{ url('/') }}">
					Directory for {{ Session::get('owner') }}
				</a>
			</div>

			<div class="collapse navbar-collapse" id="app-navbar-collapse">
				<!-- Left Side Of Navbar -->
				<ul class="nav navbar-nav">
					@if (!Auth::guest())
						@if (Session::get('is_owner') == 'yes')
							<li><a href="{{ url('/setup_mail') }}">E-mail Service</a></li>
							<li><a href="{{ url('/settings') }}">Settings</a></li>
							<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"> Users <span class="caret"></span></a>
								<ul class="dropdown-menu" role="menu">
									<li><a href="{{ url('/add_owner') }}">Add Administrative User</a></li>
									<li><a href="{{ url('/users') }}">Authorized</a></li>
									<li><a href="{{ url('/authorize_user') }}">Pending Authorization</a></li>
								</ul>
							</li>
							<li><a href="{{ url('/make_invitation') }}">Invite</a></li>
						@else
							<li><a href="{{ url('/home') }}">My Patients</a></li>
						@endif
						<li><a href="{{ url('/forum') }}">Forum</a></li>
						<li><a href="{{ url('/tickets') }}">Support</a></li>
						<li><a href="{{ url('/reports') }}">Reports</a></li>
						<li><a href="{{ url('/privacy_policy') }}">Privacy Policy</a></li>
					@endif
				</ul>

				<!-- Right Side Of Navbar -->
				<ul class="nav navbar-nav navbar-right">
					<!-- Authentication Links -->
					@if (Auth::guest())
						@if (!isset($noheader))
							<li><a href="{{ url('/patients') }}">Patients</a></li>
							<li><a href="{{ url('/clinicians') }}">Clinicians</a></li>
							<!-- <li><a href="{{ url('/others') }}">Others</a></li> -->
							<li><a href="{{ url('/privacy_policy') }}">Privacy Policy</a></li>
							<li><a href="{{ url('/support') }}">Support</a></li>
							<!-- <li><a href="{{ url('/signup') }}">Sign Up</a></li> -->
							@if (isset($demo))
								<li class="dropdown">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
										Demo User <span class="caret"></span>
									</a>
									<ul class="dropdown-menu" role="menu">
										<li><a href="{{ url('/demo_patient_list') }}"><i class="fa fa-btn fa-list"></i>Public Patient List</a></li>
										<li><a href=""><i class="fa fa-btn fa-cogs"></i>My Information</a></li>
										<li><a href=""><i class="fa fa-btn fa-sign-out"></i>Sign Out</a></li>
									</ul>
								</li>
							@else
								<li class="dropdown">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
										Demo Pages <span class="caret"></span>
									</a>
									<ul class="dropdown-menu" role="menu">
										<li><a href="{{ url('/demo_patient_list') }}"><i class="fa fa-btn fa-list"></i>Public Patient List</a></li>
										<li><a href="{{ url('/demo_patient_list/yes') }}"><i class="fa fa-btn fa-list"></i>Patient List after Login</a></li>
									</ul>
								</li>
								<li><a href="{{ url('/login') }}">Sign In</a></li>
							@endif
						@endif
					@else
						@if (Session::get('is_owner') == 'yes')
							<li class="dropdown" id="owner_li">
						@else
							<li class="dropdown">
						@endif
							@if (Session::get('is_owner') == 'yes')
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
							@else
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
							@endif
								{{ Session::get('full_name') }} <span class="caret"></span>
							</a>

							<ul class="dropdown-menu" role="menu">
								<li><a href="{{ url('/my_info') }}"><i class="fa fa-fw fa-btn fa-cogs"></i>My Information</a></li>
								<li><a href="{{ url('/logout') }}"><i class="fa fa-fw fa-btn fa-sign-out"></i>Sign Out</a></li>
							</ul>
						</li>
					@endif
				</ul>
			</div>
		</div>
	</nav>
    <div class="container">
        @include ('forum::partials.breadcrumbs')
        @include ('forum::partials.alerts')

        @yield('content')
    </div>
    <script src="{{ asset('assets/js/jquery-3.1.1.min.js') }}"></script>
	<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('assets/js/jquery.maskedinput.min.js') }}"></script>
    <script>
    var toggle = $('input[type=checkbox][data-toggle-all]');
    var checkboxes = $('table tbody input[type=checkbox]');
    var actions = $('[data-actions]');
    var forms = $('[data-actions-form]');
    var confirmString = "{{ trans('forum::general.generic_confirm') }}";

    function setToggleStates() {
        checkboxes.prop('checked', toggle.is(':checked')).change();
    }

    function setSelectionStates() {
        checkboxes.each(function() {
            var tr = $(this).parents('tr');

            $(this).is(':checked') ? tr.addClass('active') : tr.removeClass('active');

            checkboxes.filter(':checked').length ? $('[data-bulk-actions]').removeClass('hidden') : $('[data-bulk-actions]').addClass('hidden');
        });
    }

    function setActionStates() {
        forms.each(function() {
            var form = $(this);
            var method = form.find('input[name=_method]');
            var selected = form.find('select[name=action] option:selected');
            var depends = form.find('[data-depends]');

            selected.each(function() {
                if ($(this).attr('data-method')) {
                    method.val($(this).data('method'));
                } else {
                    method.val('patch');
                }
            });

            depends.each(function() {
                (selected.val() == $(this).data('depends')) ? $(this).removeClass('hidden') : $(this).addClass('hidden');
            });
        });
    }

    setToggleStates();
    setSelectionStates();
    setActionStates();

    toggle.click(setToggleStates);
    checkboxes.change(setSelectionStates);
    actions.change(setActionStates);

    forms.submit(function() {
        var action = $(this).find('[data-actions]').find(':selected');

        if (action.is('[data-confirm]')) {
            return confirm(confirmString);
        }

        return true;
    });

    $('form[data-confirm]').submit(function() {
        return confirm(confirmString);
    });
    </script>

    @yield('footer')
</body>
</html>
