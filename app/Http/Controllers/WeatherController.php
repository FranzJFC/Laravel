<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use UnexpectedValueException;

class WeatherController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lon' => ['required', 'numeric', 'between:-180,180'],
        ]);
        $key = 'weather:'.number_format((float) $data['lat'], 2, '.', '').':'.number_format((float) $data['lon'], 2, '.', '');
        try {
            $weather = Cache::remember($key, now()->addMinutes(10), function () use ($data) {
                $response = Http::timeout(10)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $data['lat'],
                    'longitude' => $data['lon'],
                    'current' => 'temperature_2m,relative_humidity_2m,weather_code',
                    'timezone' => 'auto',
                ])->throw();
                $current = $response->json('current');
                if (! is_array($current) || ! is_numeric($current['temperature_2m'] ?? null) || ! is_numeric($current['relative_humidity_2m'] ?? null) || ! is_numeric($current['weather_code'] ?? null)) {
                    throw new UnexpectedValueException('Incomplete weather response.');
                }

                return [
                    'place' => 'Selected location',
                    'temperature' => $current['temperature_2m'],
                    'description' => $this->description((int) $current['weather_code']),
                    'humidity' => $current['relative_humidity_2m'],
                ];
            });
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            report($exception);

            return response()->json(['error' => 'Weather is currently unavailable.'], 503);
        }

        return response()->json($weather);
    }

    private function description(int $code): string
    {
        return match (true) {
            $code === 0 => 'Clear sky',
            $code === 1 => 'Mainly clear',
            $code === 2 => 'Partly cloudy',
            $code === 3 => 'Overcast',
            in_array($code, [45, 48], true) => 'Fog',
            $code >= 51 && $code <= 57 => 'Drizzle',
            $code >= 61 && $code <= 67 => 'Rain',
            $code >= 71 && $code <= 77 => 'Snow',
            $code >= 80 && $code <= 82 => 'Rain showers',
            $code >= 85 && $code <= 86 => 'Snow showers',
            $code >= 95 && $code <= 99 => 'Thunderstorm',
            default => 'Unknown',
        };
    }
}
