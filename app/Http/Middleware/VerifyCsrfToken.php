<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        "get-board",
        "create-board",
        "duplicate-board",
        "update-board",
        "delete-board",
        "/monday/search-item",
        "/monday/get-item",
        "/monday/update-item",
        "/monday/transform",
        "/monday/get_transform_list_options",
        "/monday/get_github_repositories_list",
        "/monday/get_github_issues_list",
        "/monday/merge_two_boards",
        "/monday_woocommerce/products",
        "/monday/testSubscribe",
        "/monday/testUnsubscribe",
        "/monday/mappingSubscribe",
        "/monday/mappingUnsubscribe",
        "/monday/WoocommerceSubscribe",
        "/monday/WoocommerceUnsubscribe",
        "/monday/woocommerce/update",
        "/monday/webhooks",
        "/monday/customeractions",
        "/monday/updatelocation",
        "/monday/xero/create_invoice",
        "/xero/webhooks",
        "/woocommerce/webhooks",
        "/woocommerce/create_order",
        "/woocommerce/create_product",
        "/woocommerce/product_create/webhooks",
        "/woocommerce/product_update/webhooks",
        

    ];
}
