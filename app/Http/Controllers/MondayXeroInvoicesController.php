<?php

namespace App\Http\Controllers;

use App\Models\XeroInvoice;
use App\Providers\MondayServiceProvider;
use App\Providers\XeroContactServiceProvider;
use App\Providers\XeroInvoicesServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MondayXeroInvoicesController extends Controller
{
    protected $TypeColumnId = "status_18__1";
    protected $ContactsColumnId = "connect_boards__1";
    protected $IssuedDateColumnId = "date__1";
    protected $DueDateColumnId = "date_1__1";
    protected $LineAmountTypeColumnId = "status_10__1";
    protected $QuantityColumnId = "numbers__1";
    protected $UnitAmountColumnId = "numbers4__1";
    protected $DiscountRateColumnId = "numbers8__1";
    protected $DescriptionColumnId = "text__1";
    // contact
    protected $NameColumnId = "text__1";
    protected $FirstNameColumnId = "text8__1";
    protected $LastNameColumnId = "text_1__1";
    protected $LocationColumnId = "location__1";
    protected $EmailColumnId = "email__1";
    protected $BankAccountDetailsColumnId = "text9__1";
    protected $PhoneColumnId = "phone__1";

    //
    protected function getDropdownValue($board_id, $column_id, $index)
    {
        $mondayService = new MondayServiceProvider();
        $dropdownValues = json_decode($mondayService->getDropdownValues($board_id, $column_id), true);
        $labels = $dropdownValues['data']['boards'][0]['columns'][0]['settings_str'];
        $labelsData = json_decode($labels, true);
        $value = $labelsData['labels'][(string)$index];
        return $value;
    }
    protected function getSubItemsValue($item_id)
    {
        $mondayService = new MondayServiceProvider();
        $data = json_decode($mondayService->getMondayItems($item_id), true);
        $subItemData = $data["data"]["items"][0];
        Log::info("getSubItems itemData:::: " . json_encode($data));
        $columnValues = [];
        foreach ($subItemData['column_values'] as $column) {
            $id = $column['id'];
            $value = json_decode($column['value'], true);
            $columnValues[$id] = $value;
        }
        $subItemName = $data["data"]["items"][0]["name"];
        Log::info("subItemName: " . $subItemName);
        $quantity = $columnValues[$this->QuantityColumnId];
        $unitAmount = $columnValues[$this->UnitAmountColumnId];
        $discountRate = $columnValues[$this->DiscountRateColumnId];
        $description = $columnValues[$this->DescriptionColumnId];
        return [
            'subItemName' => $subItemName,
            'quantity' => $quantity,
            'unitAmount' => $unitAmount,
            'discountRate' => $discountRate,
            'description' => $description
        ];
    }
    protected function getContactsValue($item_id)
    {
        $mondayService = new MondayServiceProvider();
        $data = json_decode($mondayService->getMondayItems($item_id), true);
        $contactData = $data["data"]["items"][0];
        Log::info("getContactsValue itemData:::: " . json_encode($data));
        $columnValues = [];
        foreach ($contactData['column_values'] as $column) {
            $id = $column['id'];
            $value = json_decode($column['value'], true);
            $columnValues[$id] = $value;
        }
        $contactName = $columnValues[$this->NameColumnId];
        $contactFirstName = $columnValues[$this->FirstNameColumnId];
        $contactLastName = $columnValues[$this->LastNameColumnId];
        $contactLocation = $columnValues[$this->LocationColumnId];
        $contactEmail = $columnValues[$this->EmailColumnId];
        $contactBankDetails = $columnValues[$this->BankAccountDetailsColumnId];
        $contactPhone = $columnValues[$this->PhoneColumnId];

        return [
            'contactName' => $contactName,
            'contactFirstName' => $contactFirstName,
            'contactLastName' => $contactLastName,
            'contactLocation' => $contactLocation,
            'contactEmail' => $contactEmail,
            'contactBankDetails' => $contactBankDetails,
            'contactPhone' => $contactPhone

        ];
    }
    protected function handleValuesFromMonday($board_id, $item_id)
    {
        $mondayService = new MondayServiceProvider();
        $data = json_decode($mondayService->getMondayItems($item_id), true);
        $itemData = $data["data"]["items"][0];
        $columnValues = [];
        foreach ($itemData['column_values'] as $column) {
            $id = $column['id'];
            $value = json_decode($column['value'], true);
            $columnValues[$id] = $value;
        }
        $typeID = $columnValues[$this->TypeColumnId]['index'] ?? null;
        $typeValue = $this->getDropdownValue($board_id, $this->TypeColumnId, $typeID);
        $contactItems = $columnValues[$this->ContactsColumnId] ?? null;
        $contactItemsIDs = [];
        if ($contactItems && isset($contactItems['linkedPulseIds'])) {
            foreach ($contactItems['linkedPulseIds'] as $linkedPulse) {
                $contactItemsIDs[] = $linkedPulse['linkedPulseId'];
            }
        }
        $contactItemsValues = [];
        foreach ($contactItemsIDs as $contactItemsID) {
            $contactItemsValues[] = $this->getContactsValue($contactItemsID);
        }
        $issuedDate = $columnValues[$this->IssuedDateColumnId]['date'] ?? null;
        $dueDate = $columnValues[$this->DueDateColumnId]['date'] ?? null;
        $lineAmountTypeID = $columnValues[$this->LineAmountTypeColumnId]['index'] ?? null;
        $lineAmountTypeValue = $this->getDropdownValue($board_id, $this->LineAmountTypeColumnId, $lineAmountTypeID);
        $subitems = $columnValues['subitems__1'] ?? null;
        $subitemsIDs = [];
        if ($subitems && isset($subitems['linkedPulseIds'])) {
            foreach ($subitems['linkedPulseIds'] as $linkedPulse) {
                $subitemsIDs[] = $linkedPulse['linkedPulseId'];
            }
        }
        $subitemsValues = [];
        foreach ($subitemsIDs as $subitemsID) {
            $subitemsValues[] = $this->getSubItemsValue($subitemsID);
        }
        Log::info("typeValue: " . json_encode($typeValue));
        Log::info("contactItems linkedPulseIds: " . json_encode($contactItemsIDs));
        Log::info("contactItemsValues: " . json_encode($contactItemsValues));
        Log::info("Issued Date: " . json_encode($issuedDate));
        Log::info("Due Date: " . json_encode($dueDate));
        Log::info("lineAmountTypeValue: " . json_encode($lineAmountTypeValue));
        Log::info("Subitems linkedPulseIds: " . json_encode($subitemsIDs));
        Log::info("subitemsValues: " . json_encode($subitemsValues));

        return [
            'typeValue' => $typeValue,
            'contactItemsValues' => $contactItemsValues,
            'issuedDate' => $issuedDate,
            'dueDate' => $dueDate,
            'lineAmountTypeValue' => $lineAmountTypeValue,
            'subitemsValues' => $subitemsValues,
        ];
    }
    protected function handleContacts($contactItemsValues)
    {
        $xeroContactService = new XeroContactServiceProvider();
        $contactName = $contactItemsValues[0]["contactName"];
        $contactData = $xeroContactService->getContactsByName($contactName);
        Log::info("Check contacts: " . json_encode($contactData));
        if (!$contactData['Contacts']) {
            Log::info("Doent have Contacts, Creating Contact on Xero now...");
            return $xeroContactService->createContact($contactItemsValues[0]);
        } else {
            Log::info("Contact exsits");
            return $contactData['Contacts'][0]['ContactID'];
        }
    }
    public function createXeroInvoiceFromMonday(Request $request)
    {
        $xeroInvoiceService = new XeroInvoicesServiceProvider();
        Log::info("createXeroInvoiceFromMonday: " . $request);
        $inputFields = $request->input('payload.inputFields');
        $board_id = $inputFields["boardId"];
        $item_id = $inputFields["itemId"];

        $handledValues = $this->handleValuesFromMonday($board_id, $item_id);

        $typeValue = $handledValues['typeValue'];
        $contactItemsValues = $handledValues['contactItemsValues'];
        $issuedDate = $handledValues['issuedDate'];
        $dueDate = $handledValues['dueDate'];
        $lineAmountTypeValue = $handledValues['lineAmountTypeValue'];
        $subitemsValues = $handledValues['subitemsValues'];
        // check contacts
        $contactID = $this->handleContacts($contactItemsValues);
        Log::info("contactID: " . json_encode($contactID));
        $newInvoiceData = [
            'Type' => $typeValue,
            'Contact' => [
                "ContactID" => $contactID,
            ],
            'Date' => $issuedDate,
            'DueDate' => $dueDate,
            'LineAmountTypes' => $lineAmountTypeValue,
            'LineItems' => array_map(function ($subitem) {
                Log::info("subitems::::::   " . json_encode($subitem));
                return [
                    'Description' => $subitem['description'],
                    'Quantity' => $subitem['quantity'],
                    'UnitAmount' => $subitem['unitAmount'],
                    "AccountCode" => "200",
                    'DiscountRate' => $subitem['discountRate'],
                ];
            }, $subitemsValues)
        ];


        // check invoices
        $xeroIncovice = XeroInvoice::where("item_id", $item_id)->first();
        if ($xeroIncovice && $xeroIncovice->invoice_id != null) {
            //update
            Log::info("Invoice record found, updating...");
            $invoice_id = $xeroIncovice->invoice_id;
            $invoiceId = $xeroInvoiceService->updateInvoice($invoice_id, $newInvoiceData);
            return $invoiceId;

        } else {
            Log::info("Creating new Invoice...");
            $xeroIncovice = new XeroInvoice();
            $xeroIncovice->item_id = $item_id;
            $invoiceId = $xeroInvoiceService->createInvoice($newInvoiceData);
            Log::info("Invoice created id: " . json_encode($invoiceId));
            $xeroIncovice->invoice_id = $invoiceId;
            $xeroIncovice->save();
            return $invoiceId;
        }
    }
}
