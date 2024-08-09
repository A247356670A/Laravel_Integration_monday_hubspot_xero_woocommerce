<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Providers\MondayServiceProvider;

class MergeMondayBoardsController extends Controller
{
    /**
     * Merge booking and client boards from Monday.com.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mergeBookingClientBoards(Request $request)
    {
        Log::info("mergeBookingClientBoards Request; " . $request);

        $fields = [
            [
                'id' => 'text__1',
                'title' => 'Merged Name',
                'outboundType' => 'text',
                'inboundTypes' => ['text, text_array, numeric, date, date_time, boolean']
            ],
            [
                'id' => 'numbers__1',
                'title' => 'Number of technicians',
                'outboundType' => 'numeric',
                'inboundTypes' => ['numeric']
            ],
            [
                'id' => 'date4',
                'title' => 'Merged Date',
                'outboundType' => 'date',
                'inboundTypes' => ['date', 'date_time']
            ],
            [
                'id' => 'location',
                'title' => 'Merged Location',
                'outboundType' => 'text',
                'inboundTypes' => ['text', 'location']
            ],
        ];
        return response()->json($fields, 200);
    }
}
