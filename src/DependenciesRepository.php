<?php
namespace MCMIS\Foundation;

use MCMIS\Contracts\Foundation\Repository;

class DependenciesRepository implements Repository
{

    protected $app;

    protected $providers = [
        /** TODO: Add dependency service providers */
        'Laracasts\Flash\FlashServiceProvider',
        'Intervention\Image\ImageServiceProvider',
        'Zizaco\Entrust\EntrustServiceProvider',
        'Ixudra\Curl\CurlServiceProvider',
        'FarhanWazir\GoogleMaps\GMapsServiceProvider',
        'Khill\Lavacharts\Laravel\LavachartsServiceProvider',
    ];

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function load($repo){
        //
    }

    public function register()
    {
        foreach ($this->providers as $provider)
            $this->app->register(new $provider($this->app));

        return $this;
    }

}