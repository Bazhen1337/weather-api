<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Weather extends Model
{
    protected $apiKey = 'fd4848476d2090a78d19d44bf13059b9';
    protected $weatherUrl = 'https://api.openweathermap.org/data/2.5/weather';

    protected $geoUrl = 'http://api.openweathermap.org/geo/1.0/reverse';
    protected $forecastWeatherUrl = 'https://api.openweathermap.org/data/2.5/forecast';

    protected $iconUrl = 'https://openweathermap.org/img/wn/%s@%s.png';

    public function getCurrentWeather(string $city, string $temp = 'celsius', string $lang = 'ru'): array
    {
        $weather_response = $this->getApiResponse($this->weatherUrl, $city, $lang);
        $weather_forecast_response = $this->getApiResponse($this->forecastWeatherUrl, $city, $lang, 1);

        $direction = $this->getWindDirection($weather_response['wind']['deg']);
        if ($temp === 'fahrenheit') {
            $weather_response['main']['temp'] = $this->toFahrenheit($weather_response['main']['temp']);
        }
        $iconUrl = $this->getIcon($weather_response['weather'][0]['icon']);
        return [
            'weather_response' => $weather_response,
            'weather_forecast_response' => $weather_forecast_response['list'][0]['pop'] * 100,
            'iconUrl' => $iconUrl,
            'direction' => $direction
        ];
    }

    public function getCurrentWeatherForLocation(string $lat, string $lon, string $temp = 'celsius', string $lang = 'ru'): string
    {
        $geo_response = Http::get($this->geoUrl, [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $this->apiKey,
            'limit' => 1
        ]);

        if ($geo_response->failed()) {
            throw new \Exception('Location error');
        }

        $geo_response_data = $geo_response->json();

        if (empty($geo_response_data)) {
            throw new \Exception('Location not found');
        }

        return $geo_response_data[0]['local_names'][$lang] ?? $geo_response_data[0]['name'];
    }

    public function getIcon(string $iconCode): string
    {
        return sprintf($this->iconUrl, $iconCode, '4x');
    }

    public function toFahrenheit(float $temp): float
    {
        return ($temp * 9 / 5 + 32);
    }

    public function getWindDirection(int $degrees): string
    {
        $directions = [
            'северный', 'северо-восточный', 'восточный', 'юго-восточный',
            'южный', 'юго-западный', 'западный', 'северо-западный'
        ];

        $index = round(($degrees % 360) / 45) % 8;
        return $directions[$index];
    }

    public function getApiResponse(string $url, string $city, $lang = 'ru', $cnt = '', string $units = 'metric')
    {
        $weather_response = Http::get($url, [
            'q' => $city,
            'appid' => $this->apiKey,
            'units' => $units,
            'lang' => $lang,
            'cnt' => $cnt
        ]);

        if ($weather_response->failed()) {
            throw new \Exception('Error while requesting weather API');
        }

        if (isset($weather_response['cod']) && $weather_response['cod'] === '404') {
            throw new \Exception('City not found');
        }

        return $weather_response->json();
    }

}
