<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WoocommerceAuthController extends Controller
{
    //
    protected $woocommerce_scopes = [
        'read_write',
    ];

    # redirect to xero authorization
    public function redirectToXero()
    {
        $scopes = $this->woocommerce_scopes;
        $query = http_build_query([
            'client_id' => config('services.xero.client_id'),
            'return_url' => config('services.xero.redirect'),
            'scope' => implode(' ', $scopes),
            'response_type' => 'code',
            'state' => bin2hex(random_bytes(16)),
        ]);
        // Log::info("redirectURL: " . redirect('https://login.xero.com/identity/connect/authorize?' . $query));
        return redirect('https://login.xero.com/identity/connect/authorize?' . $query);
    }
}
