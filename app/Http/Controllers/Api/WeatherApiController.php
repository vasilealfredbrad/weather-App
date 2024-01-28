<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

// WeatherController.php
class WeatherApiController extends Controller
{
    public function fetchWeatherData(Request $request)
    {
        $this->validateWeatherRequest($request);

        try {
            $apiKey = $this->getApiKey();
            $weatherData = $this->fetchWeatherFromApi($request, $apiKey);

            return $this->processApiResponse($weatherData);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    protected function validateWeatherRequest(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);
    }

    protected function getApiKey()
    {
        return config('services.darksky.key');
    }

    protected function fetchWeatherFromApi(Request $request, $apiKey)
    {
        //$origin = $request->headers->get('origin');
        $referer = $request->headers->get('referer');

        $allowedDomain = 'http://127.0.0.1:8000/';

        if ($referer !== $allowedDomain) abort(403, 'Unauthorized'); //

        return Http::get("https://api.openweathermap.org/data/2.5/forecast", [
            'lat' => $request->input('lat'),
            'lon' => $request->input('lng'),
            'appid' => $apiKey,
            'lang' => 'RO',
            'units' => 'metric',
        ]);
    }

    protected function processApiResponse($apiResponse)
    {

        if (is_object($apiResponse)) {
            // Create a new collection for weather data
            $weatherDataCollection = collect($apiResponse->json()['list']);

            // Filter weather data to retain entries at '12:00:00'
            $uniqueWeatherDays = $this->transformWeatherData($weatherDataCollection);

            // Decode the API response to an array
            $apiData = json_decode($apiResponse, true);

            // Update the 'list' key with filtered weather data
            $apiData['list'] = $uniqueWeatherDays;

            return $apiData;
        }

        return response()->json(['error' => 'Invalid API response or API call failed'], 403);

    }

    //Filter weather data to retain entries at '12:00:00'
    protected function transformWeatherData($weatherCollection)
    {
        return $weatherCollection->filter(function ($item) {
            return Carbon::createFromTimestamp($item['dt'])->format('H:i:s') === '12:00:00';
        })->values()->map(function ($item) {
            return $this->transformWeatherItem($item);
        });
    }

    // Refine forecast data
    protected function transformWeatherItem($item)
    {
        return [
            "dt" => Carbon::createFromTimestamp($item['dt'])->format('d/m/y'),
            "temp" => round($item['main']['temp']),
            "feels_like" => round($item['main']['feels_like']),
            "temp_min" => round($item['main']['temp_min']),
            "temp_max" => round($item['main']['temp_max']),
            "weather_main" => $item['weather'][0]['main'],
            "weather_icon" => $item['weather'][0]['icon'],
            "weather_description" => $item['weather'][0]['description'],
            "weather" => $item['weather']
        ];
    }

    protected function handleException(\Exception $e)
    {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
