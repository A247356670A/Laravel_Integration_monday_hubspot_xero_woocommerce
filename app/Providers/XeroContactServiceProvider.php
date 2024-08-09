<?php

namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class XeroContactServiceProvider extends ServiceProvider
{
    public function __construct()
    {
        $this->apiUrl = "https://api.xero.com/api.xro/2.0/Contacts";
        $xeroReauthService = new XeroReauthServiceProvider();
        $this->headers = $xeroReauthService->setAuthorizationHeader();
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
    public function getContacts()
    {
        $accessHttp = new Client(['verify' => false]);
        $response = $accessHttp->get($this->apiUrl, [
            'headers' => $this->headers,
        ]);
        $data = json_decode($response->getBody(), true);
        return $data;
    }
    public function getContactsByName(String $name)
    {
        $accessHttp = new Client(['verify' => false]);
        $requestURL = $this->apiUrl . "?where=Name=\"$name\"";
        $response = $accessHttp->get($requestURL, [
            'headers' => $this->headers,
        ]);
        $data = json_decode($response->getBody(), true);
        return $data;
    }
    public function createContact($newContactData)
    {
        $requestData = [
            'Name' => $newContactData['contactName'],
            'FirstName' => $newContactData['contactFirstName'],
            'LastName' => $newContactData['contactLastName'],
            'EmailAddress' => $newContactData['contactEmail']['text'],
            'BankAccountDetails' => $newContactData['contactBankDetails'],
            'Addresses' => [
                [
                    'AddressType' => "POBOX",
                    'AddressLine1' => $newContactData['contactLocation']['address'],
                    'City' => $newContactData['contactLocation']['city']['long_name'],
                    'Country' => $newContactData['contactLocation']['country']['long_name'],

                ]
            ],
            'Phones' => [
                [
                    'PhoneType' => "DEFAULT",
                    'PhoneNumber' => $newContactData['contactPhone']['phone'],
                ]
            ]
        ];
        Log::info("createContact request data: " . json_encode($requestData));
        $accessHttp = new Client(['verify' => false]);
        $response = $accessHttp->post($this->apiUrl, [
            'headers' => $this->headers,
            'json' => $requestData,

        ]);
        $data = json_decode($response->getBody(), true);
        Log::info("Respones: " . json_encode($data));
        // Log::info("Respones id : ".json_encode($data['Contacts'][0]['ContactID']));

        return $data['Contacts'][0]['ContactID'];
    }
}
