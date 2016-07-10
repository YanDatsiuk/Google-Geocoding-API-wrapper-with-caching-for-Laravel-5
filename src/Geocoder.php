<?php

namespace Datsyuk\GoogleGeocoding;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Psy\Util\Json;

class Geocoder
{

    /**
     * ���� � Google Maps API
     */
    protected $applicationKey;


    /**
     * �������� ����������
     */
    public function __construct($config)
    {
        $this->applicationKey = $config['applicationKey'];
    }


    /**
     *
     * @param array $parameters - ��������� ��������������
     * @param string $output - ������ ������ 'json' ��� 'xml'
     *
     * @return string
     */
    public function geocode($parameters, $output = 'json', $onlyLocation = true)
    {
        //��������� ����������� cache id
        $cacheId = $this->makeCacheId($parameters, $output);

        //�������� ����
        if (Cache::has($cacheId)) {

            $formatted = Cache::get($cacheId);
        }else{

            //�������� http ������� � Google Maps API
            $formatted = $this->guzzleRequestToGoogleMapsAPI($parameters, $output);

            //���������� ������ � ���
            Cache::forever($cacheId, $formatted);
        }

        //����� ������ �������
        if ($onlyLocation) {
            $formatted = $formatted->results[0]->geometry->location;
        }

        return $formatted;
    }

    /**
     * �������� ��������������
     *
     * @param array $parameters - ��������� ��������������
     * @param string $output - ������ ������ 'json' ��� 'xml'
     *
     */
    public function reverseGeocode($parameters, $output = 'json', $onlyAddress = true)
    {

        //�������� http ������� � Google Maps API
        $formatted = $this->guzzleRequestToGoogleMapsAPI($parameters, $output);

        //����� ������ ������
        if ($onlyAddress) {
            $formatted = $formatted->results[0]->formatted_address;
        }

        return $formatted;
    }

    /**
     * ��������� ����������� cache id
     * //todo �������� ����������� ������ ��������
     */
    private function makeCacheId($parameters, $output)
    {
        return 'geocoding_' . crc32(serialize($parameters) . $output);
    }

    /**
     * �������� http ������� � Google Maps API
     *
     * @return JSON
     */
    private function guzzleRequestToGoogleMapsAPI($parameters, $output){

        //�������� ������� Guzzle (GuzzleHttp\Client)
        $client = new Client(['base_uri' => "https://maps.googleapis.com/maps/api/geocode/$output"]);

        //��������� ����� Google Maps API � ���������
        $parameters['applicationKey'] = $this->applicationKey;

        //�������� ������� � ��������� ������
        $response = $client->request('GET', "", [
            'query' => $parameters]);

        //todo ��������� ������� ������ � ������������ ������

        //������������������ � json
        return json_decode($response->getBody()->getContents());
    }

}
