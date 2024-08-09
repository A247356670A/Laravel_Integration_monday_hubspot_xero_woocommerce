<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Providers\XeroInvoicesServiceProvider;

class XeroInvoiceController extends Controller
{
    //
    public function getInvoices(Request $request){
        Log::info("getInvoices request: ". $request);
        $xeroService = new XeroInvoicesServiceProvider();
        $data = $xeroService->getInvoices();
        Log::info('Invoices Details: ' . json_encode($data));

        if (isset($data['Invoices']) && is_array($data['Invoices'])) {
            $invoices = $data['Invoices'];
            $invoiceDetails = [];

            foreach ($invoices as $invoice) {
                $invoiceDetails[] = [
                    'InvoiceID' => $invoice['InvoiceID'],
                    'InvoiceNumber' => $invoice['InvoiceNumber'],
                ];
            }

            echo response()->json($invoiceDetails);
            return response()->json($invoiceDetails);
        } else {
            Log::info('No Invoices found');
            echo response()->json(['message' => 'No Invoices found'], 404);
            return response()->json(['message' => 'No Invoices found'], 404);
        }
    
    }
    
}
