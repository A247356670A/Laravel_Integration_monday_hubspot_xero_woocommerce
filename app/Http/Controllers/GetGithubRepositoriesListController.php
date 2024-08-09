<?php

namespace App\Http\Controllers;

use App\Providers\GetUrlListsServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GetGithubRepositoriesListController extends Controller
{
    //
    public function GetGithubRepositoriesList(Request $request)
    {
        Log::info("GetGithubRepositoriesList Request; " . $request);
        $url = "https://api.github.com/repositories";
        $title = "name";
        $value = "id";
        try {
            $getUrlListService = new GetUrlListsServiceProvider();
            return $getUrlListService->getUrlLists($request, $url, $title, $value);
        } catch (\Exception $e) {
            Log::error('Failed to fetch repositories: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching repositories'], 500);
        }
    }
}
