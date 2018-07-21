<?php

namespace App\Http\Controllers;

use App;
use App\Http\Requests;
use DB;
use Form;
use Illuminate\Http\Request;
use QrCode;
use Session;
use Shihjay2\OpenIDConnectUMAClient;
use URL;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show patient list.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['name'] = Session::get('owner');
        $data['title'] = 'My Patients';
        $data['content'] = 'No patients yet.';
        $data['searchbar'] = 'yes';
        $query = DB::table('oauth_rp')->where('type', '=', 'as')->get();
		if ($query) {
            $query1 = DB::table('rp_to_users')->where('username', '=', Session::get('username'))->get();
            if ($query1) {
                $data['content'] = '<form role="form"><div class="form-group"><input class="form-control" id="searchinput" type="search" placeholder="Filter Results..." /></div>';
    			$data['content'] .= '<div class="list-group searchlist">';
                $data['content'] .= '<a class="list-group-item row"><span class="col-sm-3"><strong>Name</strong></span><span class="col-sm-4"><strong>Resources</strong></span><span class="col-sm-3"><strong>Last Activity</strong></span><span class="col-sm-2"><strong>Actions</strong></span></a>';
                foreach ($query1 as $client_row) {
                    $client = DB::table('oauth_rp')->where('as_uri', '=', $client_row->as_uri)->first();
                    $link = '<span class="col-sm-4">';
                    $rs = DB::table('as_to_rs')->where('as_id', '=', $client_row->id)->get();
                    $rs_count=0;
                    if ($rs) {
                        foreach ($rs as $rs_row) {
                            $rs_uri = $rs_row->rs_uri;
                            if (strpos($rs_row->rs_uri, "/nosh") !== false) {
                                $rs_uri . '/uma_auth';
                            }
                            if ($rs_count > 0) {
                                $link .= '<br>';
                            }
                            $link .= '<h4><span class="label label-danger pnosh_link" nosh-link="' . $rs_uri . '">' . $rs_row->rs_name . '</span></h4>';
                            $rs_count++;
                        }
                    }
                    $link .= '</span>';
    				// $link = '<span class="label label-success pnosh_link" nosh-link="' . $client->as_uri . '/nosh/uma_auth">Patient Centered Health Record</span>';
                    if ($client->picture == '' || $client->picture == null) {
                        $picture = '<i class="fa fa-btn fa-user"></i>';
                    } else {
                        $picture = '<img src="' . $client->picture . '" height="30" width="30">';
                    }
                    // $timestamp = mt_rand(1, time());
                    // $activity = '<span class="col-sm-3">' . date("Y-m-d H:i:s", $timestamp) . '</span>';
                    $activity = '<span class="col-sm-3">' . date("Y-m-d H:i:s", $client->last_activity) . '</span>';
                    $remove = '<span class="col-sm-2"><span style="margin:10px"></span><i class="fa fa-minus fa-lg directory-remove" remove-val="' . $client->as_uri . '" title="Remove from My Patient List" style="cursor:pointer;"></i></span>';
                    $data['content'] .= '<a href="' . route('resources', [$client->id]) . '" class="list-group-item row"><span class="col-sm-3">' . $picture . $client->as_name . '</span>' . $link . $activity . $remove . '</a>';
    			}
    			$data['content'] .= '</div>';
            } else {
                $data['content'] = 'No connected patients yet.';
            }
		}
        $data['back'] = '<a href="' . URL::to('all_patients') . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-users"></i> All Patients</a>';
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function all_patients(Request $request)
    {
        $data['name'] = Session::get('owner');
        $data['title'] = 'All Participating Patients';
        $data['content'] = 'No patients yet.';
        $data['searchbar'] = 'yes';
        $query = DB::table('oauth_rp')->where('type', '=', 'as')->get();
		if ($query) {
            $data['content'] = '<form role="form"><div class="form-group"><input class="form-control" id="searchinput" type="search" placeholder="Filter Results..." /></div>';
			$data['content'] .= '<div class="list-group searchlist">';
            $data['content'] .= '<a class="list-group-item row"><span class="col-sm-3"><strong>Name</strong></span><span class="col-sm-4"><strong>Resources</strong></span><span class="col-sm-3"><strong>Last Activity</strong></span><span class="col-sm-2"><strong>Actions</strong></span></a>';
            foreach ($query as $client) {
                $link = '<span class="col-sm-4">';
                $rs = DB::table('as_to_rs')->where('as_id', '=', $client_row->id)->get();
                $rs_count=0;
                if ($rs) {
                    foreach ($rs as $rs_row) {
                        $rs_uri = $rs_row->rs_uri;
                        if (strpos($rs_row->rs_uri, "/nosh") !== false) {
                            $rs_uri . '/uma_auth';
                        }
                        if ($rs_count > 0) {
                            $link .= '<br>';
                        }
                        $link .= '<h4><span class="label label-danger pnosh_link" nosh-link="' . $rs_uri . '">' . $rs_row->rs_name . '</span></h4>';
                        $rs_count++;
                    }
                }
                $link .= '</span>';
				// $link = '<span class="label label-success pnosh_link" nosh-link="' . $client->as_uri . '/nosh/uma_auth">Patient Centered Health Record</span>';
                if ($client->picture == '' || $client->picture == null) {
                    $picture = '<i class="fa fa-btn fa-user"></i>';
                } else {
                    $picture = '<img src="' . $client->picture . '" height="30" width="30">';
                }
                // $timestamp = mt_rand(1, time());
                // $activity = '<span class="col-sm-3">' . date("Y-m-d H:i:s", $timestamp) . '</span>';
                $activity = '<span class="col-sm-3">' . date("Y-m-d H:i:s", $client->last_activity) . '</span>';
                // $add = '<span class="col-sm-1"><span style="margin:10px"></span><i class="fa fa-plus fa-lg directory-add" add-val="' . $client->as_uri . '" title="Add to My Patient List" style="cursor:pointer;"></i></span>';
                $add = '<span class="col-sm-2 directory-add" add-val="' . $client->as_uri . '" title="Add to My Patient List and Get Notifications for any Changes"><i class="fa fa-plus fa-lg" style="cursor:pointer;"></i> Follow</span>';
                $check = DB::table('rp_to_users')->where('username', '=', Session::get('username'))->where('as_uri', '=', $client->as_uri)->first();
                if ($check) {
                    $add = '';
                }
            	$data['content'] .= '<a href="' . route('resources', [$client->id]) . '" class="list-group-item row"><span class="col-sm-3">' . $picture . $client->as_name . '</span>' . $link . $activity . $add . '</a>';
			}
			$data['content'] .= '</div>';
		}
        $data['back'] = '<a href="' . URL::to('home') . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-user"></i> My Patients</a>';
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function add_patient(Request $request)
    {
        $user = DB::table('oauth_users')->where('username', '=', Session::get('username'))->first();
        $owner = DB::table('owner')->first();
        $message = $user->first_name . ' ' . $user->last_name . ' is now following you on the ' . $owner->org_name . ' Directory.';
        $action = [
            'notification' => $message,
            'add_clinician' => [
                'first_name' => $user->first_name,
                'last_name' =>  $user->last_name,
                'email' => $user->email,
                'npi' => $user->npi,
                'uport_id' => $user->uport_id
            ]
        ];
        $return = $this->as_push_notification($request->input('as_uri'), $action);
        if ($return['status'] == 'OK') {
            $data = [
                'as_uri' => $request->input('as_uri'),
                'username' => Session::get('username')
            ];
            DB::table('rp_to_users')->insert($data);
            $message = 'Patient added to My Patient list';
        } else {
            $message = $return['message'];
        }
        return $message;
    }

    public function remove_patient(Request $request)
    {
        DB::table('rp_to_users')->where('username', '=', Session::get('username'))->where('as_uri', '=', $request->input('as_uri'))->delete();
        return 'Patient removed from My Patient list';
    }

    public function reports(Request $request)
    {
        $data['name'] = Session::get('owner');
        $data['title'] = 'Reports';
        $data['content'] = 'This is where you can generate and review reports of connected patients.  The functionality is pending.';
        $data['searchbar'] = 'yes';
        return view('home', $data);
    }

    /**
     * Show the registered resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function resources(Request $request, $id)
    {
        // $client = DB::table('oauth_rp')->where('id', '=', $id)->first();
		// $data['title'] = $client->as_name . "'s Patient Summary";
		// $data['message_action'] = $request->session()->get('message_action');
		// $request->session()->forget('message_action');
		// $data['back'] = '<a href="' . route('home') . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-chevron-left"></i> My Patients</a>';
		// $data['content'] = '<div class="list-group">';
		// $link = '<span class="label label-success pnosh_link" nosh-link="' . $client->as_uri . '/nosh/uma_auth">Patient Centered Health Record</span>';
		// $data['content'] .= '<a href="' . $client->as_uri . '/nosh/uma_auth" target="_blank" class="list-group-item"><span style="margin:10px;">Patient Centered Health Record (pNOSH) for ' . $client->as_name . '</span>' . $link . '</a>';
		// $data['content'] .= '<a href="' . route('resource_view', ['Condition']) . '" class="list-group-item"><img src="https://cloud.noshchartingsystem.com/i-condition.png" height="20" width="20"><span style="margin:10px;">Conditions</span></a>';
		// $data['content'] .= '<a href="' . route('resource_view', ['MedicationStatement']) . '" class="list-group-item"><img src="https://cloud.noshchartingsystem.com/i-pharmacy.png" height="20" width="20"><span style="margin:10px;">Medication List</span></a>';
		// $data['content'] .= '<a href="' . route('resource_view', ['AllergyIntolerance']) . '" class="list-group-item"><img src="https://cloud.noshchartingsystem.com/i-allergy.png" height="20" width="20"><span style="margin:10px;">Allergy List</span></a>';
		// $data['content'] .= '<a href="' . route('resource_view', ['Immunization']) . '" class="list-group-item"><img src="https://cloud.noshchartingsystem.com/i-immunizations.png" height="20" width="20"><span style="margin:10px;">Immunizations</span></a>';
		// $data['content'] .= '</div>';
        // $data['searchbar'] = 'yes';
		// Session::put('current_client_id', $id);
		// return view('home', $data);

        $client = DB::table('oauth_rp')->where('id', '=', $id)->first();
		$data['title'] = $client->as_name . "'s Patient Summary";
		$data['message_action'] = Session::get('message_action');
		Session::forget('message_action');
        // Get access token from AS in anticipation for geting the RPT; if no refresh token before, get it too.
        $oidc = new OpenIDConnectUMAClient($client->as_uri, $client->client_id, $client->client_secret);
        $oidc->setSessionName('directory');
        $oidc->setUMA(true);
        $oidc->refreshToken($client->refresh_token);
        Session::put('uma_auth_access_token_nosh', $oidc->getAccessToken());
        $resources = $oidc->get_resources(true);
        Session::put('uma_auth_resources', $resources);
        $resources_array = $this->fhir_resources();
        $data['content'] = 'No resources available yet.';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $data['back'] = '<a href="' . Session::get('last_page') . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-chevron-left"></i> Back</a>';
        // Look for pNOSH link through registered client to mdNOSH Gateway
        $data['content'] = '<div class="list-group">';
        $i = 0;
        foreach($resources as $resource) {
            foreach ($resource['resource_scopes'] as $scope) {
                if (parse_url($scope, PHP_URL_HOST) !== null) {
                    $fhir_arr = explode('/', $scope);
                    $resource_type = array_pop($fhir_arr);
                    if (strpos($resource['name'], 'from Trustee') && $i == 0) {
                        array_pop($fhir_arr);
                        $data['content'] .= '<a href="' . implode('/', $fhir_arr) . '/uma_auth" target="_blank" class="list-group-item nosh-no-load"><span style="margin:10px;">Patient Centered Health Record (pNOSH) for ' . $patient->hieofone_as_name . '</span><span class="label label-success">Patient Centered Health Record</span></a>';
                        $i++;
                    }
                    break;
                }
            }
            $data['content'] .= '<a href="' . route('resource_view', [$resource['_id']]) . '" class="list-group-item"><i class="fa ' . $resources_array[$resource_type]['icon'] . ' fa-fw"></i><span style="margin:10px;">' . $resources_array[$resource_type]['name'] . '</span></a>';
        }
        $data['content'] .= '</div>';
        Session::put('uma_pid', $id);
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    /**
     * Show permissions for the resource.
     *
     * @param  int  $id - resource_set_id
     * @return \Illuminate\Http\Response
     *
     */
    public function resource_view(Request $request, $type)
    {
        $client = DB::table('oauth_rp')->where('id', '=', Session::get('current_client_id'))->first();
		Session::put('uma_uri', $client->as_uri);
		Session::put('uma_client_id', $client->client_id);
		Session::put('uma_client_secret', $client->client_secret);
        Session::put('uma_as_name', $client->as_name);
        $resources = Session::get('uma_auth_resources');
        $key = array_search($type, array_column($resources, '_id'));
        foreach ($resources[$key]['resource_scopes'] as $scope) {
            if (parse_url($scope, PHP_URL_HOST) !== null) {
                $fhir_arr = explode('/', $scope);
                $resource_type = array_pop($fhir_arr);
                Session::put('type', $resource_type);
                if (strpos($resources[$key]['name'], 'from Trustee')) {
                    if ($resource_type == 'Patient') {
                        $scope .= '?subject:Patient=1';
                    }
                    Session::put('uma_resource_uri', $scope);
                    break;
                } else {
                    Session::put('uma_resource_uri', $scope);
                }
                $name_arr = explode(' from ', $resources[$key]['name']);
                Session::put('fhir_name', $name_arr[1]);
            }
        }
        Session::save();
        if (Session::has('rpt')) {
            return redirect()->route('uma_api');
        } else {
            return redirect()->route('uma_aat');
        }
    }

    public function uma_aat(Request $request)
	{
        // Check if call comes from rqp_claims redirect
        if (Session::has('uma_permission_ticket')) {
            if (isset($_REQUEST["authorization_state"])) {
                if ($_REQUEST["authorization_state"] != 'claims_submitted') {
                    if ($_REQUEST["authorization_state"] == 'not_authorized') {
                        $text = 'You are not authorized to have the desired authorization data added.';
                    }
                    if ($_REQUEST["authorization_state"] == 'request_submitted') {
                        $text = 'The authorization server needs additional information in order to determine whether you are authorized to have this authorization data.';
                    }
                    if ($_REQUEST["authorization_state"] == 'need_info') {
                        $text = 'The authorization server requires intervention by the patient to determine whether authorization data can be added. Try again later after receiving any information from the patient regarding updates on your access status.';
                    }
                    $data['title'] = 'Error getting data';
                    $data['content'] = 'Description:<br>' . $text;
                    $data['name'] = Session::get('owner');
                    $data['back'] = '<a href="' . Session::get('last_page') . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-chevron-left"></i> Patient Summary</a>';
                    return view('home', $data);
                } else {
                    // Great - move on!
                    return redirect()->route('uma_api');
                }
            } else {
                Session::forget('uma_permission_ticket');
            }
        }
        $urlinit = Session::get('uma_resource_uri');
        $result = $this->fhir_request($urlinit,true);
        if (isset($result['error'])) {
            $data['title'] = 'Error getting data';
            $data['content'] = 'Description:<br>' . $result;
            $data['name'] = Session::get('owner');
            $data['back'] = '<a href="' . route('resources', [Session::get('current_client_id')]) . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-chevron-left"></i> Patient Summary</a>';
            return view('home', $data);
        }
        $permission_ticket = $result['ticket'];
        Session::put('uma_permission_ticket', $permission_ticket);
        Session::save();
        $as_uri = $result['as_uri'];
        $url = route('uma_aat');
        // Requesting party claims
        $oidc = new OpenIDConnectUMAClient(Session::get('uma_uri'), Session::get('uma_client_id'), Session::get('uma_client_secret'));
        $oidc->setRedirectURL($url);
        $oidc->rqp_claims($permission_ticket);
	}

    public function uma_api(Request $request)
	{
        $as_uri = Session::get('uma_uri');
        if (!Session::has('rpt')) {
            // Send permission ticket + AAT to Authorization Server to get RPT
            $permission_ticket = Session::get('uma_permission_ticket');
            $client_id = Session::get('uma_client_id');
            $client_secret = Session::get('uma_client_secret');
            $url = route('uma_api');
            $oidc = new OpenIDConnectUMAClient($as_uri, $client_id, $client_secret);
            $oidc->setSessionName('directory');
            $oidc->setAccessToken(Session::get('uma_auth_access_token_nosh'));
            $oidc->setRedirectURL($url);
            $result1 = $oidc->rpt_request($permission_ticket);
            if (isset($result1['error'])) {
                // error - return something
                if ($result1['error'] == 'expired_ticket' || $result1['error'] == 'invalid_grant') {
                    // Session::forget('uma_aat');
                    Session::forget('uma_permission_ticket');
                    return redirect()->route('uma_aat');
                } else {
                    $data['title'] = 'Error getting data';
                    $data['content'] = 'Description:<br>' . $result1['error'];
                    $data['back'] = '<a href="' . route('resources', [Session::get('current_client_id')]) . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-chevron-left"></i> Patient Summary</a>';
                    return view('home', $data);
                }
            }
            if (isset($result1['errors'])) {
                $data['title'] = 'Error getting data';
                $data['content'] = 'Description:<br>' . $result1['errors'];
                $data['back'] = '<a href="' . route('resources', [Session::get('current_client_id')]) . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-chevron-left"></i> Patient Summary</a>';
                return view('home', $data);
            }
            $rpt = $result1['access_token'];
            // Save RPT in session in case for future calls in same session
            Session::put('rpt', $rpt);
            Session::save();
        } else {
            $rpt = Session::get('rpt');
        }
        // Contact resource again, now with RPT
        $urlinit = Session::get('uma_resource_uri');
        $result3 = $this->fhir_request($urlinit,false,$rpt);
        if (isset($result3['ticket'])) {
            // New permission ticket issued, expire rpt session
            Session::forget('rpt');
            Session::put('uma_permission_ticket', $result3['ticket']);
            Session::save();
            // Get new RPT
            return redirect()->route('uma_api');
        }
        // Format the result into a nice display
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $title_array = $this->fhir_resources();
        $data['back'] = '<a href="' . route('resources', [Session::get('current_client_id')]) . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-chevron-left"></i> Patient Summary</a>';
        $data['content'] = 'None.';
        $data['title'] = $title_array[Session::get('type')]['name'] . ' for ' . Session::get('uma_as_name');
        if (isset($result3['total'])) {
            if ($result3['total'] != '0') {
                $data = $this->fhir_display($result3, Session::get('type'), $data);
            }
        }
        $data['searchbar'] = 'yes';
        return view('home', $data);
	}

    public function uma_aat_search(Request $request)
	{
		// Check if call comes from rqp_claims redirect
		if (Session::has('uma_aat') && Session::has('uma_permission_ticket')) {
			if (isset($_REQUEST["authorization_state"])) {
				if ($_REQUEST["authorization_state"] != 'claims_submitted') {
					if ($_REQUEST["authorization_state"] == 'not_authorized') {
						$text = 'You are not authorized to have the desired authorization data added.';
					}
					if ($_REQUEST["authorization_state"] == 'request_submitted') {
						$text = 'The authorization server needs additional information in order to determine whether you are authorized to have this authorization data.';
					}
					if ($_REQUEST["authorization_state"] == 'need_info') {
						$text = 'The authorization server requires intervention by the patient to determine whether authorization data can be added. Try again later after receiving any information from the patient regarding updates on your access status.';
					}
					return $text;
				} else {
					// Great - move on!
					return redirect()->route('uma_api_search');
				}
			} else {
				Session::forget('uma_aat');
				Session::forget('uma_permission_ticket');
			}
		}
		// Get AAT
		$url_array = ['/nosh/oidc','/nosh/fhir/oidc'];
        $uma_search_count = Session::get('uma_search_count');
        if (empty($uma_search_count)) {
            Session::put('uma_search_complete', 'true');
            Session::forget('uma_search_count');
            return redirect()->route('search');
        }
        $uma_as_uri = $uma_search_count[0];
        $client = DB::table('oauth_rp')->where('as_uri', '=', $uma_search_count[0])->first();
        unset($uma_search_count[0]);
        Session::put('uma_search_count', $uma_search_count);
        Session::put('current_client_id', $client->id);
        Session::put('uma_uri', $client->as_uri);
        Session::put('uma_client_id', $client->client_id);
        Session::put('uma_client_secret', $client->client_secret);
        Session::save();
		$oidc = new OpenIDConnectUMAClient(Session::get('uma_uri'), Session::get('uma_client_id'), Session::get('uma_client_secret'));
		$oidc->requestAAT();
		Session::put('uma_aat', $oidc->getAccessToken());
		// Get permission ticket
        $urlinit = Session::get('uma_uri') . '/nosh/fhir/Patient?subject:Patient=1';
		$result = $this->fhir_request($urlinit,true);
		if (isset($result['error'])) {
			// error - return something
			return $result;
		}
		$permission_ticket = $result['ticket'];
		Session::put('uma_permission_ticket', $permission_ticket);
		Session::save();
		$as_uri = $result['as_uri'];
		$url = route('uma_aat_search');
		// Requesting party claims
		$oidc->setRedirectURL($url);
		$oidc->rqp_claims($permission_ticket);
	}

    public function uma_api_search(Request $request)
	{
		$as_uri = Session::get('uma_uri');
		if (!Session::has('rpt')) {
			// Send permission ticket + AAT to Authorization Server to get RPT
			$permission_ticket = Session::get('uma_permission_ticket');
			$client_id = Session::get('uma_client_id');
			$client_secret = Session::get('uma_client_secret');
			$url = route('uma_api_search');
			$oidc = new OpenIDConnectUMAClient($as_uri, $client_id, $client_secret);
			$oidc->setAccessToken(Session::get('uma_aat'));
			$oidc->setRedirectURL($url);
			$result1 = $oidc->rpt_request($permission_ticket);
			if (isset($result1['error'])) {
				// error - return something
				if ($result1['error'] == 'expired_ticket') {
				    Session::forget('uma_aat');
					Session::forget('uma_permission_ticket');
					return redirect()->route('uma_aat_search');
				} else {
                    if (Session::has('uma_errors')) {
                        $uma_errors = Session::get('uma_errors') . 'Error getting data from ' . $as_uri;
                        Session::put('uma_error', $uma_errors);
                    } else {
                        $uma_errors = 'Error getting data from ' . $as_uri;
                        Session::put('uma_error', $uma_errors);
                    }
					return redirect()->route('uma_aat_search');
				}
			}
			$rpt = $result1['rpt'];
			// Save RPT in session in case for future calls in same session
			Session::put('rpt', $rpt);
			Session::save();
		} else {
			$rpt = Session::get('rpt');
		}
        // Save RPT to indicate successful connection by user for future use
        $rpt_data['rpt'] = $rpt;
        $rpt_query = DB::table('rp_to_users')->where('username', '=', Session::get('username'))->where('as_uri', '=', Session::get('uma_uri'))->first();
        if ($rpt_query) {
            DB::table('rp_to_users')->where('username', '=', Session::get('username'))->where('as_uri', '=', Session::get('uma_uri'))->update($rpt_data);
        } else {
            $rpt_data['username'] = Session::get('username');
            $rpt_data['as_uri'] = Session::get('uma_uri');
            DB::table('rp_to_users')->insert($rpt_data);
        }
        DB::table('rp_to_users')->where('username', '=', Session::get('username'))->where('as_uri', '=', Session::get('uma_uri'))->update($rpt_data);
		// Contact pNOSH again, now with RPT
        $query_types = [
            'Condition?subject:Patient=1&code=~',
            'MedicationStatement?subject:Patient=1&medication=~',
            'AllergyIntolerance?subject:Patient=1&substance=~',
            'Immunization?subject:Patient=1&vaccineCode=~'
        ];
        foreach ($query_types as $query_type) {
            $urlinit = $as_uri . '/nosh/fhir/' . $query_type . urlencode(Session::get('search_term'));
    		$result3 = $this->fhir_request($urlinit,false,$rpt);
            if (isset($result3['ticket'])) {
    			// New permission ticket issued, expire rpt session
    			Session::forget('rpt');
    			Session::put('uma_permission_ticket', $result3['ticket']);
    			Session::save();
    			// Get new RPT and start over
    			return redirect()->route('uma_api_search');
    		}
            // Save results in session
            $id = Session::get('current_client_id');
            if (isset($result3['total'])) {
                if ($result3['total'] != '0') {
                    if (Session::has('uma_search_arr')) {
                        $uma_search_arr = Session::get('uma_search_arr');
                    } else {
                        $uma_search_arr[$id] = [];
                    }
                    foreach ($result3['entry'] as $entry) {
                        $uma_search_arr[$id][] = $entry['resource']['text']['div'];
                    }
                    Session::put('uma_search_arr', $uma_search_arr);
                }
            }
        }
        return redirect()->route('search');
	}

    // Needs work

    public function uma_list(Request $request)
    {
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'url' => 'required|url'
            ]);
            // Register to HIE of One AS - confirm it
            $this->clean_uma_sessions();
            $test_uri = rtrim($request->input('url'), '/') . "/.well-known/uma2-configuration";
            $url_arr = parse_url($test_uri);
            if (!isset($url_arr['scheme'])) {
                $test_uri = 'https://' . $test_uri;
            }
            $ch = curl_init($test_uri);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if($httpcode>=200 && $httpcode<302){
                $url_arr = parse_url($test_uri);
                $as_uri = $url_arr['scheme'] . '://' . $url_arr['host'];
            } else {
                return redirect()->back()->withErrors(['url' => 'Try again, URL is invalid, httpcode: ' . $httpcode . ', URL: ' . $request->input('url')]);
            }
            $practice = DB::table('practiceinfo')->where('practice_id', '=', Session::get('practice_id'))->first();
            $client_name = 'mdNOSH - ' . $practice->practice_name;
            $url1 = route('uma_auth');
            $oidc = new OpenIDConnectUMAClient($as_uri);
            $oidc->setClientName($client_name);
            $oidc->setSessionName('nosh');
            $oidc->addRedirectURLs($url1);
            $oidc->addRedirectURLs(route('uma_api'));
            $oidc->addRedirectURLs(route('uma_aat'));
            $oidc->addRedirectURLs(route('uma_register_auth'));
            // $oidc->addRedirectURLs(route('uma_resources'));
            // $oidc->addRedirectURLs(route('uma_resource_view'));
            $oidc->addScope('openid');
            $oidc->addScope('email');
            $oidc->addScope('profile');
            $oidc->addScope('address');
            $oidc->addScope('phone');
            $oidc->addScope('offline_access');
            $oidc->addScope('uma_authorization');
            $oidc->setLogo('https://cloud.noshchartingsystem.com/SAAS-Logo.jpg');
            $oidc->setClientURI(str_replace('/uma_auth', '', $url1));
            $oidc->setUMA(true);
            $oidc->register();
            $client_id = $oidc->getClientID();
            $client_secret = $oidc->getClientSecret();
            $data1 = [
                'hieofone_as_client_id' => $client_id,
                'hieofone_as_client_secret' => $client_secret,
                'hieofone_as_url' => $as_uri
            ];
            Session::put('uma_add_patient', $data1);
            Session::save();
            return redirect()->route('uma_resource_view', ['new']);
        } else {
            $items[] = [
                'name' => 'url',
                'label' => "URL of Patient's Authorization Server",
                'type' => 'text',
                'required' => true,
                'default_value' => null
            ];
            $form_array = [
                'form_id' => 'uma_list_form',
                'action' => route('uma_list'),
                'items' => $items,
                'save_button_label' => 'Add New Patient'
            ];
            $data['panel_header'] = 'FHIR Connected Patients';
            $data['content'] = $this->form_build($form_array);
            $query = DB::table('demographics')->where('hieofone_as_url', '!=', '')->orWhere('hieofone_as_url', '!=', null)->get();
            if ($query->count()) {
                $list_array = [];
                foreach ($query as $row) {
                    $arr = [];
                    $dob = date('m/d/Y', strtotime($row->DOB));
                    $arr['label'] = $row->lastname . ', ' . $row->firstname . ' (DOB: ' . $dob . ') (ID: ' . $row->pid . ')';
                    $arr['view'] = route('uma_resources', [$row->pid]);
                    $arr['jump'] = $row->hieofone_as_url . '/nosh/uma_auth';
                    $list_array[] = $arr;
                }
                $data['content'] .= $this->result_build($list_array, 'fhir_list');
            }
            $data['message_action'] = Session::get('message_action');
            Session::forget('message_action');
            $data['assets_js'] = $this->assets_js();
            $data['assets_css'] = $this->assets_css();
            return view('core', $data);
        }
    }

    public function uma_register_auth(Request $request)
    {
        $oidc = new OpenIDConnectUMAClient(Session::get('uma_uri'), Session::get('uma_client_id'), Session::get('uma_client_secret'));
        $oidc->setSessionName('directory');
        $oidc->setRedirectURL(route('uma_register_auth'));
        $oidc->setUMA(true);
        $oidc->setUMAType('client');
        $oidc->authenticate();
        if (Session::has('uma_add_patient')) {
            $data = Session::get('uma_add_patient');
            $data['hieofone_as_refresh_token'] = $oidc->getRefreshToken();
            Session::put('uma_add_patient', $data);
            $resources = $oidc->get_resources(true);
            if (count($resources) > 0) {
                // Get the access token from the AS in anticipation for the RPT
                Session::put('uma_auth_access_token_directory', $oidc->getAccessToken());
                Session::put('uma_auth_resources', $resources);
                $patient_urls = [];
                foreach ($resources as $resource) {
                    // Assume there is always a Trustee pNOSH resource and save it
                    if (strpos($resource['name'], 'from Trustee')) {
                        foreach ($resource['resource_scopes'] as $scope) {
                            $scope_arr = explode('/', $scope);
                            if (in_array('Patient', $scope_arr)) {
                                Session::put('patient_uri', $scope . '?subject:Patient=1');
                            }
                            if (in_array('MedicationStatement', $scope_arr)) {
                                Session::put('medicationstatement_uri', $scope);
                            }
                        }
                    }
                }
                return redirect()->route('uma_aat');
            } else {
                Session::put('message_action', 'Error - the authorization you were trying to connect to has no resources.');
                Session::forget('uma_add_patient');
                return redirect()->route('uma_list');
            }
        } else {
            $pid = Session::get('uma_resources_start');
            Session::forget('uma_resources_start');
            return redirect()->route('uma_resources', [$pid]);
        }
    }

    public function uma_resources(Request $request, $id)
    {
        $patient = DB::table('demographics')->where('pid', '=', $id)->first();
        // Get access token from AS in anticipation for geting the RPT; if no refresh token before, get it too.
        if ($patient->hieofone_as_refresh_token == '' || $patient->hieofone_as_refresh_token == null) {
            Session::put('uma_resources_start', $id);
            return redirect()->route('uma_register_auth');
        }
        $oidc = new OpenIDConnectUMAClient($patient->hieofone_as_url, $patient->hieofone_as_client_id, $patient->hieofone_as_client_secret);
        $oidc->setSessionName('nosh');
        $oidc->setUMA(true);
        $oidc->refreshToken($patient->hieofone_as_refresh_token);
        Session::put('uma_auth_access_token_nosh', $oidc->getAccessToken());
        $resources = $oidc->get_resources(true);
        Session::put('uma_auth_resources', $resources);
        $resources_array = $this->fhir_resources();
        $data['panel_header'] = $patient->firstname . ' ' . $patient->lastname . "'s Patient Summary";
        $data['content'] = 'No resources available yet.';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $dropdown_array = [];
        $items = [];
        $items[] = [
            'type' => 'item',
            'label' => 'Back',
            'icon' => 'fa-chevron-left',
            'url' => route('uma_list')
        ];
        $dropdown_array['items'] = $items;
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        // Look for pNOSH link through registered client to mdNOSH Gateway
        $data['content'] = '<div class="list-group">';
        $i = 0;
        foreach($resources as $resource) {
            foreach ($resource['resource_scopes'] as $scope) {
                if (parse_url($scope, PHP_URL_HOST) !== null) {
                    $fhir_arr = explode('/', $scope);
                    $resource_type = array_pop($fhir_arr);
                    if (strpos($resource['name'], 'from Trustee') && $i == 0) {
                        array_pop($fhir_arr);
                        $data['content'] .= '<a href="' . implode('/', $fhir_arr) . '/uma_auth" target="_blank" class="list-group-item nosh-no-load"><span style="margin:10px;">Patient Centered Health Record (pNOSH) for ' . $patient->hieofone_as_name . '</span><span class="label label-success">Patient Centered Health Record</span></a>';
                        $i++;
                    }
                    break;
                }
            }
            $data['content'] .= '<a href="' . route('uma_resource_view', [$resource['_id']]) . '" class="list-group-item"><i class="fa ' . $resources_array[$resource_type]['icon'] . ' fa-fw"></i><span style="margin:10px;">' . $resources_array[$resource_type]['name'] . '</span></a>';
        }
        $data['content'] .= '</div>';
        Session::put('uma_pid', $id);
        Session::put('last_page', $request->fullUrl());
        if ($id == Session::get('pid')) {
            $data = array_merge($data, $this->sidebar_build('chart'));
            $data['assets_js'] = $this->assets_js('chart');
            $data['assets_css'] = $this->assets_css('chart');
        } else {
            $data['assets_js'] = $this->assets_js();
            $data['assets_css'] = $this->assets_css();
        }
        return view('core', $data);
    }

    public function uma_resource_view(Request $request, $type)
    {
        if (Session::has('uma_add_patient')) {
            $data = Session::get('uma_add_patient');
            Session::put('uma_uri', $data['hieofone_as_url']);
            Session::put('uma_client_id', $data['hieofone_as_client_id']);
            Session::put('uma_client_secret', $data['hieofone_as_client_secret']);
            Session::put('type', 'Patient');
        } else {
            $patient = DB::table('demographics')->where('pid', '=', Session::get('uma_pid'))->first();
            Session::put('uma_uri', $patient->hieofone_as_url);
            Session::put('uma_client_id', $patient->hieofone_as_client_id);
            Session::put('uma_client_secret', $patient->hieofone_as_client_secret);
            Session::put('uma_as_name', $patient->hieofone_as_name);
            $resources = Session::get('uma_auth_resources');
            $key = array_search($type, array_column($resources, '_id'));
            foreach ($resources[$key]['resource_scopes'] as $scope) {
                if (parse_url($scope, PHP_URL_HOST) !== null) {
                    $fhir_arr = explode('/', $scope);
                    $resource_type = array_pop($fhir_arr);
                    Session::put('type', $resource_type);
                    if (strpos($resources[$key]['name'], 'from Trustee')) {
                        if ($resource_type == 'Patient') {
                            $scope .= '?subject:Patient=1';
                        }
                        Session::put('uma_resource_uri', $scope);
                        break;
                    } else {
                        Session::put('uma_resource_uri', $scope);
                    }
                    $name_arr = explode(' from ', $resources[$key]['name']);
                    Session::put('fhir_name', $name_arr[1]);
                }
            }
        }
        Session::save();
        if (Session::has('rpt')) {
            return redirect()->route('uma_api');
        } else {
            if (Session::has('uma_add_patient')) {
                return redirect()->route('uma_register_auth');
            } else {
                return redirect()->route('uma_aat');
            }
        }
    }

    /**
     * Client authorization pages.
     *
     * @param  int  $id - client_id
     * @return \Illuminate\Http\Response
     *
     */
    public function clients(Request $request)
    {
        $data['name'] = Session::get('owner');
        $data['title'] = 'Authorized Clients';
        $data['content'] = 'No authorized clients.';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $oauth_scope_array = [
            'openid' => 'OpenID Connect',
            'uma_authorization' => 'Access Resources',
            'uma_protection' => 'Register Resources'
        ];
        $query = DB::table('oauth_clients')->where('authorized', '=', 1)->get();
        if ($query) {
            $data['content'] = '<p>Clients are outside apps that work on behalf of users to access your resources.  You can authorize or unauthorized them at any time.</p><table class="table table-striped"><thead><tr><th>Client Name</th><th>Permissions</th><th></th></thead><tbody>';
            foreach ($query as $client) {
                $data['content'] .= '<tr><td>' . $client->client_name . '</td><td>';
                $scope_array = explode(' ', $client->scope);
                $i = 0;
                foreach ($scope_array as $scope) {
                    if (array_key_exists($scope, $oauth_scope_array)) {
                        if ($i > 0) {
                            $data['content'] .= ', ';
                        }
                        $data['content'] .= $oauth_scope_array[$scope];
                        $i++;
                    }
                }
                $data['content'] .= '</td><td><a href="' . route('authorize_client_disable', [$client->client_id]) . '" class="btn btn-primary" role="button">Unauthorize</a></td></tr>';
            }
        }
        return view('home', $data);
    }

    public function consents_resource_server(Request $request)
    {
        $data['name'] = Session::get('owner');
        $data['title'] = 'Resource Registration Consent';
        $data['message_action'] = Session::get('message_action');
        $data['back'] = '<a href="' . URL::to('resources') . '/' . Session::get('current_client_id') . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-chevron-left"></i> My Resources</a>';
        Session::forget('message_action');
        $query = DB::table('oauth_clients')->where('client_id', '=', Session::get('current_client_id'))->first();
        $scopes_array = explode(' ', $query->scope);
        if ($query->logo_uri == '') {
            $data['content'] = '<div><i class="fa fa-child fa-5x" aria-hidden="true" style="margin:20px;text-align: center;"></i></div>';
        } else {
            $data['content'] = '<div><img src="' . $query->logo_uri . '" style="margin:20px;text-align: center;"></div>';
        }
        $data['content'] .= '<h3>Resource Registration Consent for ' . $query->client_name . '</h3>';
        $data['content'] .= '<p>By clicking Allow, you consent to sharing your information on ' . $query->client_name . ' according to the policies selected below. You can revoke consent or change your policies for ' . $query->client_name . ' at any time using the My Resources page.  Parties requesting access to your information will be listed on the My Clients page where their access can also be revoked or changed.   Your sharing defaults can be changed on the My Policies page.</p>';
        $data['content'] .= '<input type="hidden" name="client_id" value="' . $query->client_id . '"/>';
        $data['client'] = $query->client_name;
        $data['login_direct'] = '';
        $data['login_md_nosh'] = '';
        $data['any_npi'] = '';
        $data['login_google'] = '';
        if ($query->consent_login_direct == 1) {
            $data['login_direct'] = 'checked';
        }
        if ($query->consent_login_md_nosh == 1) {
            $data['login_md_nosh'] = 'checked';
        }
        if ($query->consent_any_npi == 1) {
            $data['any_npi'] = 'checked';
        }
        if ($query->consent_login_google == 1) {
            $data['login_google'] = 'checked';
        }
        return view('rs_authorize', $data);
    }

    public function authorize_resource_server(Request $request)
    {
        $data['name'] = Session::get('owner');
        $data['title'] = 'Resource Registration Consent';
        $data['content'] = 'No resource servers pending authorization.';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $query = DB::table('oauth_clients')->where('client_id', '=', Session::get('oauth_client_id'))->first();
        if ($query) {
            $scopes_array = explode(' ', $query->scope);
            if ($query->logo_uri == '') {
                $data['content'] = '<div><i class="fa fa-child fa-5x" aria-hidden="true" style="margin:20px;text-align: center;"></i></div>';
            } else {
                $data['content'] = '<div><img src="' . $query->logo_uri . '" style="margin:20px;text-align: center;"></div>';
            }
            $data['content'] .= '<h3>Resource Registration Consent for ' . $query->client_name . '</h3>';
            $data['content'] .= '<p>By clicking Allow, you consent to sharing your information on ' . $query->client_name . ' according to the policies selected below. You can revoke consent or change your policies for ' . $query->client_name . ' at any time using the My Resources page.  Parties requesting access to your information will be listed on the My Clients page where their access can also be revoked or changed.   Your sharing defaults can be changed on the My Policies page.</p>';
            $data['content'] .= '<input type="hidden" name="client_id" value="' . $query->client_id . '"/>';
            $data['client'] = $query->client_name;
            $query1 = DB::table('owner')->first();
            $data['login_direct'] = '';
            $data['login_md_nosh'] = '';
            $data['any_npi'] = '';
            $data['login_google'] = '';
            if ($query1->login_direct == 1) {
                $data['login_direct'] = 'checked';
            }
            if ($query1->login_md_nosh == 1) {
                $data['login_md_nosh'] = 'checked';
            }
            if ($query1->any_npi == 1) {
                $data['any_npi'] = 'checked';
            }
            if ($query1->login_google == 1) {
                $data['login_google'] = 'checked';
            }
            return view('rs_authorize', $data);
        } else {
            return redirect()->route('home');
        }
    }

    public function rs_authorize_action(Request $request)
    {
        if ($request->input('submit') == 'allow') {
            $data['consent_login_direct'] = 0;
            $data['consent_login_md_nosh'] = 0;
            $data['consent_any_npi'] = 0;
            $data['consent_login_google'] = 0;
            $types = [];
            if ($request->input('consent_login_direct') == 'on') {
                $data['consent_login_direct'] = 1;
                $types[] = 'login_direct';
            }
            if ($request->input('consent_login_md_nosh') == 'on') {
                $data['consent_login_md_nosh'] = 1;
                $types[] = 'login_md_nosh';
            }
            if ($request->input('consent_any_npi') == 'on') {
                $data['consent_any_npi'] = 1;
                $types[] = 'any_npi';
            }
            if ($request->input('consent_login_google') == 'on') {
                $data['consent_login_google'] = 1;
                $types[] = 'login_google';
            }
            $data['authorized'] = 1;
            DB::table('oauth_clients')->where('client_id', '=', $request->input('client_id'))->update($data);
            $client = DB::table('oauth_clients')->where('client_id', '=', $request->input('client_id'))->first();
            $this->group_policy($request->input('client_id'), $types, 'update');
            if (Session::get('oauth_response_type') == 'code') {
                $user_array = explode(' ', $client->user_id);
                $user_array[] = Session::get('username');
                $data['user_id'] = implode(' ', $user_array);
                DB::table('oauth_clients')->where('client_id', '=', $request->input('client_id'))->update($data);
                Session::put('is_authorized', 'true');
            }
            Session::put('message_action', 'You just authorized a resource server named ' . $client->client_name);
        } else {
            Session::put('message_action', 'You just unauthorized a resource server named ' . $client->client_name);
            if (Session::get('oauth_response_type') == 'code') {
                Session::put('is_authorized', 'false');
            } else {
                $data1['authorized'] = 0;
                DB::table('oauth_clients')->where('client_id', '=', $request->input('client_id'))->update($data1);
                $this->group_policy($request->input('client_id'), $types, 'delete');
            }
        }
        if (Session::get('oauth_response_type') == 'code') {
            return redirect()->route('authorize');
        } else {
            return redirect()->route('resources', ['id' => Session::get('current_client_id')]);
        }
    }

    public function authorize_client(Request $request)
    {
        $data['name'] = Session::get('owner');
        $data['title'] = 'Clients Pending Authorization';
        $data['content'] = 'No clients pending authorization.';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $oauth_scope_array = [
            'openid' => 'OpenID Connect',
            'uma_authorization' => 'Access Resources',
            'uma_protection' => 'Register Resources'
        ];
        $query = DB::table('oauth_clients')->where('authorized', '=', 0)->get();
        if ($query) {
            $data['content'] = '<p>Clients are outside apps that work on behalf of users to access your resources.  You can authorize or unauthorized them at any time.</p><table class="table table-striped"><thead><tr><th>Client Name</th><th>Permissions Requested</th><th></th></thead><tbody>';
            foreach ($query as $client) {
                $data['content'] .= '<tr><td>' . $client->client_name . '</td><td>';
                $scope_array = explode(' ', $client->scope);
                $i = 0;
                foreach ($scope_array as $scope) {
                    if (array_key_exists($scope, $oauth_scope_array)) {
                        if ($i > 0) {
                            $data['content'] .= ', ';
                        }
                        $data['content'] .= $oauth_scope_array[$scope];
                        $i++;
                    }
                }
                $data['content'] .= '</td><td><a href="' . route('authorize_client_action', [$client->client_id]) . '" class="btn btn-primary" role="button">Authorize</a>';
                $data['content'] .= ' <a href="' . route('authorize_client_disable', [$client->client_id]) . '" class="btn btn-primary" role="button">Deny</a></td></tr>';
            }
        }
        return view('home', $data);
    }

    public function authorize_client_action(Request $request, $id)
    {
        $data['authorized'] = 1;
        DB::table('oauth_clients')->where('client_id', '=', $id)->update($data);
        $query = DB::table('oauth_clients')->where('client_id', '=', $id)->first();
        Session::put('message_action', 'You just authorized a client named ' . $query->client_name);
        return redirect()->route('authorize_client');
    }

    public function authorize_client_disable(Request $request, $id)
    {
        $query = DB::table('oauth_clients')->where('client_id', '=', $id)->first();
        Session::put('message_action', 'You just unauthorized a client named ' . $query->client_name);
        DB::table('oauth_clients')->where('client_id', '=', $id)->delete();
        return redirect()->route('authorize_client');
    }

    public function users(Request $request)
    {
        $data['name'] = Session::get('owner');
        $data['title'] = 'Authorized Users';
        $data['content'] = 'No authorized users.';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $oauth_scope_array = [
            'openid' => 'OpenID Connect',
            'uma_authorization' => 'Access Resources',
            'uma_protection' => 'Register Resources'
        ];
        $query = DB::table('oauth_users')->where('password', '!=', 'Pending')->get();
        $owner = DB::table('owner')->first();
        $proxies = DB::table('owner')->where('sub', '!=', $owner->sub)->get();
        $proxy_arr = [];
        if ($proxies) {
            foreach ($proxies as $proxy_row) {
                $proxy_arr[] = $proxy_row->sub;
            }
        }
        if ($query) {
            $data['content'] = '<p>Users have access to your resources.  You can authorize or unauthorized them at any time.</p><table class="table table-striped"><thead><tr><th>Name</th><th>Email</th><th>NPI</th><th></th><th></th></thead><tbody>';
            foreach ($query as $user) {
                $data['content'] .= '<tr><td>' . $user->first_name . ' ' . $user->last_name . '</td><td>' . $user->email . '</td><td>';
                if ($user->npi !== null && $user->npi !== '') {
                    $data['content'] .= $user->npi;
                }
                $data['content'] .= '</td><td><a href="' . route('authorize_user_disable', [$user->username]) . '" class="btn btn-primary" role="button">Unauthorize</a></td>';
                if ($user->sub == $owner->sub) {
                    $data['content'] .= '<td></td>';
                } else {
                    if (Session::get('sub') == $owner->sub) {
                        if (in_array($user->sub, $proxy_arr)) {
                            $data['content'] .= '<td><a href="' . route('proxy_remove', [$user->sub]) . '" class="btn btn-danger" role="button">Remove As Admin</a></td>';
                        } else {
                            $data['content'] .= '<td><a href="' . route('proxy_add', [$user->sub]) . '" class="btn btn-success" role="button">Add As Admin</a></td>';
                        }
                    } else {
                        $data['content'] .= '<td></td>';
                    }
                }
                $data['content'] .= '</tr>';
            }
        }
        return view('home', $data);
    }

    public function authorize_user(Request $request)
    {
        $data['name'] = Session::get('owner');
        $data['title'] = 'Users Pending Authorization';
        $data['content'] = 'No users pending authorization.';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $oauth_scope_array = [
            'openid' => 'OpenID Connect',
            'uma_authorization' => 'Access Resources',
            'uma_protection' => 'Register Resources'
        ];
        $query = DB::table('oauth_users')->where('password', '=', 'Pending')->get();
        if ($query) {
            $data['content'] = '<p>Users have access to your resources.  You can authorize or unauthorized them at any time.</p><table class="table table-striped"><thead><tr><th>Name</th><th>Email</th><th>NPI</th><th></th></thead><tbody>';
            foreach ($query as $user) {
                $data['content'] .= '<tr><td>' . $user->first_name . ' ' . $user->last_name . '</td><td>';
                if ($user->email !== null && $user->email !== '') {
                    $data['content'] .= $user->email;
                }
                $data['content'] .= '</td><td>';
                if ($user->npi !== null && $user->npi !== '') {
                    $data['content'] .= $user->npi;
                }
                $data['content'] .= '</td><td>';
                $data['content'] .= '</td><td><a href="' . route('authorize_user_action', [$user->username]) . '" class="btn btn-primary" role="button">Authorize</a>';
                $data['content'] .= ' <a href="' . route('authorize_user_disable', [$user->username]) . '" class="btn btn-primary" role="button">Deny</a></td></tr>';
            }
        }
        return view('home', $data);
    }

    public function authorize_user_action(Request $request, $id)
    {
        $data['password'] = sha($id);
        DB::table('oauth_users')->where('username', '=', $id)->update($data);
        $query = DB::table('oauth_users')->where('username', '=', $id)->first();
        $owner_query = DB::table('owner')->first();
        $data1['message_data'] = 'You have been authorized access to HIE of One Authorizaion Server for ' . $owner_query->firstname . ' ' . $owner_query->lastname;
        $data1['message_data'] .= 'Go to ' . route('login') . '/ to login.';
        $title = 'Access to HIE of One';
        $to = $query->email;
        $this->send_mail('auth.emails.generic', $data1, $title, $to);
        Session::put('message_action', 'You just authorized a user named ' . $query->first_name . ' ' . $query->last_name);
        return redirect()->route('authorize_user');
    }

    public function authorize_user_disable(Request $request, $id)
    {
        $query = DB::table('oauth_users')->where('username', '=', $id)->first();
        Session::put('message_action', 'You just unauthorized a user named ' . $query->first_name . ' ' . $query->last_name);
        DB::table('oauth_users')->where('username', '=', $id)->delete();
        DB::table('users')->where('name', '=', $id)->delete();
        return redirect()->route('authorize_user');
    }

    public function proxy_add(Request $request, $sub)
    {
        $query = DB::table('oauth_users')->where('sub', '=', $sub)->first();
        $data = [
            'lastname' => $query->last_name,
            'firstname' => $query->first_name,
            'sub' => $sub
        ];
        DB::table('owner')->insert($data);
        Session::put('message_action', 'You just added ' . $query->first_name . ' ' . $query->last_name . ' as a proxy for you');
        return redirect()->route('users');
    }

    public function proxy_remove(Request $request, $sub)
    {
        $owner = DB::table('owner')->first();
        if ($sub !== $owner->sub) {
            DB::table('owner')->where('sub', '=', $sub)->delete();
            Session::put('message_action', 'You just removed ' . $query->first_name . ' ' . $query->last_name . ' as a proxy for you');
        } else {
            Session::put('message_action', 'You cannot remove yourself as the owner.');
        }
        return redirect()->route('users');
    }

    public function add_owner(Request $request)
    {
        $owner = DB::table('owner')->first();
        $data['name'] = Session::get('owner');
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'email' => 'required|unique:users,email',
                'first_name' => 'required',
                'last_name' => 'required'
            ]);
            // Check if
            $access_lifetime = App::make('oauth2')->getConfig('access_lifetime');
            $code = $this->gen_secret();
            $data1 = [
                'email' => $request->input('email'),
                'expires' => date('Y-m-d H:i:s', time() + $access_lifetime),
                'code' => $code,
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'owner' => 'yes'
            ];
            if ($request->has('client_id')) {
                $data1['client_ids'] = implode(',', $request->input('client_id'));
            }
            DB::table('invitation')->insert($data1);
            // Send email to invitee
            $url = URL::to('accept_invitation') . '/' . $code;
            $query0 = DB::table('oauth_rp')->where('type', '=', 'google')->first();
            $data2['message_data'] = 'You are added as an administrator to the ' . $owner->org_name . ' Trustee Directory.<br>';
            $data2['message_data'] .= 'Go to ' . $url . ' to get registered.';
            $title = 'Invitation to ' . $owner->firstname . ' ' . $owner->lastname  . "'s Authorization Server";
            $to = $request->input('email');
            $this->send_mail('auth.emails.generic', $data2, $title, $to);
            $data3['name'] = Session::get('owner');
            $data3['title'] = 'Invitation Code';
            $data3['content'] = '<p>Invitation sent to ' . $request->input('first_name') . ' ' . $request->input('last_name') . ' (' . $to . ')</p>';
            $data3['content'] .= '<p>Alternatively, show the recently invited guest your QR code:</p><div style="text-align: center;">';
            $data3['content'] .= QrCode::size(300)->generate($url);
            $data3['content'] .= '</div>';
            return view('home', $data3);
        } else {
            $data['title'] = 'Invite an administrative user to the Directory';
            $data['post'] = route('add_owner');
            return view('invite', $data);
        }
    }

    public function make_invitation(Request $request)
    {
        $owner = DB::table('owner')->first();
        $data['name'] = Session::get('owner');
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'email' => 'required|unique:users,email',
                // 'first_name' => 'required',
                // 'last_name' => 'required'
            ]);
            $access_lifetime = App::make('oauth2')->getConfig('access_lifetime');
            $code = mt_rand( 10000000, 99999999);
            // $code = $this->gen_secret();
            $data1 = [
                'email' => $request->input('email'),
                'expires' => date('Y-m-d H:i:s', time() + $access_lifetime),
                'code' => $code,
                // 'first_name' => $request->input('first_name'),
                // 'last_name' => $request->input('last_name'),
                'owner' => 'no'
            ];
            if ($request->has('client_id')) {
                $data1['client_ids'] = implode(',', $request->input('client_id'));
            }
            DB::table('invitation')->insert($data1);
            // Send email to invitee
            $url = route('container_create', [$code]);
            $query0 = DB::table('oauth_rp')->where('type', '=', 'google')->first();
            $data2['message_data'] = 'You are invited to create a Trustee Authorization Server.<br>';
            $data2['message_data'] .= 'Go to <a href="' . $url . '" target="_blank">' . $url . '</a>to get started.<br>';
            $data2['message_data'] .= 'Your Invitation Code is: ' . $code;
            $data2['message_data'] .= '<br><br><br>See you soon,<br>From the ' . $owner->org_name . ' Trustee Directory';
            $title = 'Invitation to get a Trustee Authorization Server from ' . $owner->org_name . ' Trustee Directory';
            $to = $request->input('email');
            $this->send_mail('auth.emails.generic', $data2, $title, $to);
            $data3['name'] = Session::get('owner');
            $data3['title'] = 'Invitation Code';
            $data3['content'] = '<p>Invitation sent to ' . $to . '</p>';
            $data3['content'] .= '<p>Invitation code saved as: ' . $code . '</p>';
            // $data3['content'] .= '<p>Alternatively, show the recently invited guest your QR code:</p><div style="text-align: center;">';
            // $data3['content'] .= QrCode::size(300)->generate($url);
            // $data3['content'] .= '</div>';
            return view('home', $data3);
        } else {
            $data['title'] = 'Invite a user to the get a Trustee Authorizaion Server';
            $data['post'] = route('make_invitation');
            return view('invite', $data);
        }
    }

    public function login_authorize(Request $request)
    {
        $query = DB::table('owner')->first();
        $data['name'] = $query->firstname . ' ' . $query->lastname;
        $data['noheader'] = true;
        $scope_array = [
            'profle' => 'View your basic profile',
            'email' => 'View your email address',
            'offline_access' => 'Access offline',
            'uma_authorization' => 'Access resources'
        ];
        $scope_icon = [
            'profile' => 'fa-user',
            'email' => 'fa-envelope',
            'offline_access' => 'fa-share-alt',
            'uma_authorization' => 'fa-key'
        ];
        if (Session::get('logo_uri') == '') {
            $data['permissions'] = '<div><i class="fa fa-child fa-5x" aria-hidden="true" style="margin:20px;text-align: center;"></i></div>';
        } else {
            $data['permissions'] = '<div><img src="' . Session::get('logo_uri') . '" style="margin:20px;text-align: center;"></div>';
        }
        $data['permissions'] .= '<h2>' . Session::get('client_name') . ' would like to:</h2>';
        $data['permissions'] .= '<ul class="list-group">';
        $client = DB::table('oauth_clients')->where('client_id', '=', Session::get('oauth_client_id'))->first();
        $scopes_array = explode(' ', $client->scope);
        foreach ($scopes_array as $scope) {
            if (array_key_exists($scope, $scope_array)) {
                $data['permissions'] .= '<li class="list-group-item"><i class="fa fa-btn ' . $scope_icon[$scope] . '"></i> ' . $scope_array[$scope] . '</li>';
            }
        }
        $data['permissions'] .= '</ul>';
        return view('login_authorize', $data);
    }

    public function login_authorize_action(Request $request, $type)
    {
        if ($type == 'yes') {
            // Add user to client
            $client = DB::table('oauth_clients')->where('client_id', '=', Session::get('oauth_client_id'))->first();
            $user_array = explode(' ', $client->user_id);
            $user_array[] = Session::get('username');
            $data['user_id'] = implode(' ', $user_array);
            DB::table('oauth_clients')->where('client_id', '=', Session::get('oauth_client_id'))->update($data);
            Session::put('is_authorized', true);
        } else {
            Session::put('is_authorized', false);
        }
        return redirect()->route('authorize');
    }

    public function change_password(Request $request)
    {
        $data['name'] = Session::get('owner');
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'old_password' => 'required',
                'password' => 'required|min:4',
                'confirm_password' => 'required|min:4|same:password',
            ]);
            $query = DB::table('oauth_users')->where('username', '=', Session::get('username'))->first();
            if ($query->password == sha1($request->input('old_password'))) {
                $data1['password'] = sha1($request->input('password'));
                DB::table('oauth_users')->where('username', '=', Session::get('username'))->update($data1);
                Session::put('message_action', 'Password changed!');
                return redirect()->route('home');
            } else {
                return redirect()->back()->withErrors(['tryagain' => 'Your old password was incorrect.  Try again.']);
            }
        } else {
            return view('changepassword', $data);
        }
    }

    public function my_info(Request $request)
    {
        $query = DB::table('oauth_users')->where('username', '=', Session::get('username'))->first();
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $data['title'] = 'My Information';
        $data['content'] = '<ul class="list-group">';
        $data['content'] .= '<li class="list-group-item">First Name: ' . $query->first_name . '</li>';
        $data['content'] .= '<li class="list-group-item">Last Name: ' . $query->last_name . '</li>';
        $data['content'] .= '<li class="list-group-item">Email: ' . $query->email . '</li>';
        $owner_query = DB::table('owner')->first();
        if ($owner_query->sub == $query->sub) {
            $data['content'] .= '<li class="list-group-item">Date of Birth: ' . date('m/d/Y', strtotime($owner_query->DOB)) . '</li>';
            $data['content'] .= '<li class="list-group-item">Mobile Number: ' . $owner_query->mobile . '</li>';
        }
        if ($query->npi !== null && $query->npi !== '') {
            $data['content'] .= '<li class="list-group-item">NPI: ' . $query->npi . '</li>';
        }
        if ($query->specialty !== null && $query->specialty !== '') {
            $data['content'] .= '<li class="list-group-item">Speciality: ' . $query->specialty . '</li>';
        }
        $data['content'] .= '</ul>';
        if (Session::get('is_owner') == 'yes') {
            $data['content'] .= '<a href="' . route('change_password') . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-cog"></i>Change Password</a>';
            $data['content'] .= '<hr/><div class="alert alert-danger">Administrator Account</div>';
        }
        $data['back'] = '<a href="' . URL::to('my_info_edit') . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-pencil"></i> Edit</a>';
        return view('home', $data);
    }

    public function my_info_edit(Request $request)
    {
        $message = '';
        $owner_query = DB::table('owner')->first();
        $query = DB::table('oauth_users')->where('username', '=', Session::get('username'))->first();
        if ($request->isMethod('post')) {
            if ($owner_query->sub == $query->sub) {
                $this->validate($request, [
                    'email' => 'required',
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'date_of_birth' => 'required'
                ]);
            } else {
                $this->validate($request, [
                    'email' => 'required',
                    'first_name' => 'required',
                    'last_name' => 'required'
                ]);
            }
            $data1 = [
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'email' => $request->input('email')
            ];
            DB::table('oauth_users')->where('username', '=', Session::get('username'))->update($data1);
            $data2['email'] = $request->input('email');
            DB::table('users')->where('name', '=', Session::get('username'))->update($data2);
            if ($owner_query->sub == $query->sub) {
                $owner_data = [
                    'lastname' => $request->input('last_name'),
                    'firstname' => $request->input('first_name'),
                    'DOB' => date('Y-m-d', strtotime($request->input('date_of_birth'))),
                    'email' => $request->input('email'),
                    'mobile' => $request->input('mobile')
                ];
                DB::table('owner')->where('id', '=', '1')->update($owner_data);
                if ($owner_query->email !== $request->input('email') || $owner_query->mobile !== $request->input('mobile')) {
                    $pnosh_uri = URL::to('/') . '/nosh';
                    $pnosh = DB::table('oauth_clients')->where('client_uri', '=', $pnosh_uri)->first();
                    if ($pnosh) {
                        // Synchronize contact info with pNOSH
                        $url = URL::to('/') . '/nosh/as_sync';
                        $ch = curl_init();
                        $sync_data = [
                            'old_email' => $owner_query->email,
                            'client_id' => $pnosh->client_id,
                            'client_secret' => $pnosh->client_secret,
                            'email' => $request->input('email'),
                            'sms' => $request->input('mobile')
                        ];
                        $post = http_build_query($sync_data);
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
                        $result = curl_exec($ch);
                        $message = '<br>' . $result;
                    }
                }
            }
            Session::put('message_action', 'Information Updated.' . $message);
            return redirect()->route('my_info');
        } else {
            $data = [
                'first_name' => $query->first_name,
                'last_name' => $query->last_name,
                'email' => $query->email
            ];

            if ($owner_query->sub == $query->sub) {
                $data['date_of_birth'] = date('Y-m-d', strtotime($owner_query->DOB));
                $data['mobile'] = $owner_query->mobile;
            }
            return view('edit', $data);
        }
    }

    public function default_policies(Request $request)
    {
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $data['name'] = Session::get('owner');
        $query = DB::table('owner')->first();
        $default_policy_types = $this->default_policy_type();
        foreach ($default_policy_types as $default_policy_type) {
            $data[$default_policy_type] = '';
            if ($query->{$default_policy_type} == 1) {
                $data[$default_policy_type] = 'checked';
            }
        }
        $data['content'] = '<div><i class="fa fa-child fa-5x" aria-hidden="true" style="margin:20px;text-align: center;"></i></div>';
        $data['content'] .= '<h3>Resource Registration Consent Default Policies for Trustee Authorization Servers Your Directory Deploys</h3>';
        $data['content'] .= '<p>You can set default policies (who gets access to your resources) whenever you have a new resource server registered to this authorization server.</p>';
        return view('policies', $data);
    }

    public function change_policy(Request $request)
    {
        if ($request->input('submit') == 'save') {
            $default_policy_types = $this->default_policy_type();
            foreach ($default_policy_types as $default_policy_type) {
                if ($request->has($default_policy_type)) {
                    if ($request->input($default_policy_type) == 'on') {
                        $data[$default_policy_type] = 1;
                    } else {
                        $data[$default_policy_type] = 0;
                    }
                }
            }
            $query = DB::table('owner')->first();
            DB::table('owner')->where('id', '=', $query->id)->update($data);
            Session::put('message_action', 'Default policies saved!');
            return redirect()->route('home');
        } else {
            return redirect()->route('home');
        }
    }

    public function fhir_edit(Request $request)
    {
        $data['username'] = $request->input('username');
        $data['password'] = encrypt($request->input('password'));
        DB::table('fhir_clients')->where('endpoint_uri', '=', $request->input('endpoint_uri'))->update($data);
        return 'Username and password saved';
    }

    public function settings(Request $request)
    {
        if (Session::get('is_owner') == 'yes') {
            if ($request->isMethod('post')) {
                $this->validate($request, [
                    'last_name' => 'required'
                ]);
                $user_data['last_name'] = $request->input('last_name');
                DB::table('oauth_users')->where('username', '=', Session::get('username'))->update($user_data);
                $owner_data = [
                    'lastname' => $request->input('last_name'),
                    'homepage' => $request->input('homepage'),
                    'description' => $request->input('description'),
                    'condition' => $request->input('condition')
                ];
                DB::table('owner')->update($owner_data);
                Session::put('message_action', 'Settings saved');
                return redirect()->route('home');
            } else {
                $query = DB::table('owner')->first();
                $data['noheader'] = true;
                $data['last_name'] = $query->org_name;
                $data['homepage'] = $query->homepage;
                $data['description'] = $query->description;
                $data['condition'] = $query->condition;
                $data['name'] = Session::get('owner');
                return view('settings', $data);
            }
        } else {
            return redirect()->route('home');
        }
    }
}
