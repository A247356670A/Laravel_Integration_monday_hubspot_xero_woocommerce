<?php

namespace App\Providers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\XeroUser;
use App\Models\XeroToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\ServiceProvider;

class XeroReauthServiceProvider extends ServiceProvider
{
    public function __construct()
    {
    }
    public function setAuthorizationHeader()
    {
        $xeroToken = XeroToken::where('app_name', env('APP_NAME'))->first();
        if (!$xeroToken) {
            throw new \Exception('No xeroToken data found!');
        }
        if ($this->isAccessTokenExpired($xeroToken)) {
            Log::info("Token Expired, updating new access token now...");
            $newAccessToken = $this->refreshAccessToken($xeroToken);
            $accessToken = $newAccessToken;
        } else {
            Log::info("Token Not Expired, loading access token now...");
            $accessToken = Crypt::decrypt($xeroToken->access_token);
        }
        Log::info("Token got");
        $user = XeroUser::where('app_name', env('APP_NAME'))->first();
        $XeroTenantId = $user->tenant_id;
        return [
            'Authorization' => 'Bearer ' . $accessToken,
            'Xero-Tenant-Id' => $XeroTenantId,
            'Accept' => 'application/json'
        ];
    }
    protected function isAccessTokenExpired($xeroToken)
    {
        $currentTime = Carbon::now();
        $lastUpdatedTime = new Carbon($xeroToken->updated_at);
        $diffInMinutes = $currentTime->diffInMinutes($lastUpdatedTime);
        return $diffInMinutes > 20;
    }
    protected function refreshAccessToken($xeroToken)
    {
        $http = new Client(['verify' => false]);
        $response = $http->post('https://identity.xero.com/connect/token', [
            'headers' => [
                'authorization' =>  "Basic " . base64_encode(config('services.xero.client_id') . ":" . config('services.xero.client_secret')),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => Crypt::decrypt($xeroToken->refresh_token),
            ],
        ]);
        $responseBody = json_decode((string) $response->getBody(), true);
        Log::info('Response_Body: ' . json_encode($responseBody));
        $accessToken = $responseBody['access_token'];
        $refreshToken = $responseBody['refresh_token'];
        $encryptedToken = Crypt::encrypt($accessToken);
        $encryptedRefreshToken = Crypt::encrypt($refreshToken);
        $xeroToken->access_token = $encryptedToken;
        $xeroToken->refresh_token = $encryptedRefreshToken;
        $xeroToken->save();
        return $accessToken;
    }
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
