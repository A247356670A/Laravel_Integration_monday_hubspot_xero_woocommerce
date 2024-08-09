<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use App\Providers\MondayServiceProvider;

class MondayItemController extends Controller
{
    /**
     * Get an item from Monday.com.
     *
     * @param Request $request
     * @return string monday item
     */
    public function getMondayItem(Request $request)
    {
        $inputFields = $request->input('payload.inputFields');
        $item_id = $inputFields["itemId"];
        $column_id = $inputFields["columnId"];
        $mondayService = new MondayServiceProvider();
        try {
            $item = $mondayService->getMondayItem($item_id, $column_id);
            return $item;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return "error: " . $e->getMessage();
        }
    }
    /**
     * Update an item on Monday.com.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMondayItem(Request $request)
    {
        $inputFields = $request->input('payload.inputFields');
        $board_id = $inputFields["boardId"];
        $item_id = $inputFields["itemId"];
        $column_id = $inputFields["columnId"];
        $valueToUpdate = json_encode($inputFields['value']);
        $mondayService = new MondayServiceProvider();
        try {
            $data = $mondayService->updateMondayItem($board_id, $item_id, $column_id, $valueToUpdate);
            return response()->json([
                'Item updated' => "success",
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'Item updated' => "failed",
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Triggered mergeEvent when an item is updated, to handle booking-client mapping.
     *
     * @param Request $request
     * @return void
     */
    public function itemUpdatedTriggerForBookingClientMapping(Request $request)
    {
        Log::info("itemUpdatedTriggerForBookingClientMapping Request: " . $request);
        try {
            $mondayService = new MondayServiceProvider();
            $inputFields = $request->input('payload.inputFields');
            $board_id = $inputFields['boardId'];
            $item_id = $inputFields['itemId'];
            $bookingResponseContent = json_decode($mondayService->getMondayItem($item_id, 'connect_boards39__1'), true);
            $clientResponseContent = json_decode($mondayService->getMondayItem($item_id, 'connect_boards396__1'), true);
            $targetResponseContent = json_decode($mondayService->getMondayItem($item_id, 'connect_boards1__1'), true);
            $bookingItemId = $bookingResponseContent['linkedPulseIds'][0]['linkedPulseId'];
            $clientItemId = $clientResponseContent['linkedPulseIds'][0]['linkedPulseId'];
            $targetItemId = $targetResponseContent['linkedPulseIds'][0]['linkedPulseId'];

            $board = Board::where('board_id', $board_id)->first();
            if (!$board) {
                Log::info('board not found: ' . $board_id);
            }
            $webhookUrl = $board->webhookUrl_mapping;

            if (!empty($webhookUrl)) {
                $requestData = [
                    'payload' => [
                        'inputFields' => [
                            'bookingItemId' => $bookingItemId,
                            'clientItemId' => $clientItemId,
                            'targetItemId' => $targetItemId,
                        ],
                    ],
                ];
                try {
                    $controller = new webhookController();
                    $controller->mergeEvent(new Request($requestData), $webhookUrl);
                } catch (Exception $e) {
                    Log::error('itemUpdatedTriggerForBookingClientMapping Failed to update Monday.com: ' . $e->getMessage());
                }
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
    public function searchItemByColumnValues(){
        $board_id = 7056551678;
        $columnValue = "testItemSku0200";
        $column_id = 'text__1';
        $mondayService = new MondayServiceProvider();

        try {
            $data = $mondayService->searchItemByColumnValue($board_id, $column_id, $columnValue);
            Log::info("searchItemByColumnValues: ". json_encode($data));
            return response()->json([
                'Item updated' => "success",
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'Item updated' => "failed",
                'error' => $e->getMessage()
            ]);
        }
    }
}
