<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Providers\TextServiceProvider;

class TransformationController extends Controller
{
    //
    public function getTransformListOptions(Request $request){
        try {
            $transformListOptions = [
                [ 'title' => 'to upper case', 'value' => 'TO_UPPER_CASE' ],
                [ 'title' => 'to lower case', 'value' => 'TO_LOWER_CASE' ],
            ];
            return $transformListOptions;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        
    }
    public function TransformationText(Request $request)
    {
        $inputFields = $request->input('payload.inputFields');
        $board_id = $inputFields["boardId"];
        $item_id = $inputFields["itemId"];
        $sourceColumn_id = $inputFields["sourceColumnId"];
        $targetColumn_id = $inputFields["targetColumnId"];
        $transformationType = $inputFields["transformationType"]['value'];
        $mondayItemController = new MondayItemController();

        try {
            $requestDataGet = [
                'payload' => [
                    'inputFields' => [
                        'itemId' => $item_id,
                        'columnId' => $sourceColumn_id,
                    ],
                ],
            ];
            $text = $mondayItemController->getMondayItem(new Request($requestDataGet));
            $textService = new TextServiceProvider();
            $transformedText = $textService->transformText($text, $transformationType);

            $requestDataUpdate = [
                'payload' => [
                    'inputFields' => [
                        'boardId' => $board_id,
                        'itemId' => $item_id,
                        'columnId' => $targetColumn_id,
                        'value' => $transformedText,
                    ],
                ],
            ];
            return $mondayItemController->updateMondayItem(new Request($requestDataUpdate));
        } catch (\Exception $e) {
            Log::error("" . $e->getMessage());
        }
    }
}
