<?php

namespace App\Http\Controllers;

use App\Providers\XeroInvoicesServiceProvider;
use Exception;
use GuzzleHttp\Client;
use App\Models\XeroUser;
use App\Models\XeroToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class XeroAuthController extends Controller
{
    protected $xero_scopes = [
        'openid',
        'profile',
        'email',
        'accounting.transactions',
        'accounting.contacts',
        'accounting.settings',
        'accounting.attachments',
        'offline_access',
    ];

    # redirect to xero authorization
    public function redirectToXero()
    {
        $scopes = $this->xero_scopes;
        $query = http_build_query([
            'client_id' => config('services.xero.client_id'),
            'redirect_uri' => config('services.xero.redirect'),
            'scope' => implode(' ', $scopes),
            'response_type' => 'code',
            'state' => bin2hex(random_bytes(16)),
        ]);
        // Log::info("redirectURL: " . redirect('https://login.xero.com/identity/connect/authorize?' . $query));
        return redirect('https://login.xero.com/identity/connect/authorize?' . $query);
    }

    # callback to get token
    public function handleXeroCallback(Request $request)
    {
        // Log::info("request: " . $request);
        $http = new Client(['verify' => false]);
        $code = $request->query('code');
        $state = $request->query('state');
        $response = $http->post('https://identity.xero.com/connect/token', [
            'headers' => [
                'authorization' =>  "Basic " . base64_encode(config('services.xero.client_id') . ":" . config('services.xero.client_secret')),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'code' => $code,
                'redirect_uri' => config('services.xero.redirect'),
                'grant_type' => 'authorization_code',
            ],
        ]);


        $responseBody = json_decode((string) $response->getBody(), true);
        // Log::info('Response_Body: ' . json_encode($responseBody));
        $accessToken = $responseBody['access_token'];
        $refreshToken = $responseBody['refresh_token'];
        // Log::info("accessToken: " . $accessToken);
        // $decodedToken = JWT::decode($accessToken, new Key('secret', 'RS256'));
        // Log::info("decodedToken: ". $decodedToken);
        try {
            if ($accessToken) {
                $encryptedToken = Crypt::encrypt($accessToken);
                $encryptedRefreshToken = Crypt::encrypt($refreshToken);
                $xeroToken = XeroToken::where('app_name', env('APP_NAME'))->first();
                if($xeroToken){
                    $xeroToken->access_token = $encryptedToken;
                    $xeroToken->refresh_token = $encryptedRefreshToken;
                    $xeroToken->save();
                }else{
                    $xeroToken = new XeroToken();
                    $xeroToken->app_name = env('APP_NAME');
                    $xeroToken->access_token = $encryptedToken;
                    $xeroToken->refresh_token = $encryptedRefreshToken;
                    $xeroToken->save();
                }
                // $xeroTokenfind = XeroToken::where('app_name', env('APP_NAME'))->first();
                // $encryptedTokenfind = $xeroTokenfind->access_token;
                // $encryptedRefreshTokenfind = $xeroTokenfind->refresh_token;

                // $accessTokenfind = Crypt::decrypt($encryptedTokenfind);
                // $refreshTokenfind = Crypt::decrypt($encryptedRefreshTokenfind);

                // Log::info("accessTokenfind match: ". (($accessToken == $accessTokenfind) ? 'true' : 'false'));
                // Log::info("refreshTokenfind match: ". (($refreshToken = $refreshTokenfind)? 'true' : 'false'));

                $accessHttp = new Client(['verify' => true]);
                $userResponse = $accessHttp->get('https://api.xero.com/connections', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                        'Accept' => '*/*',
                    ]
                ]);
                $userData = json_decode((string) $userResponse->getBody(), true)[0];
                // Log::info("userData " . json_encode($userData));

                // Check if user exists
                $user = XeroUser::where('xero_id', $userData['id'])->first();

                if ($user) {
                    // User exists, update the record
                    $user->xero_id = $userData['id'];
                    $user->tenant_id = $userData['tenantId'];
                    $user->tenant_name = $userData['tenantName'];
                    $user->tenant_type = $userData['tenantType'];
                    $user->authEvent_id = $userData['authEventId'];
                    $user->app_name =  env('APP_NAME');
                    $user->save();
                    echo 'Success: Xero User information updated in the database.';
                } else {
                    // User does not exist, create a new record
                    $user = new XeroUser();
                    $user->xero_id = $userData['id'];
                    $user->tenant_id = $userData['tenantId'];
                    $user->tenant_name = $userData['tenantName'];
                    $user->tenant_type = $userData['tenantType'];
                    $user->authEvent_id = $userData['authEventId'];
                    $user->app_name =  env('APP_NAME');
                    $user->save();
                    echo 'Success: Xero User information saved to the database.';
                }

            } else {
                echo 'Error: Failed to obtain access token.';
            }
        } catch (Exception $e) {
            Log::error('' . $e->getMessage());
        }
    }
}
