<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Providers\WoocommerceOrderServiceProvider;

class WoocommerceOrderController extends Controller
{
    //
    public function getOrders(Request $request){
        Log::info("getOrders request: ". $request);
        $WoocomService = new WoocommerceOrderServiceProvider();
        $data = $WoocomService->getOrders();
        log::info("getOrders: ".  json_encode($data));
    
    }
    public function createOrder(Request $request){
        Log::info("createOrder request: ". $request);
        $WoocomService = new WoocommerceOrderServiceProvider();
        $data = $WoocomService->createOrder();
        log::info("createOrder: ".  json_encode($data));
    }
    
}
