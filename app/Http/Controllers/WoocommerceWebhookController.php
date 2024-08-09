<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WoocommerceWebhookController extends Controller
{
    //
    public function webhookEvent(Request $request)
    {
        Log::info("When Woocommerce webhook event triggered... " . $request . "..............ends");
        $inputFields = $request->input('payload.inputFields');
    }
    public function productCreatedEvent(Request $request)
    {
        Log::info("When Woocommerce productCreatedEvent triggered... " . $request . "..............ends");
        $woocommerceController = new WoocommerceMondayProductsController();
        $woocommerceController->updateMondayBoardFromWoocommerce($request);
    }
    public function productUpdateEvent(Request $request)
    {
        Log::info("When Woocommerce productUpdateEvent triggered... " . $request . "..............ends");
        $woocommerceController = new WoocommerceMondayProductsController();
        $woocommerceController->updateMondayBoardFromWoocommerce($request);
    }
}
