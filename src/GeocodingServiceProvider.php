<?php

namespace Datsyuk\GoogleGeocoding;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class GeocodingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        AliasLoader::getInstance()->alias('Geocoder', 'Datsyuk\GoogleGeocoding\Facades\GeocoderFacade');

        //публикация файла конфигураций
        $this->publishes([
            __DIR__ . '/config/geocoder.php' => config_path('geocoder.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return Geocoder
     */
    public function register()
    {
        $this->app['Geocoder'] = $this->app->share(function ($app) {

            $config = array();
            $config['applicationKey'] = Config::get('geocoder.applicationKey');

            return new Geocoder($config);
        });
    }
}
