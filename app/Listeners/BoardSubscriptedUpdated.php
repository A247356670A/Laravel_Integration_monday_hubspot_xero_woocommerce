<?php

namespace App\Listeners;

use App\Models\Board;
use App\Events\BoardUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\InteractsWithQueue;
use App\Http\Controllers\webhookController;
use Illuminate\Contracts\Queue\ShouldQueue;

class BoardSubscriptedUpdated
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BoardUpdated $event): void
    {
        Log::info("BoardUpdated listeners running...");
        $board = $event->board;
        Log::info("board: " . $board);
        $webhookUrl = $board->webhookUrl_test;
        // $token = config("services.monday.token");

        Log::info('BoardSubscriptedUpdatedListener: Board updated at board_id: ' . $board->board_id . ' is subscripted: ' . $board->is_subscripted);
        Log::info('BoardSubscriptedUpdatedListener: webhookUrl: ' . $webhookUrl);

        if (!empty($webhookUrl)) {
            Log::info('board_id'. $board->board_id);
            $requestData = [
                'payload' => [
                    'inputFields' =>[
                        'board_id' => $board->board_id,
                    ],
                ],
            ];
            try {
                $controller = new webhookController();
                $controller->webhookEvent(new Request($requestData), $webhookUrl);

            } catch (\Exception $e) {
                Log::error('Failed to update Monday.com: ' . $e->getMessage());
            }
        }
    }
}
