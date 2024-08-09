<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Providers\MondayServiceProvider;
use App\Providers\WoocommerceProductServiceProvider;

class MondayWoocommerceProductsController extends Controller
{
    //
    public function __construct()
    {
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
    public function updateWoocommerceFromMondayBoard(Request $request){
        Log::info("updateWoocommerceFromMondayBoard request: .." . $request);
        $inputFields = $request->input('payload.inputFields');
        $board_id = $inputFields["boardId"];
        $item_id = $inputFields["itemId"];

        $handledValues = $this->handleValuesFromMonday($board_id, $item_id);
        $sku = $handledValues['sku'];
        $woocommerceProductService = new WoocommerceProductServiceProvider();
        $productId = $woocommerceProductService->getProductIdBySku($sku);
        if($productId != null){
            $data = $woocommerceProductService->updateProduct($productId, $handledValues);

        }else{
            $data = $woocommerceProductService->createProduct($handledValues);

        }
        Log::info("updateWoocommerceFromMondayBoard data: ..". json_encode($data));
    }
    protected function stringToArray($string){
        $stringTrim = trim($string, '"'); 
        return explode(',', $stringTrim);
    }
    protected function stockStatusConverter($stock_status)
    {
        switch (strtolower($stock_status)) {
            case 'In stock':
                return 'instock';
            case 'Out of stock':
                return 'outofstock';
            case 'On back order':
                return 'onbackorder';
            default:
                return 'outofstock';
        }
    }
    protected function getDropdownValue($board_id, $column_id, $index)
    {
        $mondayService = new MondayServiceProvider();
        $dropdownValues = json_decode($mondayService->getDropdownValues($board_id, $column_id), true);
        $labels = $dropdownValues['data']['boards'][0]['columns'][0]['settings_str'];
        $labelsData = json_decode($labels, true);
        $value = $labelsData['labels'][(string)$index];
        return $value;
    }
    protected function handleValuesFromMonday($board_id, $item_id)
    {
        $mondayService = new MondayServiceProvider();
        $data = json_decode($mondayService->getMondayItems($item_id), true);
        $itemData = $data["data"]["items"][0];
        $itemName = $itemData["name"];
        $columnValues = [];
        Log::info("data::   ". json_encode($data));
        foreach ($itemData['column_values'] as $column) {
            $id = $column['id'];
            $value = json_decode($column['value'], true);
            $columnValues[$id] = $value;
        }
        $statsID = $columnValues[$this->stats_column_id]['index'] ?? null;
        $statsValue = $this->getDropdownValue($board_id, $this->stats_column_id, $statsID);
        $skuValue = $columnValues[$this->sku_column_id]??null;
        $inStockID = $columnValues[$this->stockStatus_column_id]['index'] ?? null;
        $inStockValue = $this->getDropdownValue($board_id, $this->stockStatus_column_id, $inStockID);
        $priceValue = $columnValues[$this->price_column_id]??null;
        $salePrice = $columnValues[$this->sale_price_column_id]??null;
        $categories = $columnValues[$this->categories_column_id]??null;
        $categoriesArray = $this->stringToArray($categories);
        $categoriesValue = array_map(function($category) {
            return ['name' => $category];
        }, $categoriesArray);
        $tags = $columnValues[$this->tags_column_id]??null;
        $tagsArray = $this->stringToArray($tags);
        $tagsValue = array_map(function($tag) {
            return ['name' => $tag];
        }, $tagsArray);
        $stockQuantityValue = $columnValues[$this->quantity_column_id]??null;
        $lowStockQuantityValue = $columnValues[$this->lowStockQuantity_column_id]??null;

        Log::info("itemName: ". json_encode($itemName));
        Log::info("statsValue: " . json_encode($statsValue));
        Log::info("skuValue: " . json_encode($skuValue));
        Log::info("inStockValue: " . json_encode($inStockValue));
        Log::info("priceValue: " . json_encode($priceValue));
        Log::info("salePrice: " . json_encode($salePrice));
        Log::info("stockQuantityValue: " . json_encode($stockQuantityValue));
        Log::info("lowStockQuantityValue: " . json_encode($lowStockQuantityValue));
        Log::info("categoriesValue: " . json_encode($categoriesValue));
        Log::info("tagsValue: " . json_encode($tagsValue));


        return [
            'name' => $itemName,
            'status' => $statsValue,
            'sku' => $skuValue,
            'stock_status' => $this->stockStatusConverter($inStockValue),
            'regular_price' => $priceValue,
            'sale_price' => $salePrice,
            'stock_quantity' => $stockQuantityValue,
            'low_stock_amount' => $lowStockQuantityValue,
            'categories' => $categoriesValue,
            'tags' => $tagsValue,
        ];
    }
}
