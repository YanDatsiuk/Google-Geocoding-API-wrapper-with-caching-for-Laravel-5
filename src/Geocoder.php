<?php

namespace Datsyuk\GoogleGeocoding;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Psy\Util\Json;

class Geocoder
{

    /**
     * Ключ к Google Maps API
     */
    protected $applicationKey;


    /**
     * Передача параметров
     */
    public function __construct($config)
    {
        $this->applicationKey = $config['applicationKey'];
    }


    /**
     *
     * @param array $parameters - параметры геокодирования
     * @param string $output - формат вывода 'json' или 'xml'
     *
     * @return string
     */
    public function geocode($parameters, $output = 'json', $onlyLocation = true)
    {
        //получение уникального cache id
        $cacheId = $this->makeCacheId($parameters, $output);

        //проверка кэша
        if (Cache::has($cacheId)) {

            $formatted = Cache::get($cacheId);
        }else{

            //Отправка http запроса к Google Maps API
            $formatted = $this->guzzleRequestToGoogleMapsAPI($parameters, $output);

            //сохранение ответа в кэш
            Cache::forever($cacheId, $formatted);
        }

        //вывод только локации
        if ($onlyLocation) {
            $formatted = $formatted->results[0]->geometry->location;
        }

        return $formatted;
    }

    /**
     * Обратное геокодирование
     *
     * @param array $parameters - параметри геокодирования
     * @param string $output - формат вывода 'json' или 'xml'
     *
     */
    public function reverseGeocode($parameters, $output = 'json', $onlyAddress = true)
    {

        //Отправка http запроса к Google Maps API
        $formatted = $this->guzzleRequestToGoogleMapsAPI($parameters, $output);

        //вывод только адреса
        if ($onlyAddress) {
            $formatted = $formatted->results[0]->formatted_address;
        }

        return $formatted;
    }

    /**
     * Получение уникального cache id
     * //todo проблема пересечения разных запросов
     */
    private function makeCacheId($parameters, $output)
    {
        return 'geocoding_' . crc32(serialize($parameters) . $output);
    }

    /**
     * Отправка http запроса к Google Maps API
     *
     * @return JSON
     */
    private function guzzleRequestToGoogleMapsAPI($parameters, $output){

        //создание клиента Guzzle (GuzzleHttp\Client)
        $client = new Client(['base_uri' => "https://maps.googleapis.com/maps/api/geocode/$output"]);

        //установка ключа Google Maps API в параметры
        $parameters['applicationKey'] = $this->applicationKey;

        //создание запроса и получение ответа
        $response = $client->request('GET', "", [
            'query' => $parameters]);

        //todo провереть наличие ответа и обрабатывать ошибки

        //переформатирование в json
        return json_decode($response->getBody()->getContents());
    }

}
