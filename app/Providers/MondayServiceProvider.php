<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Ramsey\Uuid\Type\Integer;

class MondayServiceProvider extends ServiceProvider
{
    public function __construct()
    {
        $this->apiUrl = "https://api.monday.com/v2";
        $this->headers = [
            'Content-Type: application/json',
            'Authorization: ' . config("services.monday.token"),
            'X-API-VERSION' => '2024-04',
        ];
    }
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    public function getMondayBoard(string $board_id)
    {
        $query = <<<GRAPHQL
        query GetBoard(\$board_id: [ID!]) {
            boards(ids: \$board_id) {
                items_page(limit: 5) {
                    cursor
                    items {
                        id
                        column_values{
                            id
                            value
                        }
                    }
                }
            }
        }
        GRAPHQL;
        $payload = json_encode([
            'query' => $query,
            'variables' => [
                'board_id' => $board_id,
            ],
        ]);
        $data = @file_get_contents($this->apiUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $this->headers,
                'content' => $payload,
            ]
        ]));
        return $data;
    }
    public function getMondayBoardIdWithItemId(string $item_id)
    {
        $query = <<<GRAPHQL
        query GetMondayBoardIdWithItemId(\$item_id: [ID!]) {
            items(ids: \$item_id) {
                board{
                    id
                }
            }
        }
        GRAPHQL;
        $payload = json_encode([
            'query' => $query,
            'variables' => [
                'item_id' => $item_id,
            ],
        ]);
        $data = @file_get_contents($this->apiUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $this->headers,
                'content' => $payload,
            ]
        ]));
        $responseContent = json_decode($data, true);

        return $responseContent['data']['items'][0]['board']['id'];
    }
    public function getMondayBoardName(string $board_id)
    {
        $query = <<<GRAPHQL
        query GetBoard(\$board_id: [ID!]) {
            boards(ids: \$board_id) {
                name
            }
        }
        GRAPHQL;
        $payload = json_encode([
            'query' => $query,
            'variables' => [
                'board_id' => $board_id,
            ],
        ]);
        $data = @file_get_contents($this->apiUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $this->headers,
                'content' => $payload,
            ]
        ]));
        $responseContent = json_decode($data, true);
        Log::info('responseContent:  ' . json_encode($responseContent));
        return $responseContent["data"]["boards"][0]["name"];
    }
    public function createMondayBoard(string $board_name)
    {
        $query = <<<GRAPHQL
        mutation createMondayBoard(\$board_name: String!) {
                create_board (board_name: \$board_name, board_kind: public) {
                    id
                }
        }
        GRAPHQL;
        $payload = json_encode([
            'query' => $query,
            'variables' => [
                'board_name' => $board_name,
            ],
        ]);
        $data = @file_get_contents($this->apiUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $this->headers,
                'content' => $payload,
            ]
        ]));
        $responseContent = json_decode($data, true);
        return $responseContent["data"]["create_board"]["id"];
    }
    public function deleteMondayBoard(string $board_id){
        $query = <<<GRAPHQL
        mutation deleteMondayBoard(\$board_id: ID!) {
            delete_board (board_id: \$board_id) { 
                id 
            }
        }
        GRAPHQL;
        $payload = json_encode([
            'query' => $query,
            'variables' => [
                'board_id' => $board_id,
            ],
        ]);
        $data = @file_get_contents($this->apiUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $this->headers,
                'content' => $payload,
            ]
        ]));
        return $data;
    }
    public function getMondayBoardColumns(string $board_id)
    {
        $query = <<<GRAPHQL
        query GetBoardColumns(\$board_id: [ID!]) {
            boards(ids: \$board_id) {
                columns{
                    id
                    title
                    type
                }
            }
        }
        GRAPHQL;
        $payload = json_encode([
            'query' => $query,
            'variables' => [
                'board_id' => $board_id,
            ],
        ]);
        $data = @file_get_contents($this->apiUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $this->headers,
                'content' => $payload,
            ]
        ]));
        return $data;
    }
    public function getMondayItemsListByColumn(string $board_id, string $item_id, string $column_id)
    {
        $query = <<<GRAPHQL
        query GetItems(\$board_id: [ID!], \$item_id: [ID!], \$column_id: String!) {
            boards(ids: \$board_id) {
                items_page(query_params: { ids: \$item_id }) {
                    cursor
                    items {
                        column_values(ids: [\$column_id]) {
                            id
                            value
                        }
                    }
                }
            }
        }
        GRAPHQL;
        $payload = json_encode([
            'query' => $query,
            'variables' => [
                'board_id' => [(int)$board_id],
                'item_id' => [(int)$item_id],
                'column_id' => $column_id,
            ],
        ]);
        Log::info("payload: ". $payload);
        $data = @file_get_contents($this->apiUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $this->headers,
                'content' => $payload,
            ]
        ]));
        return $data;
    }
    public function getMondayItemsLists(string $board_id, string $item_id)
    {
        $query = <<<GRAPHQL
        query GetItems(\$board_id: [ID!], \$item_id: [ID!]) {
            boards(ids: \$board_id) {
                items_page(query_params: { ids: \$item_id }) {
                    cursor
                    items {
                        id
                        name
                        column_values {
                            id
                            value
                        }
                    }
                }
            }
        }
        GRAPHQL;
        $payload = json_encode([
            'query' => $query,
            'variables' => [
                'board_id' => [(int)$board_id],
                'item_id' => [(int)$item_id],
            ],
        ]);
        $data = @file_get_contents($this->apiUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $this->headers,
                'content' => $payload,
            ]
        ]));
        return $data;
    }
    public function searchItemByColumnValue(string $board_id, string $column_id, string $column_value){
        $query = <<<GRAPHQL
        query searchItemByColumnValue(\$board_id: [ID!], \$column_id: ID!, \$column_value: CompareValue!) {
            boards (ids: \$board_id) {
                items_page (query_params: {rules: [{compare_value: \$column_value, column_id: \$column_id, operator: any_of}]}) {
                items {
                    id
                    name
                }
                }
            }
        }
        GRAPHQL;
        $payload = json_encode([
            'query' => $query,
            'variables' => [
                'board_id' => [(int)$board_id],
                'column_id' => $column_id,
                'column_value' => $column_value,
            ],
        ]);
        $data = @file_get_contents($this->apiUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $this->headers,
                'content' => $payload,
            ]
        ]));
        return $data;
    }
    public function getMondayItem(string $item_id, string $column_id)
    {
        $query = <<<GRAPHQL
        query GetItems(\$item_id: [ID!], \$column_id: String!) {
            items(ids: \$item_id) {
                column_values(ids: [\$column_id]) {
                    id
                    value
                }
            }
        }
        GRAPHQL;
        $payload = json_encode([
            'query' => $query,
            'variables' => [
                'item_id' => $item_id,
                'column_id' => $column_id,
            ],
        ]);
        $data = @file_get_contents($this->apiUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $this->headers,
                'content' => $payload,
            ]
        ]));

        $responseContent = json_decode($data, true);
        $itemValue = $responseContent["data"]["items"][0]["column_values"][0]["value"];
        // Log::debug('items get as: '. $data["data"]["items"][0]["column_values"][0]["value"]);
        return trim($itemValue, '"');
    }
    public function getMondayItems(string $item_id)
    {
        $query = <<<GRAPHQL
        query GetItems(\$item_id: [ID!]) {
            items(ids: \$item_id) {
                name
                column_values {
                    id
                    value
                }
            }
        }
        GRAPHQL;
        $payload = json_encode([
            'query' => $query,
            'variables' => [
                'item_id' => $item_id,
            ],
        ]);
        $data = @file_get_contents($this->apiUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $this->headers,
                'content' => $payload,
            ]
        ]));
        return $data;
    }
    public function updateMondayItem(string $board_id, string $item_id, string $column_id, string $valueToUpdate,)
    {
        $mutation = <<<GRAPHQL
        mutation ChangeItemValue(\$board_id: ID!, \$item_id: ID!, \$column_id: String!, \$valueToUpdate: JSON!) {
            change_column_value(board_id: \$board_id, item_id: \$item_id, column_id: \$column_id, value: \$valueToUpdate) {
                id
                column_values {
                    id
                    value
                }
            }
        }
        GRAPHQL;

        $payload = json_encode([
            'query' => $mutation,
            'variables' => [
                'board_id' => $board_id,
                'item_id' => $item_id,
                'column_id' => $column_id,
                'valueToUpdate' => $valueToUpdate,
            ],
        ]);

        $data = @file_get_contents($this->apiUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $this->headers,
                'content' => $payload,
            ]
        ]));
        return $data;
    }
    public function updateMultipleColumnValuesOnMondayItem($board_id, $item_id, $columnValuesJson)
    {
        $mutation = <<<GRAPHQL
            mutation updateMultipleColumnValuesOnMondayItem(\$board_id: ID!, \$item_id: ID!, \$columnValuesJson: JSON!){
                change_multiple_column_values(board_id: \$board_id, item_id: \$item_id, column_values: \$columnValuesJson) {
                    id
                }
            }
        GRAPHQL;

        $payload = json_encode([
            'query' => $mutation,
            'variables' => [
                'board_id' => $board_id,
                'item_id' => $item_id,
                'columnValuesJson' => $columnValuesJson,
            ],
        ]);

        $data = @file_get_contents($this->apiUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $this->headers,
                'content' => $payload,
            ]
        ]));
        return $data;
    }
    public function updateLocationColumn(string $board_id, string $item_id, string $column_id, string $valueToUpdate,)
    {
        $mutation = <<<GRAPHQL
        mutation ChangeLocationColumnValue(\$board_id: ID!, \$item_id: ID!, \$column_id: String!, \$valueToUpdate: String!) {
            change_simple_column_value(board_id: \$board_id, item_id: \$item_id, column_id: \$column_id, value: \$valueToUpdate) {
                id
            }
        }
        GRAPHQL;

        $payload = json_encode([
            'query' => $mutation,
            'variables' => [
                'board_id' => $board_id,
                'item_id' => $item_id,
                'column_id' => $column_id,
                'valueToUpdate' => $valueToUpdate,
            ],
        ]);

        $data = @file_get_contents($this->apiUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $this->headers,
                'content' => $payload,
            ]
        ]));
        return $data;
    }

    public function getDropdownValues(string $board_id, string $column_id){
        $query = <<<GRAPHQL
        query getDropdownValues(\$board_id: [ID!], \$column_id: [String!]) {
            boards(ids: \$board_id) {
                columns(ids: \$column_id) {
                settings_str
                }
            }
        }
        GRAPHQL;
        $payload = json_encode([
            'query' => $query,
            'variables' => [
                'board_id' => [$board_id],
                'column_id' => [$column_id],
            ],
        ]);
        $data = @file_get_contents($this->apiUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $this->headers,
                'content' => $payload,
            ]
        ]));
        return $data;
    }
}
