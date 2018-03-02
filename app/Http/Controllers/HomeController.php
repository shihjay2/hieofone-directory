<?php

namespace App\Http\Controllers;

use App;
use App\Http\Requests;
use App\Libraries\OpenIDConnectClient;
use DB;
use Form;
use Illuminate\Http\Request;
use QrCode;
use Session;
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
        $query = DB::table('oauth_rp')->where('type', '=', 'pnosh')->get();
		if ($query) {
            $query1 = DB::table('rp_to_users')->where('username', '=', Session::get('username'))->get();
            if ($query1) {
                $data['content'] = '<form role="form"><div class="form-group"><input class="form-control" id="searchinput" type="search" placeholder="Filter Results..." /></div>';
    			$data['content'] .= '<div class="list-group searchlist">';
    			foreach ($query1 as $client_row) {
                    $client = DB::table('oauth_rp')->where('as_uri', '=', $client_row->as_uri)->first();
    				$link = '<span class="label label-success pnosh_link" nosh-link="' . $client->as_uri . '/nosh/uma_auth">Patient Centered Health Record</span>';
                    if ($client->picture == '' || $client->picture == null) {
                        $picture = '<i class="fa fa-btn fa-user"></i>';
                    } else {
                        $picture = '<img src="' . $client->picture . '" height="30" width="30">';
                    }
                    $remove = '<span class="pull-right"><span style="margin:10px"></span><i class="fa fa-minus fa-lg directory-remove" remove-val="' . $client->as_uri . '" title="Add to My Patient List" style="cursor:pointer;"></i></span>';
                	$data['content'] .= '<a href="' . route('resources', [$client->id]) . '" class="list-group-item">' . $picture . '<span style="margin:10px">' . $client->as_name . '</span>' . $link . $remove . '</a>';
    			}
    			$data['content'] .= '</div>';
            } else {
                $data['content'] = 'No connected patients yet.';
            }
		}
        $data['back'] = '<a href="' . URL::to('all_patients') . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-users"></i> All Patients</a>';
        return view('home', $data);
    }

    public function all_patients(Request $request)
    {
        $data['name'] = Session::get('owner');
        $data['title'] = 'All Patients';
        $data['content'] = 'No patients yet.';
        $data['searchbar'] = 'yes';
        $query = DB::table('oauth_rp')->where('type', '=', 'pnosh')->get();
		if ($query) {
            $data['content'] = '<form role="form"><div class="form-group"><input class="form-control" id="searchinput" type="search" placeholder="Filter Results..." /></div>';
			$data['content'] .= '<div class="list-group searchlist">';
			foreach ($query as $client) {
				$link = '<span class="label label-success pnosh_link" nosh-link="' . $client->as_uri . '/nosh/uma_auth">Patient Centered Health Record</span>';
                if ($client->picture == '' || $client->picture == null) {
                    $picture = '<i class="fa fa-btn fa-user"></i>';
                } else {
                    $picture = '<img src="' . $client->picture . '" height="30" width="30">';
                }
                $add = '<span class="pull-right"><span style="margin:10px"></span><i class="fa fa-plus fa-lg directory-add" add-val="' . $client->as_uri . '" title="Add to My Patient List" style="cursor:pointer;"></i></span>';
                $check = DB::table('rp_to_users')->where('username', '=', Session::get('username'))->where('as_uri', '=', $client->as_uri)->first();
                if ($check) {
                    $add = '';
                }
            	$data['content'] .= '<a href="' . route('resources', [$client->id]) . '" class="list-group-item">' . $picture . '<span style="margin:10px">' . $client->as_name . '</span>' . $link . $add . '</a>';
			}
			$data['content'] .= '</div>';
		}
        $data['back'] = '<a href="' . URL::to('home') . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-user"></i> My Patients</a>';
        return view('home', $data);
    }

    public function add_patient(Request $request)
    {
        $data = [
            'as_uri' => $request->input('as_uri'),
            'username' => Session::get('username')
        ];
        DB::table('rp_to_users')->insert($data);
        return 'Patient added to My Patient list';
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

    public function search(Request $request)
    {
        $data['name'] = Session::get('owner');
        $data['title'] = 'Search Results';
        $data['content'] = '';
        $data['searchbar'] = 'yes';
        if ($request->isMethod('post')) {
            $q = strtolower($request->input('search_field'));
            Session::put('search_term', $q);
        }
        $proceed = false;
        // Check all registered resources
        if (Session::has('uma_search_complete')) {
            $proceed = true;
            Session::forget('uma_search_complete');
        }
        if ($proceed == false) {
            if (Session::has('uma_search_count')) {
                return redirect()->route('uma_aat_search');
            } else {
                $resource_query = DB::table('rp_to_users')->where('username', '=', Session::get('username'))->whereNotNull('rpt')->get();
                if ($resource_query) {
                    foreach ($resource_query as $resource_row) {
                        $uma_search_count[] = $resource_row->as_uri;
                    }
                    Session::forget('uma_search_arr');
                    Session::put('uma_search_count', $uma_search_count);
                    return redirect()->route('uma_aat_search');
                }
            }
        }
        if (Session::has('uma_search_arr')) {
            $uma_search_arr = Session::get('uma_search_arr');
            if (count($uma_search_arr) > 0) {
                foreach ($uma_search_arr as $uma_search_k => $uma_search_v) {
                    $patient = DB::table('oauth_rp')->where('id', '=', $uma_search_k)->first();
                    $data['content'] .= '<div class="panel panel-default"><div class="panel-heading"></div>Resources from ' . $patient->as_name . ' <div class="panel-body"><div class="list-group">';
                    foreach ($uma_search_v as $uma_search_v_row) {
                        $data['content'] .= '<li class="list-group-item">' . $uma_search_v_row . '</li>';
                    }
                    $data['content'] .= '</div></div>';
                }
            }
            Session::forget('uma_search_arr');
        }
        $q = Session::get('search_term');
        $query = DB::table('oauth_rp')
            ->where('type', '=', 'pnosh')
            ->where(function($query_array1) use ($q) {
                $query_array1->where('as_name', 'LIKE', "%$q%")
                ->orWhere('as_uri', 'LIKE', "%$q%");
            })
            ->get();
        // Metadata search placeholder
        if ($query) {
            $data['content'] .= '<div class="panel panel-default"><div class="panel-heading"></div>Connected Patients<div class="panel-body"><div class="list-group">';
            foreach ($query as $client) {
				$link = '<span class="label label-success pnosh_link" nosh-link="' . $client->as_uri . '/nosh/uma_auth">Patient Centered Health Record</span>';
                if ($client->picture == '' || $client->picture == null) {
                    $picture = '<i class="fa fa-btn fa-user"></i>';
                } else {
                    $picture = '<img src="' . $client->picture . '" height="30" width="30">';
                }
                $add = '<span class="pull-right"><span style="margin:10px"></span><i class="fa fa-plus fa-lg directory-add" add-val="' . $client->as_uri . '" title="Add to My Patient List" style="cursor:pointer;"></i></span>';
                $check = DB::table('rp_to_users')->where('username', '=', Session::get('username'))->where('as_uri', '=', $client->as_uri)->first();
                if ($check) {
                    $add = '';
                }
            	$data['content'] .= '<a href="' . route('resources', [$client->id]) . '" class="list-group-item">' . $picture . '<span style="margin:10px">' . $client->as_name . '</span>' . $link . $add . '</a>';
			}
            $data['content'] .= '</div></div>';
        }
        $data['content'] .= '<div class="alert alert-warning">Metadata search functionality coming soon...</div>';
        if (Session::has('uma_errors')) {
            $data['content'] .= '<div class="alert alert-danger">Errors: ' . Session::get('uma_errors') . '</div>';
            Session::forget('uma_errors');
        }
        return view('home', $data);
    }

    /**
     * Show the registered resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function resources(Request $request, $id)
    {
        $client = DB::table('oauth_rp')->where('id', '=', $id)->first();
		$data['title'] = $client->as_name . "'s Patient Summary";
		$data['message_action'] = $request->session()->get('message_action');
		$request->session()->forget('message_action');
		$data['back'] = '<a href="' . route('home') . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-chevron-left"></i> My Patients</a>';
		$data['content'] = '<div class="list-group">';
		$link = '<span class="label label-success pnosh_link" nosh-link="' . $client->as_uri . '/nosh/uma_auth">Patient Centered Health Record</span>';
		$data['content'] .= '<a href="' . $client->as_uri . '/nosh/uma_auth" target="_blank" class="list-group-item"><span style="margin:10px;">Patient Centered Health Record (pNOSH) for ' . $client->as_name . '</span>' . $link . '</a>';
		$data['content'] .= '<a href="' . route('resource_view', ['Condition']) . '" class="list-group-item"><img src="https://cloud.noshchartingsystem.com/i-condition.png" height="20" width="20"><span style="margin:10px;">Conditions</span></a>';
		$data['content'] .= '<a href="' . route('resource_view', ['MedicationStatement']) . '" class="list-group-item"><img src="https://cloud.noshchartingsystem.com/i-pharmacy.png" height="20" width="20"><span style="margin:10px;">Medication List</span></a>';
		$data['content'] .= '<a href="' . route('resource_view', ['AllergyIntolerance']) . '" class="list-group-item"><img src="https://cloud.noshchartingsystem.com/i-allergy.png" height="20" width="20"><span style="margin:10px;">Allergy List</span></a>';
		$data['content'] .= '<a href="' . route('resource_view', ['Immunization']) . '" class="list-group-item"><img src="https://cloud.noshchartingsystem.com/i-immunizations.png" height="20" width="20"><span style="margin:10px;">Immunizations</span></a>';
		$data['content'] .= '</div>';
		Session::put('current_client_id', $id);
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
		Session::put('type', $type);
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
					return redirect()->route('uma_api');
				}
			} else {
				Session::forget('uma_aat');
				Session::forget('uma_permission_ticket');
			}
		}
		// Get AAT
		$url_array = ['/nosh/oidc','/nosh/fhir/oidc'];
		$as_uri = Session::get('uma_uri');
		$client_id = Session::get('uma_client_id');
		$client_secret = Session::get('uma_client_secret');
		$oidc = new OpenIDConnectClient($as_uri, $client_id, $client_secret);
		$oidc->requestAAT();
		Session::put('uma_aat', $oidc->getAccessToken());
		// Get permission ticket
		$urlinit = $as_uri . '/nosh/fhir/' . Session::get('type') . '?subject:Patient=1';
		$result = $this->fhir_request($urlinit,true);
		if (isset($result['error'])) {
			// error - return something
			return $result;
		}
		$permission_ticket = $result['ticket'];
		Session::put('uma_permission_ticket', $permission_ticket);
		Session::save();
		$as_uri = $result['as_uri'];
		$url = route('uma_aat');
		// Requesting party claims
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
			$oidc = new OpenIDConnectClient($as_uri, $client_id, $client_secret);
			$oidc->setAccessToken(Session::get('uma_aat'));
			$oidc->setRedirectURL($url);
			$result1 = $oidc->rpt_request($permission_ticket);
			if (isset($result1['error'])) {
				// error - return something
				if ($result1['error'] == 'expired_ticket') {
				    Session::forget('uma_aat');
					Session::forget('uma_permission_ticket');
					return redirect()->route('uma_aat');
				} else {
					$data['title'] = 'Error getting data';
					$data['back'] = '<a href="' . route('resources', [Session::get('current_client_id')]) . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-chevron-left"></i> Patient Summary</a>';
					$data['content'] = 'Description:<br>' . $result1['error'];
					return view('home', $data);
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
		$urlinit = $as_uri . '/nosh/fhir/' . Session::get('type') . '?subject:Patient=1';
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
		$id = Session::get('current_client_id');
		$client = DB::table('oauth_rp')->where('id', '=', Session::get('current_client_id'))->first();
		$title_array = [
			'Condition' => 'Conditions',
			'MedicationStatement' => 'Medication List',
			'AllergyIntolerance' => 'Allergy List',
			'Immunization' => 'Immunizations',
			'Patient' => 'Patient Information'
		];
		$query = DB::table('resource_set')->where('resource_set_id', '=', $id)->first();
		$data['title'] = $title_array[Session::get('type')] . ' for ' . $client->as_name;
		$data['back'] = '<a href="' . route('resources', [$id]) . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-chevron-left"></i> Patient Summary</a>';
		$data['content'] = 'None.';
		$pt_name = '';
		if (isset($result3['total'])) {
			if ($result3['total'] != '0') {
                $data['content'] = '<form role="form"><div class="form-group"><input class="form-control" id="searchinput" type="search" placeholder="Filter Results..." /></div>';
				$data['content'] .= '<ul class="list-group searchlist">';
				foreach ($result3['entry'] as $entry) {
					if (Session::get('type') == 'Patient' && Session::get('hnosh') == 'true') {
						$data['title'] = $title_array[Session::get('type')];
						$data['content'] .= '<li class="list-group-item">' . $entry['resource']['text']['div'];
						$urlinit1 = $as_uri . '/nosh/fhir/MedicationStatement?subject:Patient=1';
						$result4 = $this->fhir_request($urlinit1,false,$rpt);
						if (isset($result4['total'])) {
							if ($result4['total'] != '0') {
								$data['content'] .= '<strong>Medications</strong><ul>';
								foreach ($result4['entry'] as $entry1) {
									$data['content'] .= '<li>' . $entry1['resource']['text']['div'] . '</li>';
								}
								$data['content'] .= '</ul>';
							}
						}
						$data['content'] .= '</li>';
					} else  {
						$data['content'] .= '<li class="list-group-item">' . $entry['resource']['text']['div'] . '</li>';
					}
					if (Session::get('type') == 'Patient') {
						$pt_name = $entry['resource']['name'][0]['given'][0] . ' ' . $entry['resource']['name'][0]['family'][0] . ' (DOB: ' . $entry['resource']['birthDate'] . ')';
					}
				}
				$data['content'] .= '</ul>';
			}
		}
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
        if (count($uma_search_count) == 0) {
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
		$oidc = new OpenIDConnectClient($client->as_uri, $client->$client_id, $client->$client_secret);
		$oidc->requestAAT();
		Session::put('uma_aat', $oidc->getAccessToken());
		// Get permission ticket
        $urlinit = $as_uri . '/nosh/fhir/Patient?subject:Patient=1';
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
			$oidc = new OpenIDConnectClient($as_uri, $client_id, $client_secret);
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
                            $data['content'] .= '<td><a href="' . route('proxy_remove', [$user->sub]) . '" class="btn btn-danger" role="button">Remove As Proxy</a></td>';
                        } else {
                            $data['content'] .= '<td><a href="' . route('proxy_add', [$user->sub]) . '" class="btn btn-success" role="button">Add As Proxy</a></td>';
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

    public function make_invitation(Request $request)
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
                'last_name' => $request->input('last_name')
            ];
            if ($request->has('client_id')) {
                $data1['client_ids'] = implode(',', $request->input('client_id'));
            }
            DB::table('invitation')->insert($data1);
            // Send email to invitee
            $url = URL::to('accept_invitation') . '/' . $code;
            $query0 = DB::table('oauth_rp')->where('type', '=', 'google')->first();
            $data2['message_data'] = 'You are invited to the HIE of One Authorization Server for ' . $owner->firstname . ' ' . $owner->lastname . '.<br>';
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
            if ($owner->login_direct == 0) {
                $query = DB::table('oauth_clients')->where('authorized', '=', 1)->where('scope', 'LIKE', "%uma_protection%")->get();
                if ($query) {
                    $data['rs'] = '<ul class="list-group checked-list-box">';
                    $data['rs'] .= '<li class="list-group-item"><input type="checkbox" id="all_resources" style="margin:10px;"/>All Resources</li>';
                    foreach ($query as $client) {
                        $data['rs'] .= '<li class="list-group-item"><input type="checkbox" name="client_id[]" class="client_ids" value="' . $client->client_id . '" style="margin:10px;"/><img src="' . $client->logo_uri . '" style="max-height: 30px;width: auto;"><span style="margin:10px">' . $client->client_name . '</span></li>';
                    }
                    $data['rs'] .= '</ul>';
                }
            }
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
        $data['content'] .= '</ul>';
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
        $data['login_direct'] = '';
        $data['login_md_nosh'] = '';
        $data['any_npi'] = '';
        $data['login_google'] = '';
        if ($query->login_direct == 1) {
            $data['login_direct'] = 'checked';
        }
        if ($query->login_md_nosh == 1) {
            $data['login_md_nosh'] = 'checked';
        }
        if ($query->any_npi == 1) {
            $data['any_npi'] = 'checked';
        }
        if ($query->login_google == 1) {
            $data['login_google'] = 'checked';
        }
        if ($query->login_uport == 1) {
            $data['login_uport'] = 'checked';
        }
        $data['content'] = '<div><i class="fa fa-child fa-5x" aria-hidden="true" style="margin:20px;text-align: center;"></i></div>';
        $data['content'] .= '<h3>Resource Registration Consent Default Policies</h3>';
        $data['content'] .= '<p>You can set default policies (who gets access to your resources) whenever you have a new resource server registered to this authorization server.</p>';
        return view('policies', $data);
    }

    public function change_policy(Request $request)
    {
        if ($request->input('submit') == 'save') {
            if ($request->input('login_direct') == 'on') {
                $data['login_direct'] = 1;
            } else {
                $data['login_direct'] = 0;
            }
            if ($request->input('login_md_nosh') == 'on') {
                $data['login_md_nosh'] = 1;
            } else {
                $data['login_md_nosh'] = 0;
            }
            if ($request->input('any_npi') == 'on') {
                $data['any_npi'] = 1;
            } else {
                $data['any_npi'] = 0;
            }
            if ($request->input('login_google') == 'on') {
                $data['login_google'] = 1;
            } else {
                $data['login_google'] = 0;
            }
            if ($request->input('login_uport') == 'on') {
                $data['login_uport'] = 1;
            } else {
                $data['login_uport'] = 0;
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
}
