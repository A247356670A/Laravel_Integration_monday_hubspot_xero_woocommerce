<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class GetUrlListsServiceProvider extends ServiceProvider
{
    public function __construct()
    {
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

    public function getUrlLists(Request $request, $url, $title, $value)
    {
        $payload = $request->input('payload');
        $pageRequestData = $payload['pageRequestData'] ?? [];
        $page = $pageRequestData['page'] ?? 1;
        $pageLimit = 10;

        $response = Http::get($url);
        Log::info('Respones: '. $response);

        if ($response->successful()) {
            $repositories = $response->json();
            $options = [];
            foreach ($repositories as $repository) {
                Log::info('repository'. json_encode($repository));
                Log::info('repository title '. json_encode($repository[$title]));
                Log::info('repository value '. json_encode($repository[$value]));

                $options[] = [
                    'title' => $repository[$title],
                    'value' => $repository[$value]
                ];
            }

            return response()->json([
                'options' => $options,
            ]);
        } else {
            return response()->json(['error' => 'Failed to fetch repositories'], $response->status());
        }
    }
    public function getGithubIssuesDynamicMapping(Request $request, $url)
    {
        Log::info('getGithubIssuesDynamicMapping Request: '. $request);
        $payload = $request->input('payload');

        $response = Http::get($url);
        Log::info('getGithubIssuesDynamicMapping Respones: '. $response);

        if ($response->successful()) {
            $repositories = $response->json();
            $fields = [];
            foreach ($repositories as $repository) {
                Log::info('repository'. json_encode($repository));

                $fields[] = [
                    ['id' => 'name', 'title' => 'Name', 'outboundType' => 'text', 'inboundTypes' => ['text']],
                    ['id' => 'desc', 'title' => 'Description', 'outboundType' => 'text', 'inboundTypes' => ['empty_value', 'text', 'text_array']],
                    ['id' => 'dueDate', 'title' => 'Due Date', 'outboundType' => 'date', 'inboundTypes' => ['empty_value', 'date', 'date_time']],
                    ['id' => 'people', 'title' => 'People', 'outboundType' => 'user_emails', 'inboundTypes' => 'user_emails'],
                    ['id' => 'creationDate', 'title' => 'CreateDate', 'outboundType' => 'date_time', 'inboundTypes' => ['date', 'date_time']],
                ];
            }

            return response()->json($fields ,200);
        } else {
            return response()->json(['error' => 'Failed to fetch repositories'], $response->status());
        }
    }
}
