<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Providers\WoocommerceProductServiceProvider;

class WoocommerceProductController extends Controller
{
    //
    public function getProducts(Request $request){
        Log::info("getProducts request: ". $request);
        $WoocomService = new WoocommerceProductServiceProvider();
        $data = $WoocomService->getProducts();
        log::info("getProducts: ".  json_encode($data));
    
    }
    public function createProduct(Request $request){
        Log::info("createProduct request: ". $request);
        $WoocomService = new WoocommerceProductServiceProvider();
        // $data = $WoocomService->createProduct($request);

        // log::info("createProduct data: ".  json_encode($data));
        // return $data;
    }
}
