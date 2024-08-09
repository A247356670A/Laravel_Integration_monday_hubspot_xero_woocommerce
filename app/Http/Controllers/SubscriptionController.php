<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Providers\MondayServiceProvider;

class SubscriptionController extends Controller
{
    /**
     * Handle subscription requests with webhookUrl_type
     *
     * @param Request $request
     * @param string $webhookUrl_type
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleSubscribes(Request $request, $webhookUrl_type)
    {
        Log::info("handleSubscribes Request: " . $request);
        $mondayService = new MondayServiceProvider();
        $payload = $request->input('payload');
        $inputFields = $payload['inputFields'];
        $webhookUrl = $payload["webhookUrl"];

        try {
            $board_id = $inputFields["boardId"];
            $board = Board::where('board_id', $board_id)->first();
            $board_name = $mondayService->getMondayBoardName($board_id);
            if (!$board) {
                $board = new Board();
                $board->board_id = $board_id;
                $board->board_name = $board_name;
            }
            $board->$webhookUrl_type = $webhookUrl;
            Log::info('handleSubscribes webhookUrl: ' . $webhookUrl);
            $board->save();
            return response()->json(['message' => 'Board updated successfully'], 200);
        } catch (Exception $e) {
            Log::error("Subscription Error: " . $e->getMessage());
            return response()->json(['error' => 'Subscription failed'], 500);
        }
    }
    /**
     * Handle unsubscription requests with webhookUrl_type
     *  
     * @param Request $request
     * @param string $webhookUrl_type
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleUnSubscribes(Request $request, $webhookUrl_type)
    {
        Log::info('handleUnSubscribes request: ' . $request);
        $payload = $request->input('payload');
        $subscription_id = $payload["webhookId"];
        $webhookUrl =  "https://api-gw.monday.com/automations/apps-events/" . $subscription_id;
        try {
            $board = Board::where($webhookUrl_type, $webhookUrl)->first();
            if ($board) {
                $board->$webhookUrl_type = null;
                $board->save();
                return response()->json(['message' => 'Board Unsubscripte successfully'], 200);
            } else {
                Log::error('board not found! ');
                return response()->json(['error' => 'board not found!'], 500);
            }
        } catch (Exception $e) {
            Log::error("Subscription Error: " . $e->getMessage());
            return response()->json(['error' => 'Subscription failed'], 500);
        }
    }
    /**
     * Handle test subscription requests.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleTestSubscribe(Request $request)
    {
        return $this->handleSubscribes($request, 'webhookUrl_test');
    }
    /**
     * Handle test unsubscription requests.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleTestUnSubscribe(Request $request)
    {
        return $this->handleUnSubscribes($request, 'webhookUrl_test');
    }
    /**
     * Handle mapping subscription requests.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleMappingSubscribe(Request $request)
    {
        return $this->handleSubscribes($request, 'webhookUrl_mapping');
    }
    /**
     * Handle mapping unsubscription requests.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleMappingUnSubscribe(Request $request)
    {
        return $this->handleUnSubscribes($request, 'webhookUrl_mapping');
    }

    public function handleWoocommerceSubscribe(Request $request)
    {
        return $this->handleSubscribes($request, 'webhookUrl_woocommerce');
    }
    public function handleWoocommerceUnSubscribe(Request $request)
    {
        return $this->handleUnSubscribes($request, 'webhookUrl_woocommerce');
    }
}
