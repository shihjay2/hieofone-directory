<?php

namespace App\Http\Middleware;

use Closure;
use DB;
use Response;
use Shihjay2\OpenIDConnectUMAClient;
use URL;

class Uma
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $payload = $request->header('Authorization');
        $id = $request->input('id');
        $resource = DB::table('uma')->where('scope', '=', $request->fullUrl())->first();
        if ($resource) {
            $as = DB::table('oauth_rp')->where('id', '=', $resource->as_id)->first();
            $client_id = $as->client_id;
            $client_secret = $as->client_secret;
            $open_id_url = $as->as_uri;
            if ($payload) {
                // RPT, Perform Token Introspection
                $rpt = str_replace('Bearer ', '', $payload);
                $oidc = new OpenIDConnectUMAClient($open_id_url, $client_id, $client_secret);
                $oidc->setUMA(true);
                $oidc->refreshToken($as->refresh_token);
                if ($oidc->getRefreshToken() != '') {
                    $refresh_data['refresh_token'] = $oidc->getRefreshToken();
                    DB::table('oauth_rp')->where('id', '=', $as->id)->update($refresh_data);
                }
                $result_rpt = $oidc->introspect($rpt);
                if ($result_rpt['active'] == false) {
                    $header = [
                        'WWW-Authenticate' => 'UMA realm="directory_UMA", as_uri="' . $open_id_url . '"'
                    ];
                    $statusCode = 403;
                    // Look for additional scopes for resource_set_id
                    $query1 = DB::table('uma')->where('resource_set_id', '=', $query->resource_set_id)->get();
                    $scopes = [];
                    foreach ($query1 as $row1) {
                        $scopes[] = $row1->scope;
                    }
                    $oidc = new OpenIDConnectUMAClient($open_id_url, $client_id, $client_secret);
                    $oidc->setUMA(true);
                    $oidc->refreshToken($as->refresh_token,true);
                    if ($oidc->getRefreshToken() != '') {
                        $refresh_data['refresh_token'] = $oidc->getRefreshToken();
                        DB::table('oauth_rp')->where('id', '=', $as->id)->update($refresh_data);
                    }
                    $permission_ticket = $oidc->permission_request($query->resource_set_id, $scopes);
                    if (isset($permission_ticket['error'])) {
                        $response = [
                            'error' => $permission_ticket['error'],
                            'error_description' => $permission_ticket['error_description']
                        ];
                        $header = [
                            'Warning' => '199 - "UMA Authorization Server Unreachable"'
                        ];
                    } else {
                        $response = [
                            'ticket' => $permission_ticket['ticket']
                        ];
                    }
                    return Response::json($response, $statusCode, $header);
                } else {
                    return $next($request);
                }
            } else {
                $header = [
                    'WWW-Authenticate' => 'UMA realm = "directory_UMA", as_uri = "' . $open_id_url . '"'
                ];
                $statusCode = 403;
                // Look for additional scopes for resource_set_id
                $query1 = DB::table('uma')->where('resource_set_id', '=', $query->resource_set_id)->get();
                $scopes = [];
                foreach ($query1 as $row1) {
                    $scopes[] = $row1->scope;
                }
                $oidc = new OpenIDConnectUMAClient($open_id_url, $client_id, $client_secret);
                $oidc->setUMA(true);
                $oidc->refreshToken($as->refresh_token,true);
                if ($oidc->getRefreshToken() != '') {
                    $refresh_data['refresh_token'] = $oidc->getRefreshToken();
                    DB::table('oauth_rp')->where('id', '=', $as->id)->update($refresh_data);
                }
                $permission_ticket = $oidc->permission_request($query->resource_set_id, $scopes);
                if (isset($permission_ticket['error'])) {
                    $response = [
                        'error' => $permission_ticket['error'],
                        'error_description' => $permission_ticket['error_description']
                    ];
                } else {
                    $response = [
                        'ticket' => $permission_ticket['ticket']
                    ];
                }
            }
        } else {
            $statusCode = 403;
            $response = [
                'error' => 'invalid_scope',
                'error_description' => 'At least one of the scopes included in the request was not registered previously by this resource server.'
            ];
        }
        return Response::json($response, $statusCode, $header);
    }
}
