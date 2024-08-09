<?php

namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class WoocommerceOrderServiceProvider extends ServiceProvider
{
    public function __construct()
    {
        $username = config('services.woocommerce.client_id');
        $password = config('services.woocommerce.client_secret');
        $this->apiUrl = "https://woocommerce-monday.shop.luxie.tech/wp-json/wc/v3/orders";
        $this->Authorization = base64_encode("$username:$password");
        $this->headers = [
            'Authorization' => ['Basic '. $this->Authorization],
            'Accept' => 'application/json',
        ];
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
    public function getOrders()
    {
        $accessHttp = new Client(['verify' => false]);
        $response = $accessHttp->get($this->apiUrl, [
            'headers' => $this->headers,
        ]);
        $data = json_decode($response->getBody(), true);
        return $data;
    }
    public function createOrder()
    {
        $requestData = [
                "payment_method" => "bacs",
                "payment_method_title" => "Direct Bank Transfer",
                "set_paid" => true,
                "billing" => [
                  "first_name" => "John",
                  "last_name" => "Doe",
                  "address_1" => "969 Market",
                  "address_2" => "",
                  "city" => "San Francisco",
                  "state" => "CA",
                  "postcode" => "94103",
                  "country" => "US",
                  "email" => "john.doe@example.com",
                  "phone" => "(555) 555-5555"
                ],
                "shipping" => [
                  "first_name" => "John",
                  "last_name"=> "Doe",
                  "address_1" => "969 Market",
                  "address_2" => "",
                  "city" => "San Francisco",
                  "state" => "CA",
                  "postcode" => "94103",
                  "country" => "US"
                ],
                "line_items" => [
                  [
                    "product_id" => 93,
                    "quantity" => 2
                  ],
                  [
                    "product_id" => 22,
                    "variation_id" => 23,
                    "quantity" => 1
                  ]
                ],
                "shipping_lines" => [
                  [
                    "method_id" => "flat_rate",
                    "method_title" => "Flat Rate",
                    "total" => "10.00"
                  ]
                ]
        ];
        Log::info("createOrder request data: " . json_encode($requestData));
        $accessHttp = new Client(['verify' => false]);
        $response = $accessHttp->post($this->apiUrl, [
            'headers' => $this->headers,
            'json' => $requestData,

        ]);
        $data = json_decode($response->getBody(), true);
        Log::info("Respones: " . json_encode($data));

        return $data;
    }

}
