<?php

namespace App\Providers;

use Exception;
use App\Models\Location;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class TextServiceProvider extends ServiceProvider
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

    public function transformText(string $text, string $type): string{
        switch ($type) {
            case 'TO_UPPER_CASE':
              return strtoupper($text);
            case 'TO_LOWER_CASE':
              return strtolower($text);
            case null:
              return $text;
            default:
              return $text;
          }
    }
    public function saveLocation($clientLocationValue): Location{
      $clientLocation = new Location();
      if (isset($clientLocationValue['placeId'])) {
        try {
            $clientLocation->Address = isset($clientLocationValue['address']) ? $clientLocationValue['address'] : null;
            $clientLocation->Latitude = isset($clientLocationValue['lat']) ? $clientLocationValue['lat'] : null;
            $clientLocation->Longitude = isset($clientLocationValue['lng']) ? $clientLocationValue['lng'] : null;
            $clientLocation->Country_long = isset($clientLocationValue['country']['long_name']) ? $clientLocationValue['country']['long_name'] : null;
            $clientLocation->Country_short = isset($clientLocationValue['country']['short_name']) ? $clientLocationValue['country']['short_name'] : null;
            $clientLocation->City_long = isset($clientLocationValue['city']['long_name']) ? $clientLocationValue['city']['long_name'] : null;
            $clientLocation->City_short = isset($clientLocationValue['city']['short_name']) ? $clientLocationValue['city']['short_name'] : null;
            $clientLocation->Street_long = isset($clientLocationValue['street']['long_name']) ? $clientLocationValue['street']['long_name'] : null;
            $clientLocation->Street_short = isset($clientLocationValue['street']['short_name']) ? $clientLocationValue['street']['short_name'] : null;
            $clientLocation->StreetNum_long = isset($clientLocationValue['streetNumber']['long_name']) ? $clientLocationValue['streetNumber']['long_name'] : null;
            $clientLocation->StreetNum_Short = isset($clientLocationValue['streetNumber']['short_name']) ? $clientLocationValue['streetNumber']['short_name'] : null;
            $clientLocation->placeId = isset($clientLocationValue['placeId']) ? $clientLocationValue['placeId'] : null;
            $clientLocation->save();
        } catch (Exception $e) {
            Log::info('' . $e->getMessage());
            
        }
    } else {
        $clientLocation = null;
    }
      return $clientLocation;
    }
}
