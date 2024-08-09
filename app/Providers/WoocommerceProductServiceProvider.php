<?php

namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class WoocommerceProductServiceProvider extends ServiceProvider
{
    public function __construct()
    {
        $username = config('services.woocommerce.client_id');
        $password = config('services.woocommerce.client_secret');
        $this->apiUrl = "https://woocommerce-monday.shop.luxie.tech/wp-json/wc/v3/products";
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
    public function getProducts()
    {
        $accessHttp = new Client(['verify' => false]);
        $response = $accessHttp->get($this->apiUrl, [
            'headers' => $this->headers,
        ]);
        $data = json_decode($response->getBody(), true);
        return $data;
    }
    public function getProductIdBySku($sku)
    {
        $accessHttp = new Client(['verify' => false]);
        $response = $accessHttp->get($this->apiUrl . '?sku=' . $sku, [
            'headers' => $this->headers,
        ]);
        $data = json_decode($response->getBody(), true);
        return $data[0]['id'] ?? null;
    }
    public function createProduct($data)
    {
        $requestData = [
            'name' => $data['name'],
            'status' => $data['status'],
            'sku' => $data['sku'],
            'stock_status' => $data['stock_status'],
            'regular_price' => $data['regular_price'],
            'sale_price' => $data['sale_price'],
            'stock_quantity' => $data['stock_quantity'],
            'low_stock_amount' => $data['low_stock_amount'],
            'categories' => $data['categories'],
            'tags' => $data['tags'],
        ];
        Log::info("createProduct request data: " . json_encode($requestData));
        $accessHttp = new Client(['verify' => false]);
        $response = $accessHttp->post($this->apiUrl, [
            'headers' => $this->headers,
            'json' => $requestData,

        ]);
        $data = json_decode($response->getBody(), true);
        Log::info("Respones: " . json_encode($data));

        return $data;
    }
    public function updateProduct($productId,$data)
    {
        $requestData = [
            'name' => $data['name'],
            'status' => $data['status'],
            'sku' => $data['sku'],
            'stock_status' => $data['stock_status'],
            'regular_price' => $data['regular_price'],
            'sale_price' => $data['sale_price'],
            'stock_quantity' => $data['stock_quantity'],
            'low_stock_amount' => $data['low_stock_amount'],
            'categories' => $data['categories'],
            'tags' => $data['tags'],
        ];
        Log::info("updateProduct request data: " . json_encode($requestData));
        $accessHttp = new Client(['verify' => false]);
        $response = $accessHttp->put($this->apiUrl. '/' . $productId, [
            'headers' => $this->headers,
            'json' => $requestData,

        ]);
        $data = json_decode($response->getBody(), true);
        Log::info("updateProduct Respones: " . json_encode($data));

        return $data;
    }
}
