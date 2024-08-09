<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use GuzzleHttp\Client;
use App\Models\Contact;
use \App\Models\MondayToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;
use monday\Client\Auth\OAuth\ApiException;

class MondayAuthController extends Controller
{
    protected $monday_scopes = [
        'me:read',
        'boards:read',
        'boards:write',
        'updates:read',
        'updates:write',
        'account:read',
    ];    

    # redirect to monday authorization
    public function redirectToMonday()
    {
        $scopes = $this->monday_scopes;
        $query = http_build_query([
            'client_id' => config('services.monday.client_id'),
            'redirect_uri' => config('services.monday.redirect'),
            'scope' => implode(' ', $scopes),
            'response_type' => 'code',
            'state' => bin2hex(random_bytes(16)),
        ]);
        Log::info("redirectURL: ". redirect('https://auth.monday.com/oauth2/authorize?' . $query) );
        return redirect('https://auth.monday.com/oauth2/authorize?' . $query);
    }

    # callback to get token
    public function handleMondayCallback(Request $request)
    {
        // Log::info("??????");
        $http = new Client(['verify' => false]);
        $code = $request->query('code');
        $state = $request->query('state');

        // Log::info("this is: ", $request->code);

        $response = $http->post('https://auth.monday.com/oauth2/token', [
            'form_params' => [
                'client_id' => config('services.monday.client_id'),
                'client_secret' => config('services.monday.client_secret'),
                'code' => $code,
                'redirect_uri' => config('services.monday.redirect'),
                'grant_type' => 'authorization_code',
            ],
        ]);

        $token = json_decode((string) $response->getBody(), true)['access_token'];

        // echo('Response: '. $token);
        $responseBody = json_decode((string) $response->getBody(), true);
        // echo('Response_Body: '. print_r($responseBody));
        Log::info('Response_Body: '. json_encode($responseBody));

        $accessToken = $responseBody['access_token'];
        // $refreshToken = $responseBody['refresh_token'];
        // $expiresIn = $responseBody['expires_in'];
        // $expiresAt = Carbon::now()->addSeconds($expiresIn);

        try {
            if ($accessToken) {
                $encryptedToken = Crypt::encrypt($accessToken);
                // $encryptedRefresh = Crypt::encrypt($refreshToken);
    
                $mondayToken = new MondayToken();
                $mondayToken->access_token = $encryptedToken;
                // $mondayToken->refresh_token = $encryptedRefresh;
                // $mondayToken->expires_at = $expiresAt;
                $mondayToken->save();
    
                // Use the access token to get user info from Monday.com
                $userResponse = $http->post('https://api.monday.com/v2', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                    ],
                    'json' => [
                        'query' => '{
                            me {
                                id
                                name
                                email
                            }
                        }',
                    ],
                ]);
                echo('User_Response_Body: '. print_r($userResponse));
                Log::info('User_Response_Body: '. json_encode($userResponse));
                $userData = json_decode((string) $userResponse->getBody(), true)['data']['me'];
    
                // Save user data to database
                $user = new User();
                $user->monday_id = $userData['id'];
                $user->name = $userData['name'];
                $user->email = $userData['email'];
                $user->save();
    
                echo 'Success: User information saved to database.';
            } else {
                echo 'Error: Failed to obtain access token.';
            }
        }catch (Exception $e) {
            Log::error(''. $e->getMessage());
        }

        
    }
}
