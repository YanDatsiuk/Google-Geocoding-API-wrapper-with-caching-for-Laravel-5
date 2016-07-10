<?php

/**
 * Description of Client
 *
 * @author Yan Datsyuk
 */

namespace Datsyuk\GoogleGeocoding\Facades;

use Illuminate\Support\Facades\Facade;

class GeocoderFacade extends Facade{
    
    protected static function getFacadeAccessor() {
        
        return "Geocoder";
    }
}
