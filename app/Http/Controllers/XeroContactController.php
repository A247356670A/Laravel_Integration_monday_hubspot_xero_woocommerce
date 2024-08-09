<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Providers\XeroContactServiceProvider;

class XeroContactController extends Controller
{
    //
    public function getContacts(Request $request){
        Log::info("getContacts request: ". $request);
        $xeroService = new XeroContactServiceProvider();
        $data = $xeroService->getContacts();
        log::info("getContacts: ".  json_encode($data));
    
    }

    public function getContactsByName(Request $request){
        Log::info("getContactsByName request: ". $request);
        $xeroService = new XeroContactServiceProvider();
        $inputFields = $request->input('payload.inputFields');
        $contactName = $request->contact_name;
        $data = $xeroService->getContactsByName($contactName);
        log::info("getContactsByName: ".  json_encode($data));


    }
}
