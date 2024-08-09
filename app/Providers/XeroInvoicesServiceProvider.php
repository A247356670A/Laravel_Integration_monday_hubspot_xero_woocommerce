<?php

namespace App\Providers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\XeroUser;
use App\Models\XeroToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\ServiceProvider;

class XeroInvoicesServiceProvider extends ServiceProvider
{
    public function __construct()
    {
        $this->apiUrl = "https://api.xero.com/api.xro/2.0/Invoices";
        $xeroReauthService = new XeroReauthServiceProvider();
        $this->headers = $xeroReauthService->setAuthorizationHeader();
    }
    
    public function register(): void
    {
        //
    }
    public function boot(): void
    {
        //
    }
    public function getInvoices()
    {
        $accessHttp = new Client(['verify' => false]);
        $response = $accessHttp->get($this->apiUrl, [
            'headers' => $this->headers,
        ]);
        $data = json_decode($response->getBody(), true);
        return $data;
    }
    public function createInvoice($newInvoiceData){
        $requestData = [
            'Type' => $newInvoiceData['Type'],
            'Contact' => [
                "ContactID" => $newInvoiceData['Contact']['ContactID'],
            ],
            'Date' => $newInvoiceData['Date'],
            'DueDate' => $newInvoiceData['DueDate'],
            'LineAmountTypes' => $newInvoiceData['LineAmountTypes'],
            'LineItems' => $newInvoiceData['LineItems']
        ];
        Log::info("request data: ". json_encode($requestData));
        $accessHttp = new Client(['verify' => false]);
        $response = $accessHttp->post($this->apiUrl, [
            'headers' => $this->headers,
            'json' => $requestData,

        ]);
        $data = json_decode($response->getBody(), true);
        Log::info("createInvoice Respones: ".json_encode($data));
        return $data['Invoices'][0]['InvoiceID'];
    }
    public function updateInvoice($invoiceId, $newInvoiceData){
        $requestData = [
            'Type' => $newInvoiceData['Type'],
            'Contact' => [
                "ContactID" => $newInvoiceData['Contact']['ContactID'],
            ],
            'InvoiceID' => $invoiceId,
            'Date' => $newInvoiceData['Date'],
            'DueDate' => $newInvoiceData['DueDate'],
            'LineAmountTypes' => $newInvoiceData['LineAmountTypes'],
            'LineItems' => $newInvoiceData['LineItems']
        ];
        Log::info("request data: ". json_encode($requestData));
        $accessHttp = new Client(['verify' => false]);
        $response = $accessHttp->post($this->apiUrl, [
            'headers' => $this->headers,
            'json' => $requestData,

        ]);
        $data = json_decode($response->getBody(), true);
        Log::info("updateInvoice Respones: ".json_encode($data));
        return $data['Invoices'][0]['InvoiceID'];
    }
}
