<?php

namespace App\Http\Controllers;

use App\Providers\MondayServiceProvider;
use Exception;
use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BoardController extends Controller
{
    /**
     * Get a board from Monday.com by board_id.
     * Get board raw data, to access, use: 
     * $responseContent['data']['boards'][0]['items_page']['item'][0]['column_values']
     *
     * @param Request $request
     * @return string board contents
     */
    public function getBoard(Request $request)
    {
        $board_id = $request->board_id;
        $mondayService = new MondayServiceProvider();
        $data = $mondayService->getMondayBoard($board_id);
        $responseContent = json_decode($data, true);
        echo json_encode($responseContent);
        return json_encode($responseContent);
    }
    /**
     * Create a new board on Monday.com.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createBoard(Request $request)
    {
        $board_name = $request->board_name;
        $mondayService = new MondayServiceProvider();
        $board_id = $mondayService->createMondayBoard($board_name);
        try {
            $board = Board::where('board_id', $board_id)->first();
            if (!$board) {
                $board = new Board();
            }
            $board->board_id = $board_id;
            $board->board_name = $board_name;
            $board->save();

            return response()->json([
                'board created' => "success",
                'board' => $board
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'board created' => "failed",
                'error' => $e->getMessage()
            ]);
        }
    }
    /**
     * Delete a board from Monday.com.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBoard(Request $request)
    {
        $board_id = $request->board_id;
        $mondayService = new MondayServiceProvider();
        $data = $mondayService->deleteMondayBoard($board_id);
        try {
            $responseContent = json_decode($data, true);
            if (isset($responseContent["data"]['delete_board']['id'])) {
                $boardId = $responseContent['data']['delete_board']['id'];
                $board = Board::where('board_id', $boardId)->first();
                if (!$board) {
                    return response()->json(['message' => 'Board not found'], 404);
                }
                $board->delete();
            }
            return response()->json([
                'board deleted' => "success",
                'board' => $board
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'board deleted' => "failed",
                'error' => $e->getMessage()
            ]);
        }
    }
    /**
     * Duplicate a board on Monday.com.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function duplicateBoard(Request $request)
    {
        $apiUrl = "https://api.monday.com/v2";
        $token = config("services.monday.token");
        $board_id = $request->board_id;

        $query = "mutation { duplicate_board(board_id: $board_id, duplicate_type: duplicate_board_with_structure) { board { id }}}";

        $headers = [
            'Content-Type: application/json',
            'Authorization: ' . $token
        ];

        $data = @file_get_contents($apiUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $headers,
                'content' => json_encode(['query' => $query]),
            ]
        ]));
        $responseContent = json_decode($data, true);
        echo json_encode($responseContent);
        return response()->json([
            'board duplicated' => "success",
        ]);
    }
    /**
     * Call board listener if customer action triggered on monday
     *
     * @param Request $request
     * @return void
     */
    public function boardUpdated(Request $request)
    {
        Log::info("updateBoard Request: " . $request);
        try {
            if (isset($request->board_id)) {
                $board_id = $request->board_id;
            } else {
                $inputFields = $request->input('payload.inputFields');
                $board_id = $inputFields['boardId'];
            }
            $board = Board::where('board_id', $board_id)->first();
            if (!$board) {
                Log::info('board not found: ' . $board_id);
                Log::info('board Not marked');
            }
            $board->mark();
            Log::info('board marked');
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
