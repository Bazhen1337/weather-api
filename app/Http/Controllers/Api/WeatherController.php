<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Weather;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WeatherController extends Controller
{
    public function show(Request $request)
    {
        try {

            $validated = $request->validate([
                'city' => 'required|string|max:100',
                'unit' => 'sometimes|string|in:celsius,fahrenheit',
                'lang' => 'sometimes|string'
            ]);

            $city = $validated['city'];
            $unit = $validated['unit'] ?? 'celsius';
            $lang = $validated['lang'] ?? 'ru';

            // Получаем параметры из запроса
            $weatherModel = new Weather();
            $weatherData = $weatherModel->getCurrentWeather($city, $unit, $lang);


            // Формируем структурированный JSON-ответ
            return response()->json([
                'success' => true,
                'city' => $city,
                'weather' => [
                    'temperature' => round($weatherData['weather_response']['main']['temp'], 1) ?? 'no information',
                    'unit' => $unit ?? 'нет информации',
                    'description' => $weatherData['weather_response']['weather'][0]['description'] ?? 'no information',
                    'icon' => $weatherData['iconUrl'] ?? 'нет информации',
                ],
                'wind' => [
                    'speed' => $weatherData['weather_response']['wind']['speed'] ?? 'no information',
                    'direction' => $weatherData['direction'] ?? 'no information',
                ],
                'atmosphere' => [
                    'pressure' => $weatherData['weather_response']['main']['pressure'] ?? 'no information',
                    'humidity' => $weatherData['weather_response']['main']['humidity'] ?? 'no information',
                ],
                'precipitation' => [
                    'probability' => $weatherData['weather_forecast_response'] ?? 'no information',
                ],
                'timestamp' => now()->toDateTimeString(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => config('app.debug') ? $e->getMessage() : 'Something went wrong',
            ], 500);
        }
    }

    public function getLocation(Request $request)
    {
        try {
            $request->validate([
                'lat' => 'required|numeric',
                'lon' => 'required|numeric'
            ]);

            // Используем сервис для определения города по координатам
            $weatherModel = new Weather();
            $city = $weatherModel->getCurrentWeatherForLocation($request->lat, $request->lon);
            $newRequest = new Request([
                'city' => $city,
                'unit' => $request->input('unit', 'celsius'),
                'lang' => $request->input('lang', 'ru')
            ]);
            return $this->show($newRequest);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Location service error',
                'error' => config('app.debug') ? $e->getMessage() : 'Something went wrong',
            ], 500);
        }
    }
}
