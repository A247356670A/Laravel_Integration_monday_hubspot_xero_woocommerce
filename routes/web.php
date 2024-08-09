<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\BoardController;
use App\Listeners\BoardSubscriptedUpdated;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\XeroAuthController;
use App\Http\Controllers\MondayAuthController;
use App\Http\Controllers\MondayItemController;
use App\Http\Controllers\XeroContactController;
use App\Http\Controllers\XeroInvoiceController;
use App\Http\Controllers\XeroWebhookController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TransformationController;
use App\Http\Controllers\WoocommerceOrderController;
use App\Http\Controllers\MergeMondayBoardsController;
use App\Http\Controllers\MondayXeroInvoicesController;
use App\Http\Controllers\WoocommerceProductController;
use App\Http\Controllers\WoocommerceWebhookController;
use App\Http\Controllers\GetGithubIussuesListController;
use App\Http\Controllers\GetGithubRepositoriesListController;
use App\Http\Controllers\MondayWoocommerceProductsController;
use App\Http\Controllers\WoocommerceMondayProductsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

# receive token
Route::get('/monday/auth', [MondayAuthController::class, 'redirectToMonday']);
Route::get('/oauth/callback', [MondayAuthController::class, 'handleMondayCallback']);

Route::get('/xero/auth', [XeroAuthController::class, 'redirectToXero']);
Route::get('/xero/oauth/callback', [XeroAuthController::class, 'handleXeroCallback']);

# boards
Route::post('/get-board', [BoardController::class, 'getBoard']);
Route::post('/create-board', [BoardController::class, 'createBoard']);
Route::post('/duplicate-board', [BoardController::class, 'duplicateBoard']);
Route::post('/update-board', [BoardController::class, 'updateBoard']);
Route::post('/delete-board', [BoardController::class, 'deleteBoard']);

# items
Route::post('/get-items', [ItemController::class, 'getItemsListByColumn']);
Route::post('/update-items', [ItemController::class, 'updateItem']);

Route::post('/monday/search-item', [MondayItemController::class, 'searchItemByColumnValues']);
Route::post('/monday/get-item', [MondayItemController::class, 'getMondayItem']);
Route::post('/monday/update-item', [MondayItemController::class, 'updateMondayItem']);

# customer filed types
Route::post('/monday/transform', [TransformationController::class, 'TransformationText']);
Route::post('/monday/get_transform_list_options', [TransformationController::class, 'getTransformListOptions']);

Route::post('/monday/get_github_repositories_list', [GetGithubRepositoriesListController::class, 'GetGithubRepositoriesList']);

Route::post('/monday/get_github_issues_list', [GetGithubIussuesListController::class, 'GetGithubIssuesList']);
Route::post('/monday/merge_two_boards', [MergeMondayBoardsController::class, 'mergeBookingClientBoards']);
Route::post('/monday_woocommerce/products
', [WoocommerceMondayProductsController::class, 'displayMondayProducts']);

# triggers

Route::post('/monday/testSubscribe', [SubscriptionController::class,'handleTestSubscribe']);
Route::post('/monday/testUnsubscribe', [SubscriptionController::class,'handleTestUnSubscribe']);
Route::post('/monday/mappingSubscribe', [SubscriptionController::class,'handleMappingSubscribe']);
Route::post('/monday/mappingUnsubscribe', [SubscriptionController::class,'handleMappingUnSubscribe']);

Route::post('/monday/webhooks', [WebhookController::class, 'webhookEvent']);

Route::post('/monday/WoocommerceSubscribe', [SubscriptionController::class,'handleWoocommerceSubscribe']);
Route::post('/monday/WoocommerceUnsubscribe', [SubscriptionController::class,'handleWoocommerceUnSubscribe']);
# actions

Route::post('/monday/customeractions', [MondayItemController::class, 'itemUpdatedTriggerForBookingClientMapping']);
Route::post('/monday/updatelocation', [WebhookController::class, 'updatelocation']);

# xero
Route::post('/xero/webhooks', [XeroWebhookController::class, 'webhookEvent']);
Route::post('/monday/xero/create_invoice', [MondayXeroInvoicesController::class, 'createXeroInvoiceFromMonday']);
Route::get('/xero/invoices', [XeroInvoiceController::class, 'getInvoices']);
Route::get('/xero/contacts', [XeroContactController::class, 'getContacts']);
Route::get('/xero/contactsByName', [XeroContactController::class, 'getContactsByName']);

# woocommerce
Route::post('/monday/woocommerce/update', [MondayWoocommerceProductsController::class, 'updateWoocommerceFromMondayBoard']);

Route::post('/woocommerce/webhooks', [WoocommerceWebhookController::class, 'webhookEvent']);
Route::get('/woocommerce/orders', [WoocommerceOrderController::class, 'getOrders']);
Route::get('/woocommerce/products', [WoocommerceProductController::class, 'getProducts']);

Route::post('/woocommerce/create_order', [WoocommerceOrderController::class, 'createOrder']);
Route::post('/woocommerce/create_product', [WoocommerceProductController::class, 'createProduct']);
Route::post('/woocommerce/product_create/webhooks', [WoocommerceWebhookController::class, 'productCreatedEvent']);
Route::post('/woocommerce/product_update/webhooks', [WoocommerceWebhookController::class, 'productUpdateEvent']);

