<?php
namespace App\Http\Controllers;

use App;
use App\Http\Controllers\Controller;
use App\Libraries\OpenIDConnectClient;
use App\User;
use Artisan;
use Auth;
use Crypt;
use DB;
use File;
use Google_Client;
use Hash;
use Illuminate\Http\Request;
use NaviOcean\Laravel\NameParser;
use OAuth2\HttpFoundationBridge\Request as BridgeRequest;
use OAuth2\Response as BridgeResponse;
use Response;
use Socialite;
use Storage;
use URL;
use phpseclib\Crypt\RSA;
use Session;
use SimpleXMLElement;
use GuzzleHttp;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class OauthController extends Controller
{
    /**
    * Base funtions
    *
    */

    public function github_all()
    {
        $client = new \Github\Client(
            new \Github\HttpClient\CachedHttpClient(array('cache_dir' => '/tmp/github-api-cache'))
        );
        $client = new \Github\HttpClient\CachedHttpClient();
        $client->setCache(
            new \Github\HttpClient\Cache\FilesystemCache('/tmp/github-api-cache')
        );
        $client = new \Github\Client($client);
        $result = $client->api('repo')->commits()->all('shihjay2', 'hieofone-directory', array('sha' => 'master'));
        return $result;
    }

    public function github_release()
    {
        $client = new \Github\Client(
            new \Github\HttpClient\CachedHttpClient(array('cache_dir' => '/tmp/github-api-cache'))
        );
        $client = new \Github\HttpClient\CachedHttpClient();
        $client->setCache(
            new \Github\HttpClient\Cache\FilesystemCache('/tmp/github-api-cache')
        );
        $client = new \Github\Client($client);
        $result = $client->api('repo')->releases()->latest('shihjay2', 'hieofone-directory');
        return $result;
    }

    public function github_single($sha)
    {
        $client = new \Github\Client(
            new \Github\HttpClient\CachedHttpClient(array('cache_dir' => '/tmp/github-api-cache'))
        );
        $client = new \Github\HttpClient\CachedHttpClient();
        $client->setCache(
            new \Github\HttpClient\Cache\FilesystemCache('/tmp/github-api-cache')
        );
        $client = new \Github\Client($client);
        $result = $client->api('repo')->commits()->show('shihjay2', 'hieofone-directory', $sha);
        return $result;
    }

    /**
    * Installation
    *
    */

    public function install(Request $request)
    {
        // Check if already installed, if so, go back to home page
        $query = DB::table('owner')->first();
        // if ($query) {
        if (! $query) {
            // Tag version number for baseline prior to updating system in the future
            if (!File::exists(base_path() . "/.version")) {
                // First time after install
              $result = $this->github_all();
                File::put(base_path() . "/.version", $result[0]['sha']);
            }
            // Is this from a submit request or not
            if ($request->isMethod('post')) {
                $this->validate($request, [
                    'username' => 'required',
                    'email' => 'required',
                    'password' => 'required|min:4',
                    'confirm_password' => 'required|min:4|same:password',
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'org_name' => 'required'
                ]);
                // Register user
                $sub = $this->gen_uuid();
                $user_data = [
                    'username' => $request->input('username'),
                    'password' => sha1($request->input('password')),
                    'last_name' => $request->input('last_name'),
                    'first_name' => $request->input('first_name'),
                    'sub' => $sub,
                    'email' => $request->input('email')
                ];
                DB::table('oauth_users')->insert($user_data);
                $user_data1 = [
                    'name' => $request->input('username'),
                    'email' => $request->input('email')
                ];
                DB::table('users')->insert($user_data1);
                // Register owner
                $clientId = $this->gen_uuid();
                $clientSecret = $this->gen_secret();
                $owner_data = [
                    'lastname' => $request->input('last_name'),
                    'firstname' => $request->input('first_name'),
                    'email' => $request->input('email'),
                    'org_name' => $request->input('org_name'),
                    'client_id' => $clientId,
                    'sub' => $sub
                ];
                DB::table('owner')->insert($owner_data);
                // Register server as its own client
                $grant_types = 'client_credentials password authorization_code implicit jwt-bearer refresh_token';
                $scopes = 'openid profile email address phone offline_access';
                $data = [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_types' => $grant_types,
                    'scope' => $scopes,
                    'user_id' => $request->input('username'),
                    'client_name' => $request->input('org_name'),
                    'client_uri' => URL::to('/'),
                    'redirect_uri' => URL::to('oauth_login'),
                    'authorized' => 1,
                    'allow_introspection' => 1
                ];
                DB::table('oauth_clients')->insert($data);
                $data1 = [
                    'type' => 'self',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret
                ];
                DB::table('oauth_rp')->insert($data1);
                // Register scopes as default
                $scopes_array = explode(' ', $scopes);
                $scopes_array[] = 'uma_protection';
                $scopes_array[] = 'uma_authorization';
                foreach ($scopes_array as $scope) {
                    $scope_data = [
                        'scope' => $scope,
                        'is_default' => 1
                    ];
                    DB::table('oauth_scopes')->insert($scope_data);
                }
                // Go to email setup
                Session::put('install', 'yes');
                return redirect()->route('setup_mail');
            } else {
                $data2['noheader'] = true;
                return view('install', $data2);
            }
        }
        return redirect()->route('home');
    }

    public function setup_mail(Request $request)
    {
        $query = DB::table('owner')->first();
        if (Session::get('is_owner') == 'yes' || $query == false || Session::get('install') == 'yes') {
            if ($request->isMethod('post')) {
                $this->validate($request, [
                    'mail_type' => 'required'
                ]);
                $mail_arr = [
                    'gmail' => [
                        'MAIL_DRIVER' => 'smtp',
                        'MAIL_HOST' => 'smtp.gmail.com',
                        'MAIL_PORT' => 465,
                        'MAIL_ENCRYPTION' => 'ssl',
                        'MAIL_USERNAME' => $request->input('mail_username'),
                        'MAIL_PASSWORD' => '',
                        'GOOGLE_KEY' => $request->input('google_client_id'),
                        'GOOGLE_SECRET' => $request->input('google_client_secret'),
                        'GOOGLE_REDIRECT_URI' => URL::to('account/google')
                    ],
                    'mailgun' => [
                        'MAIL_DRIVER' => 'mailgun',
                        'MAILGUN_DOMAIN' => $request->input('mailgun_domain'),
                        'MAILGUN_SECRET' => $request->input('mailgun_secret'),
                        'MAIL_HOST' => '',
                        'MAIL_PORT' => '',
                        'MAIL_ENCRYPTION' => '',
                        'MAIL_USERNAME' => '',
                        'MAIL_PASSWORD' => '',
                        'GOOGLE_KEY' => '',
                        'GOOGLE_SECRET' => '',
                        'GOOGLE_REDIRECT_URI' => ''
                    ],
                    'sparkpost' => [
                        'MAIL_DRIVER' => 'sparkpost',
                        'SPARKPOST_SECRET' => $request->input('sparkpost_secret'),
                        'MAIL_HOST' => '',
                        'MAIL_PORT' => '',
                        'MAIL_ENCRYPTION' => '',
                        'MAIL_USERNAME' => '',
                        'MAIL_PASSWORD' => '',
                        'GOOGLE_KEY' => '',
                        'GOOGLE_SECRET' => '',
                        'GOOGLE_REDIRECT_URI' => ''
                    ],
                    'ses' => [
                        'MAIL_DRIVER' => 'ses',
                        'SES_KEY' => $request->input('ses_key'),
                        'SES_SECRET' => $request->input('ses_secret'),
                        'MAIL_HOST' => '',
                        'MAIL_PORT' => '',
                        'MAIL_ENCRYPTION' => '',
                        'MAIL_USERNAME' => '',
                        'MAIL_PASSWORD' => '',
                        'GOOGLE_KEY' => '',
                        'GOOGLE_SECRET' => '',
                        'GOOGLE_REDIRECT_URI' => ''
                    ],
                    'unique' => [
                        'MAIL_DRIVER' => 'smtp',
                        'MAIL_HOST' => $request->input('mail_host'),
                        'MAIL_PORT' => $request->input('mail_port'),
                        'MAIL_ENCRYPTION' => $request->input('mail_encryption'),
                        'MAIL_USERNAME' => $request->input('mail_username'),
                        'MAIL_PASSWORD' => $request->input('mail_password'),
                        'GOOGLE_KEY' => '',
                        'GOOGLE_SECRET' => '',
                        'GOOGLE_REDIRECT_URI' => ''
                    ]
                ];
                $this->changeEnv($mail_arr[$request->input('mail_type')]);
                if ($request->input('mail_type') == 'gmail') {
                    $google_data = [
                        'type' => 'google',
                        'client_id' => $request->input('google_client_id'),
                        'client_secret' => $request->input('google_client_secret'),
                        'redirect_uri' => URL::to('account/google'),
                        'smtp_username' => $request->input('mail_username')
                    ];
                    DB::table('oauth_rp')->insert($google_data);
                    return redirect()->route('installgoogle');
                } else {
                    return redirect()->route('setup_mail_test');
                }
            } else {
                $data2['noheader'] = true;
                $data2['mail_type'] = '';
                $data2['mail_host'] = env('MAIL_HOST');
                $data2['mail_port'] = env('MAIL_PORT');
                $data2['mail_encryption'] = env('MAIL_ENCRYPTION');
                $data2['mail_username'] = env('MAIL_USERNAME');
                $data2['mail_password'] = env('MAIL_PASSWORD');
                $data2['google_client_id'] = env('GOOGLE_KEY');
                $data2['google_client_secret'] = env('GOOGLE_SECRET');
                $data2['mail_username'] = env('MAIL_USERNAME');
                $data2['mailgun_domain'] = env('MAILGUN_DOMAIN');
                $data2['mailgun_secret'] = env('MAILGUN_SECRET');
                $data2['mail_type'] == 'sparkpost';
                $data2['sparkpost_secret'] = env('SPARKPOST_SECRET');
                $data2['ses_key'] = env('SES_KEY');
                $data2['ses_secret'] = env('SES_SECRET');
                if (env('MAIL_DRIVER') == 'smtp') {
                    if (env('MAIL_HOST') == 'smtp.gmail.com') {
                        $data2['mail_type'] = 'gmail';
                    } else {
                        $data2['mail_type'] = 'unique';
                    }
                } else {
                    $data2['mail_type'] = env('MAIL_DRIVER');
                }
                $data2['message_action'] = Session::get('message_action');
                Session::forget('message_action');
                return view('setup_mail', $data2);
            }
        } else {
            return redirect()->route('home');
        }
    }

    public function setup_mail_test(Request $request)
    {
        $data_message['message_data'] = 'This is a test';
        $query = DB::table('owner')->first();
        $message_action = 'Check to see in your registered e-mail account if you have recieved it.  If not, please come back to the E-mail Service page and try again.';
        try {
            $this->send_mail('auth.emails.generic', $data_message, 'Test E-mail', $query->email);
        } catch(\Exception $e){
            $message_action = 'Error - There is an error in your configuration.  Please try again.';
            Session::put('message_action', $message_action);
            return redirect()->route('setup_mail');
        }
        Session::put('message_action', $message_action);
        return redirect()->route('home');
    }

    /**
    * Welcome page functions
    *
    */

    public function welcome(Request $request)
    {
        $query = DB::table('owner')->first();
        // Check for empty forum categories and seed if none
        $categories = DB::table('chatter_categories')->first();
        if (! $categories) {
            DB::table('chatter_categories')->insert([
                0 => [
                    'id'         => 1,
                    'parent_id'  => null,
                    'order'      => 1,
                    'name'       => 'Introductions',
                    'color'      => '#3498DB',
                    'slug'       => 'introductions',
                    'created_at' => null,
                    'updated_at' => null,
                ],
                1 => [
                    'id'         => 2,
                    'parent_id'  => null,
                    'order'      => 2,
                    'name'       => 'General',
                    'color'      => '#2ECC71',
                    'slug'       => 'general',
                    'created_at' => null,
                    'updated_at' => null,
                ],
                2 => [
                    'id'         => 3,
                    'parent_id'  => null,
                    'order'      => 3,
                    'name'       => 'Feedback',
                    'color'      => '#9B59B6',
                    'slug'       => 'feedback',
                    'created_at' => null,
                    'updated_at' => null,
                ],
                3 => [
                    'id'         => 4,
                    'parent_id'  => null,
                    'order'      => 4,
                    'name'       => 'Random',
                    'color'      => '#E67E22',
                    'slug'       => 'random',
                    'created_at' => null,
                    'updated_at' => null,
                ],
                4 => [
                    'id'         => 5,
                    'parent_id'  => 1,
                    'order'      => 1,
                    'name'       => 'Rules',
                    'color'      => '#227ab5',
                    'slug'       => 'rules',
                    'created_at' => null,
                    'updated_at' => null,
                ],
                5 => [
                    'id'         => 6,
                    'parent_id'  => 5,
                    'order'      => 1,
                    'name'       => 'Basics',
                    'color'      => '#195a86',
                    'slug'       => 'basics',
                    'created_at' => null,
                    'updated_at' => null,
                ],
                6 => [
                    'id'         => 7,
                    'parent_id'  => 5,
                    'order'      => 2,
                    'name'       => 'Contribution',
                    'color'      => '#195a86',
                    'slug'       => 'contribution',
                    'created_at' => null,
                    'updated_at' => null,
                ],
                7 => [
                    'id'         => 8,
                    'parent_id'  => 1,
                    'order'      => 2,
                    'name'       => 'About',
                    'color'      => '#227ab5',
                    'slug'       => 'about',
                    'created_at' => null,
                    'updated_at' => null,
                ],
            ]);
        }
        if ($query) {
            if ($query->homepage == '0') {
                return redirect()->route('welcome0');
            }
            if ($query->homepage == '1') {
                return redirect()->route('welcome1');
            }
        } else {
            return redirect()->route('install');
        }
    }

    public function welcome0(Request $request)
    {
        $owner = DB::table('owner')->first();
        if ($owner) {
            $data['name'] = $owner->org_name;
            $data['title'] = 'Participating Patients';
            $data['content'] = 'No patients yet.';
            $data['searchbar'] = 'yes';
            $query = DB::table('oauth_rp')->where('type', '=', 'as')->get();
    		if ($query) {
                $data['content'] = '<form role="form"><div class="form-group"><input class="form-control" id="searchinput" type="search" placeholder="Filter Results..." /></div>';
    			$data['content'] .= '<div class="list-group searchlist">';
                $data['content'] .= '<a class="list-group-item row"><span class="col-sm-4"><strong>Name</strong></span><span class="col-sm-4"><strong>Resources</strong></span><span class="col-sm-3"><strong>Last Activity</strong></span></a>';
                // usort($query, function($a, $b) {
                //     return $b['last_act'] <=> $a['last_act'];
                // });
                foreach ($query as $client) {
                    $link = '<span class="col-sm-4">';
                    $rs = DB::table('as_to_rs')->where('as_id', '=', $client->id)->get();
                    $rs_count=0;
                    if ($rs) {
                        foreach ($rs as $rs_row) {
                            if ($rs_row->rs_public == 0) {
                                $link .= '<h4><span class="label label-danger">Please Sign In</span></h4>';
                            } else {
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
                    // $check = DB::table('rp_to_users')->where('username', '=', Session::get('username'))->where('as_uri', '=', $client->as_uri)->first();
                    // if ($check) {
                    //     $add = '';
                    // }
                	$data['content'] .= '<a href="' . route('resources', [$client->id]) . '" class="list-group-item row"><span class="col-sm-4">' . $picture . $client->as_name . '</span>' . $link . $activity . '</a>';
    			}
    			$data['content'] .= '</div>';
    		}
            // $data['back'] = '<a href="' . URL::to('home') . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-user"></i> My Patients</a>';
            return view('home', $data);
        } else {
            return redirect()->route('install');
        }
    }

    public function welcome0_old(Request $request)
    {
        $query = DB::table('owner')->first();
        if ($query) {
            $description = $query->description;
            if ($query->description == '') {
                $description = 'This is some test text you can enter';
            }
            $data = [
                'name' => $query->org_name,
                'text' => $description
            ];
            return view('welcome', $data);
        } else {
            return redirect()->route('install');
        }
    }

    public function welcome1(Request $request)
    {
        $query = DB::table('owner')->first();
        if ($query) {
            $description = $query->description;
            if ($query->description == '') {
                $description = 'This is some test text you can enter';
            }
            $data = [
                'name' => $query->org_name,
                'text' => $description,
                'search' => ''
            ];
            return view('welcome1', $data);
            // return redirect()->route('login');
        } else {
            return redirect()->route('install');
        }
    }

    public function privacy_policy(Request $request)
    {
        $data['date'] = 'May 16, 2018';
        return view('privacy_policy', $data);
    }

    public function search_welcome(Request $request)
    {
        // Demo
        $query = DB::table('owner')->first();
        $search = '';
        if ($request->isMethod('post')) {
            // $rp_count = '0';
            $rp_count = '7532';
            $condition = '.';
            $rp = DB::table('oauth_rp')->where('type', '=', 'pnosh')->get();
            if ($rp) {
                $rp_count = '7532'; // Demo
                // $rp_count = $rp->count();
            }
            if ($query->condition !== '') {
                $condition = ', each belonging to a patient with ' . $query->condition;
            }
            $search = '<p>The ' . $query->org_name . ' Trustee Directory is linked to ' . $rp_count . ' individual HIE of One accounts' . $condition . '</p>';
            if ($request->input('search_field') !== '') {
                $search_count = '10';
                $search .= '<p>The search term ' . $request->input('search_field') . ' appears in the notes for ' . $search_count .  ' patients</p>';
            }
        }
        $description = $query->description;
        if ($query->description == '') {
            $description = 'This is some test text you can enter';
        }
        $data = [
            'name' => $query->org_name,
            'text' => $description,
            'search' => $search
        ];
        return view('welcome1', $data);
    }

    public function metadata(Request $request, $type)
    {
        // Demo
        $query = DB::table('owner')->first();
        if ($type == 'medication') {
            $data['title'] = 'Linked medication data';
            $count = '1577';
            $no_count = '2409';
            $privacy_count = '4412';
            $last_update = '38 minutes ago';
            $data['content'] = '<p>The ' . $query->org_name . ' Trustee Directory is linked to ' . $count . ' individual patients who have recorded medication use data,</p><p>';
            $data['content'] .= $no_count . ' patients who have no data matching this criterion, and </p><p>';
            $data['content'] .= $privacy_count . ' patients whose privacy settings are too strict for us to include them in the calculation for anonymous users to see, but they may have additional data for you to use depending on the terms you propose.</p>';
            $data['content'] .= '<div class="alert alert-warning">Last update: ' . $last_update . '</div>';
            $data['back'] = '<a href="' . route('welcome1') . '" class="btn btn-default" role="button"><i class="fa fa-btn fa-chevron-left"></i> Back</a>';
        }
        return view('home', $data);
    }

    public function patients(Request $request, $create='')
    {
        $query = DB::table('owner')->first();
        if ($query) {
            $description = $query->description;
            if ($query->description == '') {
                $description = 'This is some test text you can enter';
            }
            $data = [
                'name' => $query->org_name,
                'text' => $description,
                'create' => 'no'
            ];
            if ($create !== '') {
                $data['create'] = 'yes';
            }
            return view('patients', $data);
        } else {
            return redirect()->route('install');
        }
    }

    public function container_create(Request $request, $code='')
    {
        $owner = DB::table('owner')->first();
        if ($request->isMethod('post')) {
            if ($code !== 'complete') {
                $this->validate($request, [
                    'email' => 'required'
                ]);
                $data['code'] = $this->gen_uuid();
                $data['email'] = $request->input('email');
                DB::table('invitation')->insert($data);
                $url = route('container_create', [$data['code']]);
                $data1['message_data'] = "This is message from the " . $owner->org_name . " Trustee Directory.<br><br>";
                $data1['message_data'] .= "Please confirm your e-mail so we know you're a real human.<br>";
                $data1['message_data'] .= 'To finish this process, please click on the following link or point your web browser to:<br>';
                $data1['message_data'] .= $url;
                $title1 = 'Complete your Trustee Patient Container creation from the ' . $owner->org_name . ' Trustee Directory';
                $to1 = $request->input('email');
                $this->send_mail('auth.emails.generic', $data1, $title1, $to1);
                $data6 = [
                    'name' => $owner->org_name,
                    'text' => 'Your request for a patient container has been received.  You will be receiving a confirmation e-mail to verify if you hare a human.  Thank you.'
                ];
                return view('simple', $data6);
            } else {
                $this->validate($request, [
                    'email' => 'required',
                    'url' => 'required',
                    'password' => 'required',
                    'username' => 'required'
                ]);
                $url7 = route('container_create', [$data['code']]);
                $data7['message_data'] = "This is message from the " . $owner->org_name . " Trustee Directory.<br><br>";
                $data7['message_data'] .= "Your Trustee Patient Container is ready for use!<br>";
                $data7['message_data'] .= 'To finish this process, please click on the following link or point your web browser to:<br>https://';
                $data7['message_data'] .= $request->input('url');
                $data7['message_data'] .= '<br><br>If you need to login to the terminal through SSH (Secure Shell), you can set your SSH client or terminal to the same URL above, using port 22.<br><br>';
                $data7['message_data'] .= 'Your usernmae is ' . $request->input('username') . '<br>';
                $data7['message_data'] .= 'Your temporary password is ' . $request->input('password') . '<br><br>You will be asked to change your password upon your first login via SSH';
                $title7 = 'Your Trustee Patient Container has been created!';
                $to7 = $request->input('email');
                $this->send_mail('auth.emails.generic', $data7, $title7, $to7);
                return 'Message has been sent to the patient';
            }
        } else {
            if ($code == '') {
                if (Session::get('is_owner') == 'yes') {
                    // This is a placeholder for automated droplet creation is handled; right now, it is manually entered
                    return view('container_create');
                }
            } else {
                // Check code
                $query = DB::table('invitation')->where('code', '=', $code)->first();
                if ($query) {
                    // create SSH key pair for patient - temporarily disabled due to Mac OpenSSL
                    // $rsa = new RSA();
                    // $rsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_OPENSSH);
                    // extract($rsa->createKey());
                    // $priv_key = $code . "_private_key";
                    // $pub_key = $code . "_public_key";
                    // File::put(storage_path() . "/app/" . $priv_key, $privatekey);
                    // File::put(storage_path() . "/app/" . $pub_key, $publickey);
                    $url2 = route('container_create', [$data['code']]);
                    $data3['message_data'] = "This is message from the " . $owner->org_name . " Trustee Directory.<br><br>";
                    $data3['message_data'] .= "Please create a container for " . $query->email . "<br>";
                    // $data3['message_data'] .= 'This is the SSH public key to include the droplet creation';
                    // $data3['message_data'] .= nl2br($publickey);
                    // $data3['message_data'] .= 'To finish this process, please click on the following link or point your web browser to:<br>';
                    // $data3['message_data'] .= $url2;
                    $title3 = 'Create a Trustee container under the ' . $owner->org_name . ' Trustee Directory';
                    $to3 = $owner->email;
                    $this->send_mail('auth.emails.generic', $data3, $title3, $to3);
                    $data4['code'] = 'Pending';
                    DB::table('invitation')->where('id', '=', $query->id)->update($data4);
                    $data5 = [
                        'name' => $owner->org_name,
                        'text' => 'You are verfied to be a human and we will be creating your patient container shortly.  Please await an email response when your container is ready for use.  Thank you.',
                        // 'privatekey' => route('key_download', [$priv_key]),
                        // 'publickey' => route('key_download', [$pub_key])
                    ];
                    return view('simple', $data5);
                }
            }
        }
    }

    public function key_download(Request $request, $file)
    {
        $pathToFile = storage_path() . "/app/" . $file;
        if(strpos($file, "private_key") !== false) {
            $name = 'trustee_id_rsa';
        } else {
            $name = 'trustee_id_rsa.pub';
        }
        return response()->download($pathToFile, $name)->deleteFileAfterSend(true);
    }

    public function clinicians(Request $request)
    {
        $query = DB::table('owner')->first();
        if ($query) {
            $description = $query->description;
            if ($query->description == '') {
                $description = 'This is some test text you can enter';
            }
            $data = [
                'name' => $query->org_name,
                'text' => $description
            ];
            return view('clinicians', $data);
        } else {
            return redirect()->route('install');
        }
    }

    public function others(Request $request)
    {
        $query = DB::table('owner')->first();
        if ($query) {
            $description = $query->description;
            if ($query->description == '') {
                $description = 'This is some test text you can enter';
            }
            $data = [
                'name' => $query->org_name,
                'text' => $description
            ];
            return view('providers', $data);
        } else {
            return redirect()->route('install');
        }
    }

    public function search(Request $request)
    {
        $owner = DB::table('owner')->first();
        $data['name'] = $owner->org_name;
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
            if (! empty($uma_search_arr)) {
                foreach ($uma_search_arr as $uma_search_k => $uma_search_v) {
                    $patient = DB::table('oauth_rp')->where('id', '=', $uma_search_k)->first();
                    $data['content'] .= '<div class="panel panel-default"><div class="panel-heading">Resources from ' . $patient->as_name . '</div><div class="panel-body"><div class="list-group">';
                    foreach ($uma_search_v as $uma_search_v_row) {
                        $data['content'] .= '<li class="list-group-item">' . $uma_search_v_row . '</li>';
                    }
                    $data['content'] .= '</div></div></div>';
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
            $data['content'] .= '<div class="panel panel-default"><div class="panel-heading">Connected Patients</div><div class="panel-body"><div class="list-group">';
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
            $data['content'] .= '</div></div></div>';
        }
        $data['content'] .= '<div class="alert alert-warning">Metadata search functionality coming soon...</div>';
        if (Session::has('uma_errors')) {
            $data['content'] .= '<div class="alert alert-danger">Errors: ' . Session::get('uma_errors') . '</div>';
            Session::forget('uma_errors');
        }
        return view('home', $data);
    }

    /**
    * Login and logout functions
    *
    */

    public function login(Request $request)
    {
        if (Auth::guest()) {
            $owner_query = DB::table('owner')->first();
            $proxies = DB::table('owner')->where('sub', '!=', $owner_query->sub)->get();
            $proxy_arr = [];
            if ($proxies) {
                foreach ($proxies as $proxy_row) {
                    $proxy_arr[] = $proxy_row->sub;
                }
            }
            if ($request->isMethod('post')) {
                $this->validate($request, [
                    'username' => 'required',
                    'password' => 'required'
                ]);
                // Check if there was an old request from the ouath_authorize function, else assume login is coming from server itself
                if (Session::get('oauth_response_type') == 'code') {
                    $client_id = Session::get('oauth_client_id');
                    $data['nooauth'] = true;
                } else {
                    $client = DB::table('owner')->first();
                    $client_id = $client->client_id;
                }
                // Get client secret
                $client1 = DB::table('oauth_clients')->where('client_id', '=', $client_id)->first();
                // Run authorization request
                $request->merge([
                    'client_id' => $client_id,
                    'client_secret' => $client1->client_secret,
                    'username' => $request->username,
                    'password' => $request->password,
                    'grant_type' => 'password'
                ]);
                $bridgedRequest = BridgeRequest::createFromRequest($request);
                $bridgedResponse = new BridgeResponse();
                $bridgedResponse = App::make('oauth2')->grantAccessToken($bridgedRequest, $bridgedResponse);
                if (isset($bridgedResponse['access_token'])) {
                    // Update to include JWT for introspection in the future if needed
                    $new_token_query = DB::table('oauth_access_tokens')->where('access_token', '=', substr($bridgedResponse['access_token'], 0, 255))->first();
                    $jwt_data = [
                        'jwt' => $bridgedResponse['access_token'],
                        'expires' => $new_token_query->expires
                    ];
                    DB::table('oauth_access_tokens')->where('access_token', '=', substr($bridgedResponse['access_token'], 0, 255))->update($jwt_data);
                    // Access token granted, authorize login!
                    $oauth_user = DB::table('oauth_users')->where('username', '=', $request->username)->first();
                    Session::put('access_token',  $bridgedResponse['access_token']);
                    Session::put('client_id', $client_id);
                    Session::put('owner', $owner_query->org_name);
                    Session::put('username', $request->input('username'));
                    Session::put('full_name', $oauth_user->first_name . ' ' . $oauth_user->last_name);
                    Session::put('client_name', $client1->client_name);
                    Session::put('logo_uri', $client1->logo_uri);
                    Session::put('sub', $oauth_user->sub);
                    Session::put('email', $oauth_user->email);
                    Session::put('login_origin', 'login_direct');
                    Session::put('invite', 'no');
                    Session::put('is_owner', 'no');
                    if ($oauth_user->sub == $owner_query->sub || in_array($oauth_user->sub, $proxy_arr)) {
                        Session::put('is_owner', 'yes');
                    }
                    if ($owner_query->sub == $oauth_user->sub) {
                        Session::put('invite', 'yes');
                    }
                    $user1 = DB::table('users')->where('name', '=', $request->username)->first();
                    Auth::loginUsingId($user1->id);
                    Session::save();
                    if (Session::has('uma_permission_ticket') && Session::has('uma_redirect_uri') && Session::has('uma_client_id') && Session::has('email')) {
                        // If generated from rqp_claims endpoint, do this
                        return redirect()->route('rqp_claims');
                    }
                    if (Session::get('oauth_response_type') == 'code') {
                        // Confirm if client is authorized
                        $authorized = DB::table('oauth_clients')->where('client_id', '=', $client_id)->where('authorized', '=', 1)->first();
                        if ($authorized) {
                            // This call is from authorization endpoint and client is authorized.  Check if user is associated with client
                            $user_array = explode(' ', $authorized->user_id);
                            if (in_array($request->username, $user_array)) {
                                // Go back to authorize route
                                Session::put('is_authorized', 'true');
                                return redirect()->route('authorize');
                            } else {
                                // Get user permission
                                return redirect()->route('login_authorize');
                            }
                        } else {
                            // Get owner permission if owner is logging in from new client/registration server
                            if ($oauth_user) {
                                if ($owner_query->sub == $oauth_user->sub) {
                                    return redirect()->route('authorize_resource_server');
                                } else {
                                    // Somehow, this is a registered user, but not the owner, and is using an unauthorized client - return back to login screen
                                    return redirect()->back()->withErrors(['tryagain' => 'Please contact the owner of this authorization server for assistance.']);
                                }
                            } else {
                                // Not a registered user
                                return redirect()->back()->withErrors(['tryagain' => 'Please contact the owner of this authorization server for assistance.']);
                            }
                        }
                    } else {
                        //  This call is directly from the home route.
                        return redirect()->intended('home');
                    }
                } else {
                    //  Incorrect login information
                    return redirect()->back()->withErrors(['tryagain' => 'Try again']);
                }
            } else {
                $query = DB::table('owner')->first();
                if ($query) {
                    // Show login form
                    $data['name'] = $query->org_name;
                    $data['noheader'] = true;
                    if (Session::get('oauth_response_type') == 'code') {
                        // Check if owner has set default policies and show other OIDC IDP's to relay information with HIE of One AS as relaying IDP
                        if ($owner_query->login_md_nosh == 0 && $owner_query->any_npi == 0 && $owner_query->login_google == 0) {
                            $data['nooauth'] = true;
                        }
                    } else {
                        Session::forget('oauth_response_type');
                        Session::forget('oauth_redirect_uri');
                        Session::forget('oauth_client_id');
                        Session::forget('oauth_nonce');
                        Session::forget('oauth_state');
                        Session::forget('oauth_scope');
                        Session::forget('is_authorized');
                    }
                    $data['google'] = DB::table('oauth_rp')->where('type', '=', 'google')->first();
                    $data['twitter'] = DB::table('oauth_rp')->where('type', '=', 'twitter')->first();
                    if (file_exists(base_path() . '/.version')) {
                        $data['version'] = file_get_contents(base_path() . '/.version');
                    } else {
                        $version = $this->github_all();
                        $data['version'] = $version[0]['sha'];
                    }
                    return view('auth.login', $data);
                } else {
                    // Not installed yet
                    return redirect()->route('install');
                }
            }
        } else {
            return redirect()->route('home');
        }
    }

    public function logout(Request $request)
    {
        Session::flush();
        Auth::logout();
        // Ensure pNOSH logs out too for safety
        $pnosh = DB::table('oauth_clients')->where('client_name', 'LIKE', "%Patient NOSH for%")->first();
        if ($pnosh) {
            $redirect_uri = URL::to('/') . '/nosh';
            $params = [
    			'redirect_uri' => URL::to('/')
    		];
    		$redirect_uri .= '/remote_logout?' . http_build_query($params, null, '&');
            return redirect($redirect_uri);
        }
        return redirect()->route('welcome');
    }

    public function login_uport(Request $request, $admin='')
    {
        $owner_query = DB::table('owner')->first();
        $proxies = DB::table('owner')->where('sub', '!=', $owner_query->sub)->get();
        $proxy_arr = [];
        if ($proxies) {
            foreach ($proxies as $proxy_row) {
                $proxy_arr[] = $proxy_row->sub;
            }
        }
        if ($request->has('uport')) {
            $proceed = false;
            $admin_status = 'no';
            if ($admin == 'admin') {
                $admin_parser = new NameParser();
                $admin_name_arr = $admin_parser->parse_name($request->input('name'));
                $admin_name_query = DB::table('owner')->where('firstname', '=', $admin_name_arr['fname'])->where('lastname', '=', $admin_name_arr['lname'])->first();
                if ($admin_name_query) {
                    $uport_user = DB::table('oauth_users')->where('sub', '=', $admin_name_query->sub)->first();
                    $proceed = true;
                    $admin_status = 'yes';
                } else {
                    $return['message'] = 'You are not authorized to access this Directory';
                }
            } else {
                $user_table = 'oauth_users';
                $uport_static = 'npi';
                $user_table_result = DB::select('SHOW INDEX FROM ' . $user_table . " WHERE Key_name = 'PRIMARY'");
                $user_table_result_arr = json_decode(json_encode($user_table_result), true);
                $table_key = $user_table_result_arr[0]['Column_name'];
                $uport_credentials = [
                    'name' => [
                        'fname' => 'first_name',
                        'lname' => 'last_name',
                        'description' => 'Name'
                    ],
                    'email' => [
                        'column' => 'email',
                        'description' => 'E-mail address'
                    ],
                    'npi' => [
                        'column' => 'npi',
                        'description' => 'NPI number'
                    ],
                    'uport' => [
                        'column' => 'uport_id',
                        'description' => 'uport address'
                    ]
                ];
                $username_arr = [];
                $static = '';
                $missing_creds = [];
                foreach ($uport_credentials as $uport_credential_key => $uport_credential_value) {
                    if ($uport_credential_key == 'name') {
                        $parser = new NameParser();
                        $name_arr = $parser->parse_name($request->input('name'));
                        $name_query = DB::table($user_table)->where($uport_credential_value['fname'], '=', $name_arr['fname'])->where($uport_credential_value['lname'], '=', $name_arr['lname'])->get();
                        if ($name_query) {
                            foreach ($name_query as $name_row) {
                                $username_arr[$uport_credential_key][] = $name_row->{$table_key};
                            }
                        }
                    } else {
                        if ($request->has($uport_credential_key)) {
                            $cred_query = DB::table($user_table)->where($uport_credential_value['column'], '=', $request->input($uport_credential_key))->first();
                            if ($cred_query) {
                                $username_arr[$uport_credential_key] = $cred_query->{$table_key};
                                if ($uport_static == $uport_credential_key) {
                                    $static = $cred_query->{$table_key};
                                }
                                if ($cred_query->sub == $owner_query->sub || in_array($cred_query->sub, $proxy_arr)) {
                                    //Admin user - start override
                                    $proceed = true;
                                    $uport_user = DB::table($user_table)->where($table_key, '=',  $cred_query->{$table_key})->first();
                                }
                            }
                        } else {
                            $missing_creds[] = $uport_credential_key;
                        }
                    }
                }
                if (empty($missing_creds)) {
                    if (! empty($username_arr)) {
                        // Check if static credential's username matches existing user
                        if ($static !== '') {
                            // There is a user with a static credential in the system
                            // Update any non-static entries if different
                            $uport_user = DB::table($user_table)->where($table_key, '=', $static)->first();
                            $static_data = [];
                            foreach ($uport_credentials as $uport_credential_key1 => $uport_credential_value1) {
                                if ($uport_user->{$uport_credential_value1['column']} !== $request->input($uport_credential_key1)) {
                                    $static_data[$uport_credential_value1['column']] = $request->input($uport_credential_key1);
                                }
                            }
                            if (! empty($static_data)) {
                                DB::table($table)->where($table_key, '=', $static)->update($static_data);
                            }
                            $proceed = true;
                        } else {
                            $return['message'] = 'You are not authorized to access this Directory.  Your ' . $uport_credentials[$uport_static]['description'] . ' does not exist in our system.';
                        }
                    } else {
                        $return['message'] = 'You are not authorized to access this Directory';
                    }
                } else {
                    $return['message'] = 'You are missing these credentials from your uPort: ';
                    $missing_ct = 0;
                    foreach ($missing_creds as $missing_cred) {
                        if ($missing_ct > 0) {
                            $return['message'] .= ', ';
                        }
                        $return['message'] .= $uport_credentials[$missing_cred]['description'];
                        $missing_ct++;
                    }
                }
            }
        } else {
            $return['message'] = 'Please contact the owner of this authorization server for assistance.';
        }
        if ($proceed == true) {
            if (Session::get('oauth_response_type') == 'code') {
                $client_id = Session::get('oauth_client_id');
            } else {
                $client_id = $owner_query->client_id;
            }
            Session::put('login_origin', 'login_direct');
            $this->login_sessions($uport_user, $client_id, $admin_status);
            $user = DB::table('users')->where('email', '=', $uport_user->email)->first();
            Auth::loginUsingId($user->id);
            Session::save();
            $return['message'] = 'OK';
            if (Session::has('uma_permission_ticket') && Session::has('uma_redirect_uri') && Session::has('uma_client_id') && Session::has('email')) {
                // If generated from rqp_claims endpoint, do this
                $return['url'] = route('rqp_claims');
            }
            if (Session::get('oauth_response_type') == 'code') {
                // Confirm if client is authorized
                $authorized = DB::table('oauth_clients')->where('client_id', '=', $client_id)->where('authorized', '=', 1)->first();
                if ($authorized) {
                    // This call is from authorization endpoint and client is authorized.  Check if user is associated with client
                    $user_array = explode(' ', $authorized->user_id);
                    if (in_array($uport_user->username, $user_array)) {
                        // Go back to authorize route
                        Session::put('is_authorized', 'true');
                        $return['url'] = route('authorize');
                    } else {
                        // Get user permission
                        $return['url'] = route('login_authorize');
                    }
                } else {
                    // Get owner permission if owner is logging in from new client/registration server
                    if ($oauth_user) {
                        if ($owner_query->sub == $uport_user->sub) {
                            $return['url'] = route('authorize_resource_server');
                        } else {
                            // Somehow, this is a registered user, but not the owner, and is using an unauthorized client - return back to login screen
                            $return['message'] = 'Unauthorized client.  Please contact the owner of this authorization server for assistance.';
                        }
                    } else {
                        // Not a registered user
                        $return['message'] = 'Not a registered user.  Please contact the owner of this authorization server for assistance.';
                    }
                }
            } else {
                //  This call is directly from the home route.
                $return['url'] = route('home');
            }
        }
        return $return;
    }

    public function uport_user_add(Request $request)
    {
        $owner = DB::table('owner')->first();
        $name = Session::get('google_name');
        $name_arr = explode(' ', $name);
        if (Session::get('oauth_response_type') == 'code') {
            $client_id = Session::get('oauth_client_id');
        } else {
            $client_id = $owner->client_id;
        }
        $sub = Session::get('uport_id');
        $email = Session::get('uport_email');
        $user_data = [
            'username' => $sub,
            'password' => sha1($sub),
            'first_name' => Session::get('uport_first_name'),
            'last_name' => Session::get('uport_last_name'),
            'email' => $email,
            'npi' => Session::get('uport_npi'),
            'sub' => $sub,
            'uport_id' => $sub
        ];
        Session::forget('uport_first_name');
        Session::forget('uport_last_name');
        Session::forget('uport_npi');
        Session::forget('uport_id');
        Session::forget('uport_email');
        DB::table('oauth_users')->insert($user_data);
        $user_data1 = [
            'name' => $sub,
            'email' => $email
        ];
        DB::table('users')->insert($user_data1);
        $user = DB::table('oauth_users')->where('username', '=', $sub)->first();
        $local_user = DB::table('users')->where('name', '=', $sub)->first();
        $this->login_sessions($user, $client_id);
        Auth::loginUsingId($local_user->id);
        if (Session::has('uma_permission_ticket') && Session::has('uma_redirect_uri') && Session::has('uma_client_id') && Session::has('email')) {
            // If generated from rqp_claims endpoint, do this
            return redirect()->route('rqp_claims');
        } elseif (Session::get('oauth_response_type') == 'code') {
            Session::put('is_authorized', 'true');
            Session::save();
            return redirect()->route('authorize');
        } else {
            Session::save();
            return redirect()->route('home');
        }
    }

    public function remote_logout(Request $request)
    {
        Session::flush();
        Auth::logout();
        return redirect($request->input('redirect_uri'));
    }

    public function oauth_login(Request $request)
    {
        $code = $request->input('code');
        return $code;
    }

    public function password_email(Request $request)
    {
        $owner = DB::table('owner')->first();
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'email' => 'required',
            ]);
            $query = DB::table('oauth_users')->where('email', '=', $request->input('email'))->first();
            if ($query) {
                $data['password'] = $this->gen_secret();
                DB::table('oauth_users')->where('email', '=', $request->input('email'))->update($data);
                $url = URL::to('password_reset') . '/' . $data['password'];
                $data2['message_data'] = 'This message is to notify you that you have reset your password with the ' . $owner->org_name . ' Trustee Directory.<br>';
                $data2['message_data'] .= 'To finish this process, please click on the following link or point your web browser to:<br>';
                $data2['message_data'] .= $url;
                $title = 'Reset password for the ' . $owner->org_name . ' Trustee Directory';
                $to = $request->input('email');
                $this->send_mail('auth.emails.generic', $data2, $title, $to);
            }
            return redirect()->route('welcome');
        } else {
            return view('password');
        }
    }

    public function password_reset(Request $request, $id)
    {
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'password' => 'required|min:7',
                'confirm_password' => 'required|min:7|same:password',
            ]);
            $query = DB::table('oauth_users')->where('password', '=', $id)->first();
            if ($query) {
                $data['password'] = sha1($request->input('password'));
                DB::table('oauth_users')->where('password', '=', $id)->update($data);
            }
            return redirect()->route('home');
        } else {
            $query1 = DB::table('oauth_users')->where('password', '=', $id)->first();
            if ($query1) {
                $data1['id'] = $id;
                return view('resetpassword', $data1);
            } else {
                return redirect()->route('welcome');
            }
        }
    }

    /**
    * Update system through GitHub
    *
    */

    public function update_system($type='')
    {
        if ($type !== '') {
            if ($type == 'composer_install') {
                $install = new Process("/usr/local/bin/composer install");
                $install->setWorkingDirectory(base_path());
                $install->setEnv(['COMPOSER_HOME' => '/usr/local/bin/composer']);
                $install->setTimeout(null);
                $install->run();
                return nl2br($install->getOutput());
            }
            if ($type = 'migrate') {
                $migrate = new Process("php artisan migrate --force");
                $migrate->setWorkingDirectory(base_path());
                $migrate->setTimeout(null);
                $migrate->run();
                return nl2br($migrate->getOutput());
            }
        } else {
            $current_version = File::get(base_path() . "/.version");
            $result = $this->github_all();
            $composer = false;
            if ($current_version != $result[0]['sha']) {
                $arr = [];
                foreach ($result as $row) {
                    $arr[] = $row['sha'];
                    if ($current_version == $row['sha']) {
                        break;
                    }
                }
                $arr2 = array_reverse($arr);
                foreach ($arr2 as $sha) {
                    $result1 = $this->github_single($sha);
                    if (isset($result1['files'])) {
                        foreach ($result1['files'] as $row1) {
                            $filename = base_path() . "/" . $row1['filename'];
                            if ($row1['status'] == 'added' || $row1['status'] == 'modified' || $row1['status'] == 'renamed') {
                                $github_url = str_replace(' ', '%20', $row1['raw_url']);
                                if ($github_url !== '') {
                                    $file = file_get_contents($github_url);
                                    $parts = explode('/', $row1['filename']);
                                    array_pop($parts);
                                    $dir = implode('/', $parts);
                                    if (!is_dir(base_path() . "/" . $dir)) {
                                        if ($parts[0] == 'public') {
                                            mkdir(base_path() . "/" . $dir, 0777, true);
                                        } else {
                                            mkdir(base_path() . "/" . $dir, 0755, true);
                                        }
                                    }
                                    file_put_contents($filename, $file);
                                    if ($filename == 'composer.json') {
                                        $composer = true;
                                    }
                                }
                            }
                            if ($row1['status'] == 'removed') {
                                if (file_exists($filename)) {
                                    unlink($filename);
                                }
                            }
                        }
                    }
                }
                define('STDIN',fopen("php://stdin","r"));
                File::put(base_path() . "/.version", $result[0]['sha']);
                $return = "System Updated with version " . $result[0]['sha'] . " from " . $current_version;
                $migrate = new Process("php artisan migrate --force");
                $migrate->setWorkingDirectory(base_path());
                $migrate->setTimeout(null);
                $migrate->run();
                $return .= '<br>' . nl2br($migrate->getOutput());
                if ($composer == true) {
                    $install = new Process("/usr/local/bin/composer install");
                    $install->setWorkingDirectory(base_path());
                    $install->setEnv(['COMPOSER_HOME' => '/usr/local/bin/composer']);
                    $install->setTimeout(null);
                    $install->run();
                    $return .= '<br>' . nl2br($install->getOutput());
                }
                return $return;
            } else {
                return "No update needed";
            }
        }
    }

    /**
    * Client registration page if they are given a QR code by the owner of this authorization server
    *
    */
    public function client_register(Request $request)
    {
        if ($request->isMethod('post')) {
        } else {
        }
    }

    /**
    * Social authentication as Open ID Connect relying party
    *
    * @return RQP claims route when authentication is successful
    * $user->token;
    * $user->getId();
    * $user->getNickname();
    * $user->getName();
    * $user->getEmail();
    * $user->getAvatar();
    *
    */

    public function installgoogle(Request $request)
    {
        $query0 = DB::table('oauth_rp')->where('type', '=', 'google')->first();
        $url = URL::to('installgoogle');
        $google = new Google_Client();
        $google->setRedirectUri($url);
        $google->setApplicationName('HIE of One');
        $google->setClientID($query0->client_id);
        $google->setClientSecret($query0->client_secret);
        $google->setAccessType('offline');
        $google->setApprovalPrompt('force');
        $google->setScopes(array('https://mail.google.com/'));
        if (isset($_REQUEST["code"])) {
            $credentials = $google->authenticate($_GET['code']);
            $data['refresh_token'] = $credentials['refresh_token'];
            DB::table('oauth_rp')->where('type', '=', 'google')->update($data);
            return redirect()->route('setup_mail_test');
        } else {
            $authUrl = $google->createAuthUrl();
            header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
            exit;
        }
    }

    public function google_redirect()
    {
        $query0 = DB::table('oauth_rp')->where('type', '=', 'google')->first();
        config(['services.google.client_id' => $query0->client_id]);
        config(['services.google.client_secret' => $query0->client_secret]);
        config(['services.google.redirect' => $query0->redirect_uri]);
        return Socialite::driver('google')->redirect();
    }

    public function google(Request $request)
    {
        $query0 = DB::table('oauth_rp')->where('type', '=', 'google')->first();
        $owner_query = DB::table('owner')->first();
        $proxies = DB::table('owner')->where('sub', '!=', $owner->sub)->get();
        $proxy_arr = [];
        if ($proxies) {
            foreach ($proxies as $proxy_row) {
                $proxy_arr[] = $proxy_row->sub;
            }
        }
        config(['services.google.client_id' => $query0->client_id]);
        config(['services.google.client_secret' => $query0->client_secret]);
        config(['services.google.redirect' => $query0->redirect_uri]);
        $user = Socialite::driver('google')->user();
        $google_user = DB::table('oauth_users')->where('email', '=', $user->getEmail())->first();
        // Get client if from OIDC call
        if (Session::get('oauth_response_type') == 'code') {
            $client_id = Session::get('oauth_client_id');
        } else {
            $client = DB::table('owner')->first();
            $client_id = $client->client_id;
        }
        $authorized = DB::table('oauth_clients')->where('client_id', '=', $client_id)->where('authorized', '=', 1)->first();
        if ($google_user) {
            // Google email matches
            Session::put('login_origin', 'login_google');
            $local_user = DB::table('users')->where('email', '=', $google_user->email)->first();
            if (Session::has('uma_permission_ticket') && Session::has('uma_redirect_uri') && Session::has('uma_client_id') && Session::has('email')) {
                // If generated from rqp_claims endpoint, do this
                return redirect()->route('rqp_claims');
            } elseif (Session::get('oauth_response_type') == 'code') {
                if ($authorized) {
                    Session::put('is_authorized', 'true');
                    $this->login_sessions($google_user, $client_id);
                    Auth::loginUsingId($local_user->id);
                    Session::save();
                    return redirect()->route('authorize');
                } else {
                    // Get owner permission if owner is logging in from new client/registration server
                    if ($owner_query->sub == $google_user->sub) {
                        $this->login_sessions($google_user, $client_id);
                        Auth::loginUsingId($local_user->id);
                        Session::save();
                        return redirect()->route('authorize_resource_server');
                    } else {
                        return redirect()->route('login')->withErrors(['tryagain' => 'Unauthorized client.  Please contact the owner of this authorization server for assistance.']);
                    }
                }
            } else {
                $this->login_sessions($google_user, $client_id);
                Auth::loginUsingId($local_user->id);
                Session::save();
                return redirect()->route('home');
            }
        } else {
            if ($owner_query->any_npi == 1 || $owner_query->login_google == 1) {
                if ($authorized) {
                    // Add new user
                    Session::put('google_sub' ,$user->getId());
                    Session::put('google_name', $user->getName());
                    Session::put('google_email', $user->getEmail());
                    return redirect()->route('google_md1');
                    // return redirect()->route('google_md');
                } else {
                    return redirect()->route('login')->withErrors(['tryagain' => 'Unauthorized client.  Please contact the owner of this authorization server for assistance.']);
                }
            } else {
                return redirect()->route('login')->withErrors(['tryagain' => 'Not a registered user.  Any NPI or Any Google not set.  Please contact the owner of this authorization server for assistance.']);
            }
        }
    }

    public function google_md1(Request $request)
    {
        $owner = DB::table('owner')->first();
        $name = Session::get('google_name');
        $name_arr = explode(' ', $name);
        if (Session::get('oauth_response_type') == 'code') {
            $client_id = Session::get('oauth_client_id');
        } else {
            $client_id = $owner->client_id;
        }
        $sub = Session::get('google_sub');
        $email = Session::get('google_email');
        Session::forget('google_sub');
        Session::forget('google_name');
        Session::forget('google_email');
        $npi = '1234567890';
        $user_data = [
            'username' => $sub,
            'password' => sha1($sub),
            'first_name' => $name_arr[0],
            'last_name' => $name_arr[1],
            'sub' => $sub,
            'email' => $email,
            'npi' => $npi
        ];
        DB::table('oauth_users')->insert($user_data);
        $user_data1 = [
            'name' => $sub,
            'email' => $email
        ];
        DB::table('users')->insert($user_data1);
        $user = DB::table('oauth_users')->where('username', '=', $sub)->first();
        $local_user = DB::table('users')->where('name', '=', $sub)->first();
        $this->login_sessions($user, $client_id);
        Auth::loginUsingId($local_user->id);
        if (Session::has('uma_permission_ticket') && Session::has('uma_redirect_uri') && Session::has('uma_client_id') && Session::has('email')) {
            // If generated from rqp_claims endpoint, do this
            return redirect()->route('rqp_claims');
        } elseif (Session::get('oauth_response_type') == 'code') {
            Session::put('is_authorized', 'true');
            Session::save();
            return redirect()->route('authorize');
        } else {
            Session::save();
            return redirect()->route('home');
        }
    }

    public function google_md(Request $request, $npi='')
    {
        $owner = DB::table('owner')->first();
        $name = Session::get('google_name');
        $name_arr = explode(' ', $name);
        if ($request->isMethod('post') || $npi !== '') {
            if (Session::get('oauth_response_type') == 'code') {
                $client_id = Session::get('oauth_client_id');
            } else {
                $client_id = $owner->client_id;
            }
            $sub = Session::get('google_sub');
            $email = Session::get('google_email');
            Session::forget('google_sub');
            Session::forget('google_name');
            Session::forget('google_email');
            if ($npi == '') {
                $npi = $request->input('npi');
            }
            $user_data = [
                'username' => $sub,
                'password' => sha1($sub),
                'first_name' => $name_arr[0],
                'last_name' => $name_arr[1],
                'sub' => $sub,
                'email' => $email,
                'npi' => $npi
            ];
            DB::table('oauth_users')->insert($user_data);
            $user_data1 = [
                'name' => $sub,
                'email' => $email
            ];
            DB::table('users')->insert($user_data1);
            $user = DB::table('oauth_users')->where('username', '=', $sub)->first();
            $local_user = DB::table('users')->where('name', '=', $sub)->first();
            $this->login_sessions($user, $client_id);
            Auth::loginUsingId($local_user->id);
            if (Session::has('uma_permission_ticket') && Session::has('uma_redirect_uri') && Session::has('uma_client_id') && Session::has('email')) {
                // If generated from rqp_claims endpoint, do this
                return redirect()->route('rqp_claims');
            } elseif (Session::get('oauth_response_type') == 'code') {
                Session::put('is_authorized', 'true');
                Session::save();
                return redirect()->route('authorize');
            } else {
                Session::save();
                return redirect()->route('home');
            }
        } else {
            $data['noheader'] = true;
            $data['owner'] = $owner->firstname . ' ' . $owner->lastname . "'s Authorization Server";
            $npi_arr = $this->npi_lookup($name_arr[0], $name_arr[1]);
            $data['npi'] = '<div class="list-group">';
            if ($npi_arr['result_count'] > 0) {
                foreach ($npi_arr['results'] as $npi) {
                    $label = '<strong>Name:</strong> ' . $npi['basic']['first_name'];
                    if (isset($npi['basic']['middle_name'])) {
                        $label .= ' ' . $npi['basic']['middle_name'];
                    }
                    $label .= ' ' . $npi['basic']['last_name'] . ', ' . $npi['basic']['credential'];
                    $label .= '<br><strong>NPI:</strong> ' . $npi['number'];
                    $label .= '<br><strong>Specialty:</strong> ' . $npi['taxonomies'][0]['desc'];
                    $label .= '<br><strong>Location:</strong> ' . $npi['addresses'][0]['city'] . ', ' . $npi['addresses'][0]['state'];
                    $data['npi'] .= '<a class="list-group-item" href="' . route('google_md', [$npi['number']]) . '">' . $label . '</a>';
                }
            }
            $data['npi'] .= '</div>';
            return view('google_md', $data);
        }
    }

    public function twitter_redirect()
    {
        $query0 = DB::table('oauth_rp')->where('type', '=', 'twitter')->first();
        config(['services.twitter.client_id' => $query0->client_id]);
        config(['services.twitter.client_secret' => $query0->client_secret]);
        config(['services.twitter.redirect' => $query0->redirect_uri]);
        return Socialite::driver('twitter')->redirect();
    }

    public function twitter(Request $request)
    {
        $query0 = DB::table('oauth_rp')->where('type', '=', 'twitter')->first();
        config(['services.twitter.client_id' => $query0->client_id]);
        config(['services.twitter.client_secret' => $query0->client_secret]);
        config(['services.twitter.redirect' => $query0->redirect_uri]);
        $user = Socialize::driver('twitter')->user();
        Session::put('email', $user->getEmail());
        Session::put('login_origin', 'login_twitter');
        if (Session::has('uma_permission_ticket') && Session::has('uma_redirect_uri') && Session::has('uma_client_id') && Session::has('email')) {
            // If generated from rqp_claims endpoint, do this
            return redirect()->route('rqp_claims');
        } else {
            $this->oauth_authenticate($user->getEmail());
            return redirect()->route('home');
        }
    }

    public function mdnosh(Request $request)
    {
        // Check if dynamically registered
        $query0 = DB::table('oauth_rp')->where('type', '=', 'mdnosh')->first();
        if ($query0) {
            // Registered
            $client = [
                'client_id' => $query0->client_id,
                'client_secret' => $query0->client_secret
            ];
        } else {
            $client = $this->mdnosh_register_client();
        }
        $open_id_url = 'http://noshchartingsystem.com/oidc';
        $url = route('mdnosh');
        $oidc = new OpenIDConnectClient($open_id_url, $client['client_id'], $client['client_secret']);
        $oidc->setRedirectURL($url);
        $oidc->addScope('openid');
        $oidc->addScope('email');
        $oidc->addScope('profile');
        $oidc->authenticate();
        $firstname = $oidc->requestUserInfo('given_name');
        $lastname = $oidc->requestUserInfo('family_name');
        $email = $oidc->requestUserInfo('email');
        $npi = $oidc->requestUserInfo('npi');
        $sub = $oidc->requestUserInfo('sub');
        $access_token = $oidc->getAccessToken();
        Session::put('email',  $oidc->requestUserInfo('email'));
        Session::put('login_origin', 'login_md_nosh');
        Session::put('npi', $npi);
        if (Session::has('uma_permission_ticket') && Session::has('uma_redirect_uri') && Session::has('uma_client_id') && Session::has('email')) {
            // If generated from rqp_claims endpoint, do this
            return redirect()->route('rqp_claims');
        } elseif (Session::get('oauth_response_type') == 'code') {
            $client_id = Session::get('oauth_client_id');
            $authorized = DB::table('oauth_clients')->where('client_id', '=', $client_id)->where('authorized', '=', 1)->first();
            if ($authorized) {
                Session::put('is_authorized', 'true');
                $owner_query = DB::table('owner')->first();
                $proxies = DB::table('owner')->where('sub', '!=', $owner_query->sub)->get();
                $proxy_arr = [];
                if ($proxies) {
                    foreach ($proxies as $proxy_row) {
                        $proxy_arr[] = $proxy_row->sub;
                    }
                }
                if ($owner_query->login_md_nosh == 1) {
                    // Add user if not added already
                    $sub_query = DB::table('oauth_users')->where('sub', '=', $sub)->first();
                    if (!$sub_query) {
                        $user_data = [
                            'username' => $sub,
                            'password' => sha1($sub),
                            'first_name' => $firstname,
                            'last_name' => $lastname,
                            'sub' => $sub,
                            'email' => $email,
                            'npi' => $npi
                        ];
                        DB::table('oauth_users')->insert($user_data);
                        $user_data1 = [
                            'name' => $sub,
                            'email' => $email
                        ];
                        DB::table('users')->insert($user_data1);
                    }
                    Session::put('sub', $sub);
                    Session::save();
                    $user1 = DB::table('users')->where('name', '=', $sub)->first();
                    Auth::loginUsingId($user1->id);
                    return redirect()->route('authorize');
                } else {
                    return redirect()->route('login')->withErrors(['tryagain' => 'Please contact the owner of this authorization server for assistance.']);
                }
            } else {
                return redirect()->route('login')->withErrors(['tryagain' => 'Please contact the owner of this authorization server for assistance.']);
            }
        } else {
            $this->oauth_authenticate($oidc->requestUserInfo('email'));
            return redirect()->route('home');
        }
    }

    public function mdnosh_register_client()
    {
        $user = DB::table('owner')->where('id', '=', '1')->first();
        $dob = date('m/d/Y', strtotime($user->DOB));
        $client_name = 'HIE of One Authorization Server for ' . $user->firstname . ' ' . $user->lastname . ' (DOB: ' . $dob . ')';
        $open_id_url = 'http://noshchartingsystem.com/oidc';
        $url = route('mdnosh');
        $oidc = new OpenIDConnectClient($open_id_url);
        $oidc->setClientName($client_name);
        $oidc->setRedirectURL($url);
        $oidc->register();
        $client_id = $oidc->getClientID();
        $client_secret = $oidc->getClientSecret();
        $data = [
            'type' => 'mdnosh',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $url
        ];
        DB::table('oauth_rp')->insert($data);
        return $data;
    }

    public function oauth_authenticate($email)
    {
        $user = User::where('email', '=', $email)->first();
        //$query = DB::table('oauth_users')->where('email', '=', $email)->first();
        if ($user) {
            Auth::login($user);
        }
        return true;
    }

    /**
    * Authorization endpoint
    *
    * @return Response
    */

    public function oauth_authorize(Request $request)
    {
        if (Auth::check()) {
            // Logged in, check if there was old request info and if so, plug into request since likely request is empty on the return.
            if (Session::has('oauth_response_type')) {
                $request->merge([
                    'response_type' => Session::get('oauth_response_type'),
                    'redirect_uri' => Session::get('oauth_redirect_uri'),
                    'client_id' => Session::get('oauth_client_id'),
                    'nonce' => Session::get('oauth_nonce'),
                    'state' => Session::get('oauth_state'),
                    'scope' => Session::get('oauth_scope')
                ]);
                if (Session::get('is_authorized') == 'true') {
                    $authorized = true;
                } else {
                    $authorized = false;
                }
                Session::forget('oauth_response_type');
                Session::forget('oauth_redirect_uri');
                Session::forget('oauth_client_id');
                Session::forget('oauth_nonce');
                Session::forget('oauth_state');
                Session::forget('oauth_scope');
                Session::forget('is_authorized');
            } else {
                $owner_query = DB::table('owner')->first();
                $oauth_user = DB::table('oauth_users')->where('username', '=', Session::get('username'))->first();
                $authorized_query = DB::table('oauth_clients')->where('client_id', '=', $request->input('client_id'))->where('authorized', '=', 1)->first();
                if ($authorized_query) {
                    // This call is from authorization endpoint and client is authorized.  Check if user is associated with client
                    $user_array = explode(' ', $authorized_query->user_id);
                    if (in_array(Session::get('username'), $user_array)) {
                        $authorized = true;
                    } else {
                        Session::put('oauth_response_type', $request->input('response_type'));
                        Session::put('oauth_redirect_uri', $request->input('redirect_uri'));
                        Session::put('oauth_client_id', $request->input('client_id'));
                        Session::put('oauth_nonce', $request->input('nonce'));
                        Session::put('oauth_state', $request->input('state'));
                        Session::put('oauth_scope', $request->input('scope'));
                        // Get user permission
                        return redirect()->route('login_authorize');
                    }
                } else {
                    if ($owner_query->sub == $oauth_user->sub) {
                        Session::put('oauth_response_type', $request->input('response_type'));
                        Session::put('oauth_redirect_uri', $request->input('redirect_uri'));
                        Session::put('oauth_client_id', $request->input('client_id'));
                        Session::put('oauth_nonce', $request->input('nonce'));
                        Session::put('oauth_state', $request->input('state'));
                        Session::put('oauth_scope', $request->input('scope'));
                        return redirect()->route('authorize_resource_server');
                    } else {
                        // Somehow, this is a registered user, but not the owner, and is using an unauthorized client - logout and return back to login screen
                        Session::flush();
                        Auth::logout();
                        return redirect()->route('login')->withErrors(['tryagain' => 'Please contact the owner of this authorization server for assistance.']);
                    }
                }
            }
            $bridgedRequest = BridgeRequest::createFromRequest($request);
            $bridgedResponse = new BridgeResponse();
            $bridgedResponse = App::make('oauth2')->handleAuthorizeRequest($bridgedRequest, $bridgedResponse, $authorized, Session::get('sub'));
            return $bridgedResponse;
        } else {
            // Do client check
            $query = DB::table('oauth_clients')->where('client_id', '=', $request->input('client_id'))->first();
            if ($query) {
                // Validate request
                $bridgedRequest = BridgeRequest::createFromRequest($request);
                $bridgedResponse = new BridgeResponse();
                $bridgedResponse = App::make('oauth2')->validateAuthorizeRequest($bridgedRequest, $bridgedResponse);
                if ($bridgedResponse == true) {
                    // Save request input to session prior to going to login route
                    Session::put('oauth_response_type', $request->input('response_type'));
                    Session::put('oauth_redirect_uri', $request->input('redirect_uri'));
                    Session::put('oauth_client_id', $request->input('client_id'));
                    Session::put('oauth_nonce', $request->input('nonce'));
                    Session::put('oauth_state', $request->input('state'));
                    Session::put('oauth_scope', $request->input('scope'));
                    return redirect()->route('login');
                } else {
                    return response('invalid_request', 400);
                }
            } else {
                return response('unauthorized_client', 400);
            }
        }
    }

    /**
    * Userinfo endpoint
    *
    * @return Response
    */

    public function userinfo(Request $request)
    {
        $bridgedRequest = BridgeRequest::createFromRequest($request);
        $bridgedResponse = new BridgeResponse();
        // Fix for Laravel
        $bridgedRequest->request = new \Symfony\Component\HttpFoundation\ParameterBag();
        $rawHeaders = getallheaders();
        if (isset($rawHeaders["Authorization"])) {
            $authorizationHeader = $rawHeaders["Authorization"];
            $bridgedRequest->headers->add([ 'Authorization' => $authorizationHeader]);
        }
        // $bridgedResponse = App::make('oauth2')->handleUserInfoRequest($bridgedRequest, $bridgedResponse);
        // return $bridgedResponse;
        if (App::make('oauth2')->verifyResourceRequest($bridgedRequest, $bridgedResponse)) {
            $token = App::make('oauth2')->getAccessTokenData($bridgedRequest);
            // Grab user details
            $query = DB::table('oauth_users')->where('sub', '=', $token['user_id'])->first();
            $owner_query = DB::table('owner')->first();
            if ($owner_query->sub == $token['user_id']) {
                $birthday = str_replace(' 00:00:00', '', $owner_query->DOB);
            } else {
                $birthday = '';
            }
            return Response::json(array(
                'sub' => $token['user_id'],
                'name' => $query->first_name . ' ' . $query->last_name,
                'given_name' => $query->first_name,
                'family_name' => $query->last_name,
                'email' => $query->email,
                'picture' => $query->picture,
                'birthday' => $birthday,
                'npi' => $query->npi,
                'uport_id' => $query->uport_id,
                'client'  => $token['client_id'],
                'expires' => $token['expires']
            ));
        } else {
            return Response::json(array('error' => 'Unauthorized'), $bridgedResponse->getStatusCode());
        }
    }

    /**
    * JSON Web Token signing keys
    *
    * @return Response
    */

    public function jwks_uri(Request $request)
    {
        $rsa = new RSA();
        $publicKey = File::get(base_path() . "/.pubkey.pem");
        $rsa->loadKey($publicKey);
        $parts = $rsa->getPublicKey(RSA::PUBLIC_FORMAT_XML);
        $values = new SimpleXMLElement($parts);
        $n = (string) $values->Modulus;
        $e = (string) $values->Exponent;
        $keys[] = [
            'kty' => 'RSA',
            'alg' => 'RS256',
            'use' => 'sig',
            'n' => $n,
            'e' => $e
        ];
        $return = [
            'keys' => $keys
        ];
        return $return;
    }

    /**
    * Introspection endpoint
    *
    * @return Response
    */

    public function introspect(Request $request)
    {
        $token = $request->input('token');
        $return['active'] = false;
        $query = DB::table('oauth_access_tokens')->where('jwt', '=', $token)->first();
        if ($query) {
            $expires = strtotime($query->expires);
            if ($expires > time()) {
                $return['active'] = true;
            }
        }
        return $return;
    }

    /**
    * Revocation endpoint
    *
    * @return Response
    */

    public function revoke(Request $request)
    {
        $bridgedRequest = BridgeRequest::createFromRequest($request);
        $bridgedResponse = new BridgeResponse();
        // Fix for Laravel
        $bridgedRequest->request = new \Symfony\Component\HttpFoundation\ParameterBag();
        $rawHeaders = getallheaders();
        if (isset($rawHeaders["Authorization"])) {
            $authorizationHeader = $rawHeaders["Authorization"];
            $bridgedRequest->headers->add([ 'Authorization' => $authorizationHeader]);
        }
        $bridgedResponse = App::make('oauth2')->handleRevokeRequest($bridgedRequest, $bridgedResponse);
        return $bridgedResponse;
    }

    /**=
    * Webfinger
    *
    * @return Response
    *
    */
    public function webfinger(Request $request)
    {
        $resource = str_replace('acct:', '', $request->input('resource'));
        $rel = $request->input('rel');
        $query = DB::table('oauth_users')->where('email', '=', $resource)->first();
        if ($query) {
            $response = [
                'subject' => $request->input('resource'),
                'links' => [
                    ['rel' => $rel, 'href' => URL::to('/')]
                ]
            ];
            return $response;
        } else {
            abort(404);
        }
    }

    public function accept_invitation(Request $request, $id)
    {
        $query = DB::table('invitation')->where('code', '=', $id)->first();
        if ($query) {
            $expires = strtotime($query->expires);
            $username = '';
            $password = '';
            $owner_status = false;
            if ($query->owner == 'yes') {
                $owner_status = true;
            }
            if ($expires > time()) {
                if ($request->isMethod('post')) {
                    $this->validate($request, [
                        'username' => 'unique:oauth_users,username',
                        'password' => 'min:7',
                        'confirm_password' => 'min:7|same:password'
                    ]);
                    if ($request->input('username') !== '') {
                        $username = $request->input('username');
                        $password = sha1($request->input('password'));
                    }
                    $this->add_user($id, $username, $password, $owner_status);
                    // if ($query->client_ids !== null) {
                    //     // Add policies to individual client resources
                    //     $client_ids = explode(',', $query->client_ids);
                    //     foreach ($client_ids as $client_id) {
                    //         $resource_sets = DB::table('resource_set')->where('client_id', '=', $client_id)->get();
                    //         foreach ($resource_sets as $resource_set) {
                    //             $data2['resource_set_id'] = $resource_set->resource_set_id;
                    //             $policy_id = DB::table('policy')->insertGetId($data2);
                    //             $query1 = DB::table('claim')->where('claim_value', '=', $query->email)->first();
                    //             if ($query1) {
                    //                 $claim_id = $query1->claim_id;
                    //             } else {
                    //                 $data3 = [
                    //                     'name' => $query->first_name . ' ' . $query->last_name,
                    //                     'claim_value' => $query->email
                    //                 ];
                    //                 $claim_id = DB::table('claim')->insertGetId($data3);
                    //             }
                    //             $data4 = [
                    //                 'claim_id' => $claim_id,
                    //                 'policy_id' => $policy_id
                    //             ];
                    //             DB::table('claim_to_policy')->insert($data4);
                    //             $scopes = DB::table('resource_set_scopes')->where('resource_set_id', '=', $resource_set->resource_set_id)->get();
                    //             foreach ($scopes as $scope) {
                    //                 $data5 = [
                    //                     'policy_id' => $policy_id,
                    //                     'scope' => $scope->scope
                    //                 ];
                    //                 DB::table('policy_scopes')->insert($data5);
                    //             }
                    //         }
                    //     }
                    // }
                    DB::table('invitation')->where('code', '=', $id)->delete();
                    return redirect()->route('home');
                } else {
                    if ($query->owner == 'yes') {
                        $data['noheader'] = true;
                        $owner = DB::table('owner')->first();
                        $data['code'] = $id;
                        $data['owner'] = $owner->org_name  . " Trustee Directory";
                        return view('accept_invite', $data);
                    } else {
                        $this->add_user($id, $username, $password, $owner_status);
                    }
                }
            } else {
                $error = 'Your invitation code expired.';
                return $error;
            }
        } else {
            $error = 'Your invitation code is invalid';
            return $error;
        }
    }

    public function pnosh_sync(Request $request)
    {
        $return = 'Error';
        if ($request->isMethod('post')) {
            $query = DB::table('oauth_clients')->where('client_id', '=', $request->input('client_id'))->where('client_secret', '=', $request->input('client_secret'))->first();
            if ($query) {
                $user = DB::table('users')->where('email', '=', $request->input('old_email'))->first();
                if ($user) {
                    $user1 = DB::table('oauth_users')->where('email', '=', $request->input('old_email'))->first();
                    $owner = DB::table('owner')->where('id', '=', '1')->where('sub', '=', $user1->sub)->first();
                    if ($owner) {
                        $owner_data = [
                            'email' => $request->input('email'),
                            'mobile' => $request->input('sms')
                        ];
                        DB::table('owner')->where('id', '=', $owner->id)->update($owner_data);
                        $data['email'] = $request->input('email');
                        DB::table('users')->where('email', '=', $request->input('old_email'))->update($data);
                        DB::table('oauth_users')->where('email', '=', $request->input('old_email'))->update($data);
                        $return = 'Contact data synchronized';
                    }
                }
            }
        }
        return $return;
    }

    public function reset_demo(Request $request)
    {
        if (route('welcome') == 'https://cloud.noshchartingsystem.com/hiedirectory') {
            $file = '/opt/hieofone-directory/demo_oidc.sql';
    		$command = "mysql -u " . env('DB_USERNAME', false) . " -p". env('DB_PASSWORD', false) . " oidc_directory < " . $file;
    		system($command);
    		$request->session()->flush();
    		Auth::logout();
            Session::flush();
            $mdnosh_url = 'http://noshchartingsystem.com/oidc/reset_demo';
            return redirect($mdnosh_url);
        } else {
            return redirect()->route('welcome');
        }
    }

    public function invite_demo(Request $request)
    {
        if (route('home') == 'https://shihjay.xyz/home') {
            if ($request->isMethod('post')) {
                $this->validate($request, [
                    'email' => 'required'
                ]);
                $data['email'] = $request->input('email');
                $owner = DB::table('owner')->first();
                DB::table('oauth_users')->where('sub', '=', $owner->sub)->update($data);
                $oauth_user = DB::table('oauth_users')->where('sub', '=', $owner->sub)->first();
                DB::table('users')->where('name', '=', $oauth_user->username)->update($data);
                $time = time() + 600;
                $file = $time . ',' . $request->ip();
                File::put(base_path() . "/.timer", $file);
                Session::flush();
                Auth::logout();
                return redirect()->route('login');
            } else {
                $data = [
                    'noheader' => true,
                    'timer' => true
                ];
                $file = File::get(base_path() . "/.timer");
                $arr = explode(',', $file);
                if (time() > $arr[0]) {
                    $data['timer'] = false;
                }
                if ($data['timer'] == true) {
                    $left = ($arr[0] - time()) / 60;
                    $data['timer_val'] = round($left);
                    $data['timer_val1'] = 10 - $data['timer_val'];
                    $newfile = $arr[0] . ',' . $request->ip();
                    File::put(base_path() . "/.timer", $newfile);
                }
                return view('reset_demo', $data);
            }
        } else {
            return redirect()->route('welcome');
        }
    }

    public function check_demo(Request $request)
    {
        $file = File::get(base_path() . "/.timer");
        $arr = explode(',', $file);
        if (time() < $arr[0]) {
            $left = ($arr[0] - time()) / 60;
            $return = round($left) . ',' . $arr[1];
            return $return;
        } else {
            return 'OK';
        }
    }

    public function check_demo_self(Request $request)
	{
        $return = 'OK';
        $return1 = 'OK';
        $file = File::get(base_path() . "/.timer");
        $arr = explode(',', $file);
        if (time() < $arr[0]) {
            $left = ($arr[0] - time()) / 60;
            $return = round($left) . ',' . $arr[1];
        }
		if ($return !== 'OK') {
			$arr = explode(',', $return);
			if ($arr[1] !== $request->ip()) {
				// Alert
				$return1 = 'You have ' . $arr[0] . ' minutes left to finish the demo.';
			}
		}
		return $return1;
	}

    public function test1(Request $request)
    {
    }

    public function demo_patient_list(Request $request, $login='no')
    {
        $owner = DB::table('owner')->First();
        $data['name'] = $owner->org_name;
        $data['title'] = 'Participating Patients';
        $data['content'] = 'No patients yet.';
        $data['searchbar'] = 'yes';
        $query_arr[] = [
            'as_uri' => 'http://hieofone.org',
            'as_uri2' => 'https://www.epic.com/',
            'as_name2' => 'Epic Health Records - XYZ Hospital',
            'picture' => '',
            'id' => '1',
            'as_name' => 'Alice Patient',
            'last_act' => mt_rand(1, time())
        ];
        $query_arr[] = [
            'as_uri' => 'http://hieofone.org',
            'as_uri2' => 'https://www.cerner.com/',
            'as_name2' => 'Cerner - ABC Hospital',
            'picture' => '',
            'id' => '2',
            'as_name' => 'Bob Patient',
            'last_act' => mt_rand(1, time())
        ];
        $query_arr[] = [
            'as_uri' => 'http://hieofone.org',
            'as_uri2' => 'https://noshemr.wordpress.com',
            'as_name2' => 'NOSH ChartingSystem',
            'picture' => '',
            'id' => '3',
            'as_name' => 'Charlie Patient',
            'last_act' => mt_rand(1, time())
        ];
        $query_arr[] = [
            'as_uri' => 'http://hieofone.org',
            'as_uri2' => 'https://www.epic.com/',
            'as_name2' => 'Epic Health Records - DEF Hospital',
            'picture' => '',
            'id' => '4',
            'as_name' => 'Donald Patient',
            'last_act' => mt_rand(1, time())
        ];
        $query = $query_arr;
        usort($query, function($a, $b) {
            return $b['last_act'] <=> $a['last_act'];
        });
        // $query = DB::table('oauth_rp')->where('type', '=', 'pnosh')->get();
		if ($query) {
            $data['content'] = '<form role="form"><div class="form-group"><input class="form-control" id="searchinput" type="search" placeholder="Filter Results..." /></div>';
			$data['content'] .= '<div class="list-group searchlist">';
            $data['content'] .= '<a class="list-group-item row"><span class="col-sm-3"><strong>Name</strong></span><span class="col-sm-4"><strong>Resources</strong></span><span class="col-sm-3"><strong>Last Activity</strong></span>';
            if ($login == 'yes') {
                $data['content'] .= '<span class="col-sm-2"><strong>Actions</strong></span>';
            }
            $data['content'] .= '</a>';
			foreach ($query as $client) {
                if ($login == 'yes') {
    				$link = '<span class="col-sm-4"><h4><span class="label label-danger pnosh_link" nosh-link="' . $client['as_uri'] . '">Patient Centered Health Record</span></h4>';
                    $link .= '<h4><span class="label label-danger pnosh_link" nosh-link="' . $client['as_uri2'] . '">' . $client['as_name2'] . '</span></h4></span>';
                } else {
                    $link = '<span class="col-sm-4"><h4><span class="label label-danger">Please Sign In</span></h4></span>';
                }
                if ($client['picture'] == '' || $client['picture'] == null) {
                    $picture = '<i class="fa fa-btn fa-user"></i>';
                } else {
                    $picture = '<img src="' . $client['picture'] . '" height="30" width="30">';
                }

                $act = '<span class="col-sm-3">' . date("Y-m-d H:i:s", $client['last_act']) . '</span>';
            	$data['content'] .= '<a href="' . route('resources', [$client['id']]) . '" class="list-group-item row">' . '<span class="col-sm-3">' . $picture . $client['as_name'] . '</span>' . $link . $act;
                if ($login == 'yes') {
                    $add = '<span class="col-sm-2 directory-add" add-val="' . $client['as_uri'] . '" title="Add to My Patient List and Get Notifications for any Changes"><i class="fa fa-plus fa-lg" style="cursor:pointer;"></i> Follow</span>';
                    $check = DB::table('rp_to_users')->where('username', '=', Session::get('username'))->where('as_uri', '=', $client['as_uri'])->first();
                    if ($check) {
                        $add = '';
                    }
                    $data['content'] .=  $add;
                }
                $data['content'] .= '</a>';
            }
			$data['content'] .= '</ul>';
		}
        if ($login == 'yes') {
            $data['demo'] = 'true';
            $data['title'] = 'All Participating Patients';
            $data['back'] = '<a href="" class="btn btn-default" role="button"><i class="fa fa-btn fa-user"></i> My Patients</a>';
        }
        // $data['noheader'] = 'true';
        return view('home', $data);
    }

    public function signup(Request $request)
	{
        $owner = DB::table('owner')->first();
		if ($request->isMethod('post')) {
			$this->validate($request, [
				// 'username' => 'required|unique:oauth_users,username',
				'email' => 'required|unique:oauth_users,email',
				// 'password' => 'required|min:7',
				// 'confirm_password' => 'required|min:7|same:password',
				'first_name' => 'required',
				'last_name' => 'required',
                'uport_id' => 'required|unique:oauth_users,uport_id',
				'npi' => 'required|min:10|numeric|unique:oauth_users,npi',
                'specialty' => 'required'
			]);
			// Register user
			$sub = $this->gen_uuid();
			$user_data = [
				'username' => Crypt::encrypt($sub),
				'password' => sha1($sub),
				'first_name' => $request->input('first_name'),
				'last_name' => $request->input('last_name'),
				'sub' => $sub,
				'email' => $request->input('email'),
				'npi' => $request->input('npi'),
                'uport_id' => $request->input('uport_id'),
                'specialty' => $request->input('specialty')
			];
			DB::table('oauth_users')->insert($user_data);
			$user_data1 = [
				'name' => $user_data['username'],
				'email' => $request->input('email')
			];
			DB::table('users')->insert($user_data1);
			$url = route('signup_confirmation', [$user_data['username']]);
			$data2['message_data'] = 'This message is to notify you that you have registered for an account with the ' . $owner->org_name . ' Directory.<br>';
			$data2['message_data'] .= 'To complete your registration, please click on the following link or point your web browser to:<br>';
			$data2['message_data'] .= $url;
			$title = 'Complete registration to the ' . $owner->org_name . ' Directory';
			$to = $request->input('email');
			$this->send_mail('auth.emails.generic', $data2, $title, $to);
		} else {
            $query = DB::table('owner')->first();
			$data2 = [
				'noheader' => true,
                'name' => $query->org_name
			];
			return view('signup', $data2);
		}
		return redirect()->route('home');
	}

	public function signup_confirmation($code)
	{
		$row = DB::table('oauth_users')->where('username', '=', $code)->first();
		if ($row) {
			$data_edit['username'] = Crypt::decrypt($code);
			$data_edit1['name'] = Crypt::decrypt($code);
			DB::table('users')->where('name', '=', $code)->update($data_edit1);
			DB::table('oauth_users')->where('username', '=', $code)->update($data_edit);
			$data1['content'] = '<p>Registration successful!</p><p><a href="' . route('login') . '">Login here.</a></p>';
		} else {
			$data1['content'] = '<p>Registration unsuccessful.  Try again</p>';
		}
		$data1['title'] = 'HIE of One Directory Registration Response';
		return view('home', $data1);
	}

    public function signup_hieofone(Request $request)
    {
        $data['title'] = 'Signup for HIE of One';
        $data['content'] = 'This is a placeholder page for deploying a new HIE of One container instances for a patient.  This will be installed as a subdomain of the directory root domain.';
        return view('home', $data);
    }

    public function uma_auth(Request $request)
	{
		$url = route('uma_auth');
		$open_id_url = Session::get('pnosh_url');
		$client_id = Session::get('pnosh_client_id');
		$client_secret = Session::get('pnosh_client_secret');
		$oidc = new OpenIDConnectClient($open_id_url, $client_id, $client_secret);
		$oidc->setRedirectURL($url);
		$oidc->addScope('openid');
		$oidc->addScope('email');
		$oidc->addScope('profile');
		$oidc->addScope('offline_access');
		$oidc->addScope('uma_authorization');
		// $oidc->addScope('uma_protection');
        $oidc->setUMA(true);
        $oidc->setUMAType('');
		$oidc->authenticate();
		$refresh_data['refresh_token'] = $oidc->getRefreshToken();
		$name = $oidc->requestUserInfo('name');
		$birthday = $oidc->requestUserInfo('birthday');
		$refresh_data['as_name'] = $name . ' (DOB: ' . $birthday . ')';
		$refresh_data['picture'] = $oidc->requestUserInfo('picture');
		DB::table('oauth_rp')->where('id', '=', Session::get('pnosh_id'))->update($refresh_data);
		$access_token = $oidc->getAccessToken();
		$data1['content'] = '<p>You may now close this window or <a href="' . Session::get('pnosh_url') .'/home">view your patient-centered health record.</a></p>';
		$data1['title'] = 'Health information exchange consent successful!';
		return view('home', $data1);
	}

    public function directory_auth(Request $request)
	{
		$url = route('directory_auth');
		$open_id_url = Session::get('as_url');
		$client_id = Session::get('as_client_id');
		$client_secret = Session::get('as_client_secret');
		$oidc = new OpenIDConnectClient($open_id_url, $client_id, $client_secret);
		$oidc->setRedirectURL($url);
		$oidc->addScope('openid');
		$oidc->addScope('email');
		$oidc->addScope('profile');
		$oidc->addScope('offline_access');
		$oidc->addScope('uma_authorization');
		// $oidc->addScope('uma_protection');
        $oidc->setUMA(true);
        $oidc->setUMAType('');
		$oidc->authenticate();
		$refresh_data['refresh_token'] = $oidc->getRefreshToken();
		$name = $oidc->requestUserInfo('name');
		$birthday = $oidc->requestUserInfo('birthday');
		$refresh_data['as_name'] = $name . ' (DOB: ' . $birthday . ')';
		$refresh_data['picture'] = $oidc->requestUserInfo('picture');
		DB::table('oauth_rp')->where('id', '=', Session::get('as_id'))->update($refresh_data);
        $owner = DB::table('owner')->first();
        $params = [
            'name' => $owner->org_name,
            'directory_id' => Session::get('as_id')
        ];
        $redirect_url = rtrim($open_id_url, '/') . '/directory_add/approve?' . http_build_query($params, null, '&');
        return redirect($redirect_url);
	}

    public function directory_check(Request $request, $id)
    {
        $row = DB::table('oauth_rp')->where('id', '=', $id)->first();
        if ($row) {
            return 'OK';
        } else {
            return 'Not registered to this directory';
        }
    }

    public function directory_registration(Request $request, $id='')
	{
        $owner = DB::table('owner')->first();
        if ($request->isMethod('post')) {
            $as_uri = $request->input('as_uri');
            $data1 = [
    			'type' => 'as',
    			'as_uri' => $as_uri,
                'last_activity' => $request->input('last_update')
    		];
            $query = DB::table('oauth_rp')->where('as_uri', '=', $as_uri)->first();
    		if ($query) {
    			$id = $query->id;
    			// Update information
    			DB::table('oauth_rp')->where('id', '=', $query->id)->update($data1);
    		} else {
    			$id = DB::table('oauth_rp')->insertGetId($data1);
    		}
            $rs = json_decode(json_encode($request->input('rs')), true);
            foreach ($rs as $rs_row) {
                $data = [
                    'as_id' => $id,
                    'rs_uri' => $rs_row['uri'],
                    'rs_name' => $rs_row['name'],
                    'rs_public' => $rs_row['public'],
                    'rs_private' => $rs_row['private']
                ];
                DB::table('as_to_rs')->insert($data);
            }
            $return = [
                'id' => $id,
                'uri' => route('directory_registration', [$id])
            ];
            return $return;
        } else {
            if ($id !== '') {
                $query1 = DB::table('oauth_rp')->where('id', '=', $id)->first();
                if ($query1) {
                    $client_name = "Directory - " . $owner->org_name;
            		$url1 = route('directory_auth');
            		$oidc = new OpenIDConnectClient($query1->as_uri);
            		$oidc->setClientName($client_name);
            		// $oidc->setRedirectURL($url1);
                    $oidc->setSessionName('directory');
                    $oidc->addRedirectURLs($url1);
                    $oidc->addRedirectURLs(route('uma_auth'));
                    $oidc->addRedirectURLs(route('uma_api'));
                    $oidc->addRedirectURLs(route('uma_aat'));
                    $oidc->addRedirectURLs(route('uma_register_auth'));
                    $oidc->addRedirectURLs(route('uma_aat_search'));
                    $oidc->addRedirectURLs(route('uma_api_search'));
            		$oidc->addScope('openid');
            		$oidc->addScope('email');
            		$oidc->addScope('profile');
            		$oidc->addScope('address');
            		$oidc->addScope('phone');
            		$oidc->addScope('offline_access');
            		$oidc->addScope('uma_authorization');
                    $oidc->setUMA(true);
            		$oidc->register();
            		$client_id = $oidc->getClientID();
            		$client_secret = $oidc->getClientSecret();
                    $data2 = [
            			'client_id' => $client_id,
            			'client_secret' => $client_secret,
            		];
                    DB::table('oauth_rp')->where('id', '=', $id)->update($data2);
            		Session::put('as_client_id', $client_id);
            		Session::put('as_client_secret', $client_secret);
            		Session::put('as_url', $query1->as_uri);
            		Session::put('as_id', $id);
            		Session::save();
            		return redirect()->route('directory_auth');
                } else {
                    return 'You are not a registered authorization server';
                }
            } else {
                return redirect()->route('welcome');
            }
        }
	}

    public function directory_remove(Request $request, $id)
    {
        $row = DB::table('oauth_rp')->where('id', '=', $id)->first();
        if ($row) {
            DB::table('oauth_rp')->where('id', '=', $id)->delete();
            DB::table('as_to_rs')->where('as_id', '=', $id)->delete();
            $return['message'] = 'Directory removed';
        } else {
            $return['message'] = 'Error: Authorization Server not registered';
        }
        return $return;
    }

    public function directory_update(Request $request, $id)
    {
        $query = DB::table('oauth_rp')->where('id', '=', $id)->first();
        $return = [];
        if ($query) {
            $rs = json_decode(json_encode($request->input('rs')), true);
            DB::table('as_to_rs')->where('as_id', '=', $id)->delete();
            foreach ($rs as $rs_row) {
                $data = [
                    'as_id' => $id,
                    'rs_uri' => $rs_row['uri'],
                    'rs_name' => $rs_row['name'],
                    'rs_public' => $rs_row['public'],
                    'rs_private' => $rs_row['private']
                ];
                DB::table('as_to_rs')->insert($data);
            }
            $data1 = [
                'as_uri' => $request->input('as_uri'),
                'name' => $request->input('name'),
                'last_activity' => $request->input('last_update')
            ];
            $row = DB::table('oauth_rp')->where('id', '=', $id)->update($data1);
            $return['message'] = 'Update successful';
        } else {
            $return['message'] = 'Error: Authorization Server not registered';
        }
        return $return;
    }

	public function uma_register(Request $request)
	{
		if ($request->isMethod('post')) {
			$this->validate($request, [
				'email' => 'required|email'
			]);
			Session::forget('type');
			Session::forget('client_id');
			Session::forget('url');
			$domain = explode('@', $request->input('email'));
			// webfinger
			$url = 'https://' . $domain[1] . '/.well-known/webfinger';
			$query_string = 'resource=acct:' . $request->input('email') . '&rel=http://openid.net/specs/connect/1.0/issuer';
			$url .= '?' . $query_string ;
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_FAILONERROR,1);
			curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_TIMEOUT, 60);
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,0);
			$result = curl_exec($ch);
			$result_array = json_decode($result, true);
			curl_close($ch);
			if (isset($result_array['subject'])) {
				$as_uri = $result_array['links'][0]['href'];
                $owner_query = DB::table('owner')->first();
				$client_name = $owner_query->org_name . " Trustee Directory";
				$url1 = route('uma_auth');
				$oidc = new OpenIDConnectClient($as_uri);
				$oidc->setClientName($client_name);
                $oidc->setSessionName('directory');
                $oidc->addRedirectURLs($url1);
                $oidc->addRedirectURLs(route('directory_auth'));
                $oidc->addRedirectURLs(route('uma_api'));
                $oidc->addRedirectURLs(route('uma_aat'));
                $oidc->addRedirectURLs(route('uma_register_auth'));
                $oidc->addRedirectURLs(route('uma_aat_search'));
                $oidc->addRedirectURLs(route('uma_api_search'));
				$oidc->addScope('openid');
				$oidc->addScope('email');
				$oidc->addScope('profile');
				$oidc->addScope('offline_access');
				$oidc->addScope('uma_authorization');
                // $oidc->addScope('uma_protection');
                $oidc->setUMA(true);
				$oidc->register();
				$client_id = $oidc->getClientID();
				$client_secret = $oidc->getClientSecret();
				$data1 = [
					'type' => 'pnosh',
					'client_id' => $client_id,
					'client_secret' => $client_secret,
					'as_uri' => $as_uri
				];
				// Check if as_uri already exists
				$query = DB::table('oauth_rp')->where('as_uri', '=', $as_uri)->first();
				if ($query) {
					$id = $query->id;
					// Update information
					DB::table('oauth_rp')->where('id', '=', $query->id)->update($data1);
				} else {
					$id = DB::table('oauth_rp')->insertGetId($data1);
				}
				Session::put('pnosh_client_id', $client_id);
				Session::put('pnosh_client_secret', $client_secret);
				Session::put('pnosh_url', $as_uri);
				Session::put('pnosh_id', $id);
				Session::save();
				return redirect()->route('uma_auth');
			} else {
				return redirect()->back()->withErrors(['tryagain' => 'Try again']);
			}
		} else {
			$data['noheader'] = true;
			return view('uma_register', $data);
		}
	}

    public function uma_register_url(Request $request)
	{
		if ($request->isMethod('post')) {
			$this->validate($request, [
				'url' => 'required'
			]);
			Session::forget('type');
			Session::forget('client_id');
			Session::forget('url');
			$url = $request->input('url');
            if(strpos($url, "https://") !== false) {
                $url = $url;
            } else {
                $url = 'https://' . $url;
            }
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_FAILONERROR,1);
			curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_TIMEOUT, 60);
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,0);
			$result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
            if ($httpCode !== 404 && $httpCode !== 0) {
                $owner_query = DB::table('owner')->first();
				$client_name = $owner_query->org_name . " Trustee Directory";
				$url1 = route('uma_auth');
				$oidc = new OpenIDConnectClient($url);
				$oidc->setClientName($client_name);
                $oidc->setSessionName('directory');
                $oidc->addRedirectURLs($url1);
                $oidc->addRedirectURLs(route('directory_auth'));
                $oidc->addRedirectURLs(route('uma_api'));
                $oidc->addRedirectURLs(route('uma_aat'));
                $oidc->addRedirectURLs(route('uma_register_auth'));
                $oidc->addRedirectURLs(route('uma_aat_search'));
                $oidc->addRedirectURLs(route('uma_api_search'));
				$oidc->addScope('openid');
				$oidc->addScope('email');
				$oidc->addScope('profile');
				$oidc->addScope('offline_access');
				$oidc->addScope('uma_authorization');
                // $oidc->addScope('uma_protection');
                $oidc->setUMA(true);
				$oidc->register();
				$client_id = $oidc->getClientID();
				$client_secret = $oidc->getClientSecret();
				$data1 = [
					'type' => 'pnosh',
					'client_id' => $client_id,
					'client_secret' => $client_secret,
					'as_uri' => $url
				];
				// Check if as_uri already exists
				$query = DB::table('oauth_rp')->where('as_uri', '=', $url)->first();
				if ($query) {
					$id = $query->id;
					// Update information
					DB::table('oauth_rp')->where('id', '=', $query->id)->update($data1);
				} else {
					$id = DB::table('oauth_rp')->insertGetId($data1);
				}
				Session::put('pnosh_client_id', $client_id);
				Session::put('pnosh_client_secret', $client_secret);
				Session::put('pnosh_url', $url);
				Session::put('pnosh_id', $id);
				Session::save();
				return redirect()->route('uma_auth');
			} else {
				return redirect()->back()->withErrors(['tryagain' => 'Try again']);
			}
		} else {
			$data['noheader'] = true;
			return view('uma_register_url', $data);
		}
	}

    public function check(Request $request)
    {
        $query = DB::table('owner')->first();
        return $query->org_name;
    }

    public function mailgun(Request $request)
    {
        $uri = $request->input('uri');
        $dns_uri = 'https://dns-api.org/A/' . $uri;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $dns_uri);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch,CURLOPT_FAILONERROR,1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_TIMEOUT, 60);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,0);
        $return = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close ($ch);
        $return_arr = json_decode($return, true);
        if ($httpCode !== 404 && $httpCode !== 0) {
            if ($return_arr[0]['value'] == $_SERVER['REMOTE_ADDR']) {
                $secret = env('MAILGUN_SECRET', false);
                return $secret;
            } else {
                return "Not authorized.";
            };
        } else {
            return "Try again.";
        }
    }
}
