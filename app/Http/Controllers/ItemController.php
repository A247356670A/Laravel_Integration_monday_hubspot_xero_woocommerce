<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Board;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Providers\MondayServiceProvider;

class ItemController extends Controller
{
    /**
     * Get a list of items by column from Monday.com with Internal request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getItemsListByColumn(Request $request)
    {
        Log::info("request: ". $request);
        $board_id = strval($request->board_id);
        $item_id = strval($request->item_id);
        $column_id = $request->column_id;
        Log::info("board_id: ". $board_id. " item_id: ". $item_id);
        $mondayService = new MondayServiceProvider();
        try {
            $data = $mondayService->getMondayItemsListByColumn($board_id, $item_id, $column_id);
            return response()->json([
                'Items got' => "success", 
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'Items got' => "failed",
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update an item on Monday.com with Internal request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateItem(Request $request)
    {
        $board_id = $request->board_id;
        $item_id = $request->item_id;
        $column_id = $request->column_id;
        $valueToUpdate = json_encode($request->valueToUpdate);
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
}
