<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Providers\MondayServiceProvider;
use Exception;

class WoocommerceMondayProductsController extends Controller
{
    //
    protected $sending_to_woocommerce;
    protected $item_name;
    protected $stats_column_id;
    protected $sku_column_id;
    protected $stockStatus_column_id;
    protected $price_column_id;
    protected $categories_column_id;
    protected $tags_column_id;
    protected $column_list;

    public function __construct()
    {
        $this->sending_to_woocommerce = "dup__of_stats__1";
        $this->item_name = "name";
        $this->stats_column_id = "status";
        $this->sku_column_id = "text__1";
        $this->stockStatus_column_id = "status_1__1";
        $this->price_column_id = "numbers__1";
        $this->sale_price_column_id = "dup__of_price__1";
        $this->categories_column_id = "text0__1";
        $this->tags_column_id = "text8__1";
        $this->quantity_column_id = "numbers9__1";
        $this->lowStockQuantity_column_id = "numbers7__1";
        $this->column_list = [
            $this->sending_to_woocommerce,
            $this->item_name,
            $this->stats_column_id,
            $this->sku_column_id,
            $this->stockStatus_column_id,
            $this->price_column_id,
            $this->sale_price_column_id,
            $this->categories_column_id,
            $this->tags_column_id,
            $this->quantity_column_id,
            $this->lowStockQuantity_column_id,
        ];
    }
    public function displayMondayProducts(Request $request)
    {
        Log::info("loading displayMondayProducts......" . $request);
        $fields = [
            [
                'id' => $this->item_name,
                'title' => 'Name',
                'outboundType' => 'text',
                'inboundTypes' => ['text, numeric, boolean']
            ],
            [
                'id' => $this->stats_column_id,
                'title' => 'Stats',
                'outboundType' => 'text',
                'inboundTypes' => ['text, numeric, boolean']
            ],
            [
                'id' => $this->sku_column_id,
                'title' => 'SKU',
                'outboundType' => 'text',
                'inboundTypes' => ['text, text_array, numeric, date, date_time, boolean']
            ],
            [
                'id' => $this->stockStatus_column_id,
                'title' => 'In stock',
                'outboundType' => 'text',
                'inboundTypes' => ['text, numeric, boolean']
            ],
            [
                'id' => $this->price_column_id,
                'title' => 'Price',
                'outboundType' => 'numeric',
                'inboundTypes' => ['numeric']
            ],
            [
                'id' => $this->sale_price_column_id,
                'title' => 'Sale Price',
                'outboundType' => 'numeric',
                'inboundTypes' => ['numeric']
            ],
            [
                'id' => $this->categories_column_id,
                'title' => 'Categories',
                'outboundType' => 'text',
                'inboundTypes' => ['text, text_array, numeric, date, date_time, boolean']
            ],
            [
                'id' => $this->tags_column_id,
                'title' => 'Tags',
                'outboundType' => 'text',
                'inboundTypes' => ['text, text_array, numeric, date, date_time, boolean']
            ],
            [
                'id' => $this->quantity_column_id,
                'title' => 'Stock quantity',
                'outboundType' => 'numeric',
                'inboundTypes' => ['numeric']
            ],
            [
                'id' => $this->lowStockQuantity_column_id,
                'title' => 'Low stock quantity',
                'outboundType' => 'numeric',
                'inboundTypes' => ['numeric']
            ],
        ];
        return response()->json($fields, 200);
    }
    public function updateMondayBoardFromWoocommerce(Request $request)
    {
        $data = $this->handleWoocommerceWebhookValues($request);
        $boards = Board::whereNotNull('webhookUrl_woocommerce')
            ->select('board_id', 'webhookUrl_woocommerce')
            ->get();
        foreach ($boards as $board) {
            if ($this->checkProductSkuOnMonday($board->board_id, $data[$this->sku_column_id]) != null) {
                Log::info("sku found, updating......");
                $this->updateToMonday($data, $board);
            } else {
                Log::info("sku not found, creating......");
                $this->createToMonday($data, $board);
            }
        }
    }
    public function handleWoocommerceWebhookValues(Request $request)
    {
        $inputData = $request->all();
        $name = $inputData['name'];
        $stock_status = $inputData['stock_status'];
        $sku = $inputData['sku'];
        $price = $inputData['regular_price'];
        $sale_price = $inputData['sale_price'];

        $status = $inputData['status'];
        $categories = array_map(function ($category) {
            return $category['name'];
        }, $inputData['categories']);
        $tags = array_map(function ($tag) {
            return $tag['name'];
        }, $inputData['tags']);
        $quantity = $inputData['stock_quantity'];
        $lowQuantity = $inputData['low_stock_amount'];

        Log::info("Parsed Data - Name: $name, Stock Status: $stock_status, SKU: $sku, Price: $price, Status: $status, Categories: " . json_encode($categories) . ", Tags: " . json_encode($tags));
        $data = [
            $this->sending_to_woocommerce => "Items changed",
            $this->item_name => $name,
            $this->stats_column_id => $status,
            $this->sku_column_id => $sku,
            $this->stockStatus_column_id => $this->stockStatusConverter($stock_status),
            $this->price_column_id => $price,
            $this->sale_price_column_id => $sale_price,
            $this->categories_column_id => $this->arrayToString($categories),
            $this->tags_column_id => $this->arrayToString($tags),
            $this->quantity_column_id => $quantity,
            $this->lowStockQuantity_column_id => $lowQuantity,
        ];
        return $data;
    }
    protected function arrayToString($array) {
        if (is_array($array) && !empty($array)) {
            return implode(',', $array);
        }
        return '';
    }
    protected function stockStatusConverter($stock_status)
    {
        switch (strtolower($stock_status)) {
            case 'instock':
                return 'In stock';
            case 'outofstock':
                return 'Out of stock';
            case 'onbackorder':
                return 'On back order';
            default:
                return 'Out of stock';
        }
    }
    public function checkProductSkuOnMonday($board_id, $column_value)
    {
        $mondayService = new MondayServiceProvider();
        $data = json_decode($mondayService->searchItemByColumnValue($board_id, $this->sku_column_id, $column_value), true);
        if (isset($data['data']['boards'][0]['items_page']['items'][0]['id'])) {
            return $data['data']['boards'][0]['items_page']['items'][0]['id'];
        } else {
            return null;
        }
    }
    public function createToMonday($data, $board)
    {
        try {
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => env('SIGNING_SECRET'),
                'X-API-VERSION' => '2024-04',
            ];
            $payload = [
                'trigger' => [
                    'outputFields' => [
                        'board_id' => $board->board_id,
                        "WoocommerceProductsMappingKey" => [
                            $this->sending_to_woocommerce => "Items changed",
                            $this->item_name => $data[$this->item_name],
                            $this->stats_column_id => $data[$this->stats_column_id],
                            $this->sku_column_id => $data[$this->sku_column_id],
                            $this->stockStatus_column_id => $data[$this->stockStatus_column_id],
                            $this->price_column_id => floatval($data[$this->price_column_id]),
                            $this->sale_price_column_id => floatval($data[$this->sale_price_column_id]),
                            $this->categories_column_id => $data[$this->categories_column_id],
                            $this->tags_column_id => $data[$this->tags_column_id],
                            $this->quantity_column_id => $data[$this->quantity_column_id],
                            $this->lowStockQuantity_column_id => $data[$this->lowStockQuantity_column_id],
                        ]
                    ],
                ],
            ];
            Log::info("payload that sending....." . json_encode($payload));
            $response = Http::withHeaders($headers)->post($board->webhookUrl_woocommerce, $payload);
            if ($response->successful()) {
                Log::info('WoocommerceEvent sent successfully. Response: ' . json_encode($response->json()));
            } else {
                Log::error('Failed to send WoocommerceEvent. Response: ' . json_encode($response->json()));
            }
        } catch (Exception $e) {
            Log::error('Failed to send webhook: ' . $e->getMessage());
        }
    }
    public function updateToMonday($data, $board)
    {
        $item_id = $this->checkProductSkuOnMonday($board->board_id, $data[$this->sku_column_id]);
        $columnValues = [];
        foreach ($this->column_list as $column_id) {
            if (isset($data[$column_id])) {
                $columnValues[$column_id] = $data[$column_id];
            } else {
                $columnValues[$column_id] = null; 
            }
        }
        $columnValuesJson = json_encode($columnValues);
        $mondayService = new MondayServiceProvider();
        $returndata = $mondayService->updateMultipleColumnValuesOnMondayItem($board->board_id, $item_id, $columnValuesJson);
        Log::info("updateMultipleColumnValuesOnMondayItem : dataa:: ..." . json_encode($returndata));
    }
}
