<?php

namespace App\Http\Controllers;

use App\Providers\TextServiceProvider;
use DateTime;
use Exception;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Providers\MondayServiceProvider;

class WebhookController extends Controller
{
    //
    public function webhookEvent(Request $request, $webhookUrl)
    {
        Log::info("When webhook event triggered... " . $request . "..............ends");
        $inputFields = $request->input('payload.inputFields');

        $mondayService = new MondayServiceProvider();
        Log::info('webhookUrl ' . $webhookUrl);
        $board_id = $inputFields["boardId"];
        $item_id = $inputFields["itemId"];
        Log::info("board_id" . $board_id . "item_id" . $item_id);
        $item_data = $mondayService->getMondayItemsLists($board_id, $item_id);
        $responseContent = json_decode($item_data, true);
        Log::info("item_data" . json_encode($responseContent));

        if (isset($responseContent['data']['boards'][0]['items_page']['items'][0]['column_values'])) {
            $columnValues = $responseContent['data']['boards'][0]['items_page']['items'][0]['column_values'];
            $name = $responseContent['data']['boards'][0]['items_page']['items'][0]['name'];
            $text = null;
            $text_1 = null;
            $status = null;
            $people = null;
            $email = null;
            $date = null;
            $numbers = null;

            foreach ($columnValues as $column) {
                switch ($column['id']) {
                    case 'text':
                        $text = $column['value'];
                        break;
                    case 'text_1':
                        $text_1 = $column['value'];
                        break;
                    case 'status':
                        $status = json_decode($column['value'], true)['index'];
                        break;
                    case 'people':
                        $people = json_decode($column['value'], true)['personsAndTeams'][0]['id'];
                        break;
                    case 'email':
                        $email = json_decode($column['value'], true)['text'];
                        break;
                    case 'date':
                        $date = json_decode($column['value'], true)['date'];
                        break;
                    case 'numbers':
                        $numbers = (int)json_decode($column['value'], true);
                        break;
                }
            }

            Log::info("Extracted values - text: $text, text_1: $text_1, status: $status, people: $people, email: $email, date: $date, numbers: $numbers");
        } else {
            Log::warning("Item data format is not as expected.");
        }
        try {
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => env('SIGNING_SECRET'),
                'X-API-VERSION' => '2024-04',
            ];
            $payload = [
                'trigger' => [
                    'outputFields' => [
                        "dynamicMappingKey" => [
                            "name" => $name,
                            "text" => $text,
                            "text_1" => $text_1,
                            "status" => ["index" => $status],
                            "email" => $email,
                            "people" => ["identifierType" => "email", "identifierValue" => [$email]],
                            "date" => $date,
                            "numbers" => $status,
                        ]
                    ],
                ],
            ];
            Log::info("payload: " . json_encode($payload));
            $response = Http::withHeaders($headers)->post($webhookUrl, $payload);
            if ($response->successful()) {
                Log::info('Webhook sent successfully. Response: ' . json_encode($response->json()));
            } else {
                Log::error('Failed to send webhook. Response: ' . json_encode($response->json()));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send webhook: ' . $e->getMessage());
        }
    }
    public function mergeEvent(Request $request, $webhookUrl)
    {
        $mondayService = new MondayServiceProvider();
        $textService = new TextServiceProvider();
        $locationToUpdate = '';
        $clientLocation = new Location();

        $inputFields = $request->input('payload.inputFields');
        $bookingItemId = $inputFields["bookingItemId"];
        $clientItemId = $inputFields["clientItemId"];
        $targetItemId = $inputFields["targetItemId"];
        $targetBoardId = $mondayService->getMondayBoardIdWithItemId($targetItemId);

        $bookingResponseContent = json_decode($mondayService->getMondayItems($bookingItemId), true);
        $clientResponseContent = json_decode($mondayService->getMondayItems($clientItemId), true);

        if (isset($bookingResponseContent['data']['items'][0]['column_values']) && isset($clientResponseContent['data']['items'][0]['column_values'])) {
            $bookingColumnValues = $bookingResponseContent['data']['items'][0]['column_values'];
            $clientColumnValues = $clientResponseContent['data']['items'][0]['column_values'];
            foreach ($bookingColumnValues as $column) {
                switch ($column['id']) {
                    case 'numbers':
                        $bookingTravelTime = trim($column['value'], '"');
                        break;
                    case 'numbers48':
                        $bookingJobLength = trim($column['value'], '"');
                        break;
                    case 'numbers1':
                        $bookingJobEndTime = trim($column['value'], '"');
                        break;
                    case 'date_1':
                        $bookingJobDateValue = json_decode($column['value'], true);
                        if (isset($bookingJobDateValue['date'])) {
                            $bookingJobDate = new DateTime($bookingJobDateValue['date']);
                        } else {
                            $bookingJobDate = null;
                        }
                        break;
                    case 'numbers6':
                        $bookingNOT = trim($column['value'], '"');
                        break;
                    case 'text__1':
                        $bookingNameA = trim($column['value'], '"');
                        break;
                }
            }
            foreach ($clientColumnValues as $column) {
                switch ($column['id']) {
                    case 'date4':
                        $clientDateValue = json_decode($column['value'], true);
                        if (isset($clientDateValue['date'])) {
                            $clientDate = new DateTime($clientDateValue['date']);
                        } else {
                            $clientDate = null;
                        }
                    case 'text__1':
                        $clientNameB = trim($column['value'], '"');
                        break;
                    case 'numbers__1':
                        $clientNOT = trim($column['value'], '"');
                        break;
                    case 'location':
                        $clientLocationValue = json_decode($column['value'], true);
                        $clientLocation = $textService->saveLocation($clientLocationValue);
                }
            }
            $daysToAdd = $bookingJobLength + $bookingTravelTime;
            $text__1 = $bookingNameA . " " . $clientNameB;
            $numbers__1 = $bookingNOT + $clientNOT;
            $date4 = $clientDate->modify("+$daysToAdd days")->format("Y-m-d");
            $locationText = $clientLocation->Address;
        } else {
            Log::warning("Item data format is not as expected.");
        }
        try {
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => env('SIGNING_SECRET'),
                'X-API-VERSION' => '2024-04',
            ];
            $payload = [
                'trigger' => [
                    'outputFields' => [
                        "dynamicMappingKey" => [
                            "text__1" => $text__1,
                            "numbers__1" => $numbers__1,
                            "date4" => $date4,
                            "location" => $locationText,
                        ]
                    ],
                ],
            ];
            $response = Http::withHeaders($headers)->post($webhookUrl, $payload);
            if ($response->successful()) {
                Log::info('Webhook sent successfully. Response: ' . json_encode($response->json()));
            } else {
                Log::error('Failed to send webhook. Response: ' . json_encode($response->json()));
            }
        } catch (Exception $e) {
            Log::error('Failed to send webhook: ' . $e->getMessage());
        }
    }
    public function updatelocation(Request $request)
    {
        Log::info("itemUpdated Request: " . $request);
        try {
            $inputFields = $request->input('payload.inputFields');
            $board_id = $inputFields['boardId'];
            $item_id = $inputFields['itemId'];
            $columnId = $inputFields['columnId'];

            $mondayService = new MondayServiceProvider();
            $locationText = $mondayService->getMondayItem($item_id, $columnId);
            $location = Location::where('Address', $locationText)->first();
            $locationToUpdate = $location->toString();
            if ($location) {
                try {
                    $data = $mondayService->updateLocationColumn($board_id, $item_id, 'location', $locationToUpdate);
                    Log::info('updateMondayItem: ' . json_encode($data));
                } catch (Exception $e) {
                    Log::error($e->getMessage());
                }
            } else {
                Log::error('Location Not Found!');
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    
}
