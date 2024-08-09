<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XeroWebhookController extends Controller
{
    //
    public function webhookEvent(Request $request)
    {
        Log::info("When Xero webhook event triggered... " . $request . "..............ends");
        $inputFields = $request->input('payload.inputFields');

       
    }
}
