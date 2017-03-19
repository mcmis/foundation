<?php
namespace MCMIS\Foundation;


class DependenciesRepository
{

    protected $app;

    protected $providers = [
        /** TODO: Add dependency service providers */
        'Zizaco\Entrust\EntrustServiceProvider',
        'FarhanWazir\GoogleMaps\GMapsServiceProvider',
    ];

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function register()
    {
        foreach ($this->providers as $provider)
            $this->app->register(new $provider($this->app));
    }

}