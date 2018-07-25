<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LastActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('uma');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $client = DB::table('oauth_access_tokens')->where('access_token', '=', substr($token, 0, 255))->first();
        // Check if there is a uma_authorization scope
        $client_scopes = explode(' ', $client->scope);
        if (in_array('uma_authorization', $client_scopes)) {
            $query = DB::table('oauth_rp')->where('id', '=', $id)->first();
            $return['last_activity'] = $query->last_activity;
            $statusCode = 200;
        } else {
            $return = [
                'error' => 'unauthorized',
                'error_description' => 'The request has not been applied because it lacks valid authentication credentials for the target resource.'
            ];
            $statusCode = 401;
        }
        return response()->json($return, $statusCode);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $client = DB::table('oauth_access_tokens')->where('access_token', '=', substr($token, 0, 255))->first();
        $client_info = DB::table('oauth_clients')->where('client_id', '=', $client->client_id)->first();
        $client_scopes = explode(' ', $client_info->scope);
        if (in_array('uma_protection', $client_scopes)) {
            $data['last_activity'] = $request->input('last_activity');
            DB::table('oauth_rp')->where('id', '=', $id)->update($data);
            $statusCode = 200;
        } else {
           $statusCode = 401;
           $return = [
               'error' => 'unauthorized',
               'error_description' => 'The request has not been applied because it lacks valid authentication credentials for the target resource.'
           ];
       }
       return response()->json($return, $statusCode);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $client = DB::table('oauth_access_tokens')->where('access_token', '=', substr($token, 0, 255))->first();
        $client_info = DB::table('oauth_clients')->where('client_id', '=', $client->client_id)->first();
        $client_scopes = explode(' ', $client_info->scope);
        if (in_array('uma_protection', $client_scopes)) {
            $data['last_activity'] = '';
            DB::table('oauth_rp')->where('id', '=', $id)->update($data);
            $statusCode = 200;
        } else {
           $statusCode = 401;
           $return = [
               'error' => 'unauthorized',
               'error_description' => 'The request has not been applied because it lacks valid authentication credentials for the target resource.'
           ];
       }
       return response()->json($return, $statusCode);
    }
}
