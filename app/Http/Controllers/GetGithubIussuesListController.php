<?php

namespace App\Http\Controllers;

use App\Providers\MondayServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Providers\GetUrlListsServiceProvider;

class GetGithubIussuesListController extends Controller
{
    //
    public function GetGithubIssuesList(Request $request)
    {
        Log::info("GetGithubIssuesList Request; " . $request);
        $payload = $request->input('payload');
        $board_id = $payload["boardId"];
        $exboard_id = $request->get("exBoardId");
        $mondayService = new MondayServiceProvider();
        $data = $mondayService->getMondayBoardColumns($board_id);
        $responseContent = json_decode($data, true);
        Log::info("data" . json_encode($responseContent));

        $fields = [];

        $columns = $responseContent['data']['boards'][0]['columns'];
        foreach ($columns as $column) {
            Log::info("column" . json_encode($column));
            $outboundType = '';
            $inboundTypes = [];

            switch ($column['type']) {
                case 'name':
                    $outboundType = 'text';
                    $inboundTypes = ['text, text_array, numeric'];
                    break;
                case 'text':
                    $outboundType = 'text';
                    $inboundTypes = ['text, text_array, numeric, date, date_time, boolean
'];
                    break;
                case 'status':
                    $outboundType = 'status';
                    $inboundTypes = ['text, numeric, boolean
'];
                    break;
                case 'people':
                    $outboundType = 'user_emails';
                    $inboundTypes = ['user_emails'];
                    break;
                case 'date':
                    $outboundType = 'date';
                    $inboundTypes = ['date', 'date_time'];
                    break;
                case 'numbers':
                    $outboundType = 'numeric';
                    $inboundTypes = ['numeric'];
                    break;
                default:
                    $outboundType = 'text';
                    $inboundTypes = ['text'];
                    break;
            }
            $fields[] = [
                'id' => $column['id'],
                'title' => $column['title'],
                'outboundType' => $outboundType,
                'inboundTypes' => $inboundTypes
            ];
        }
        Log::info('fields ' . json_encode($fields));
        return response()->json($fields, 200);
    }
}
