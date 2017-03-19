<?php
namespace MCMIS\Foundation;


class ModelsRepository
{

    protected $app;

    protected $models = [];

    protected $registeredModels = [];

    public function __construct($app)
    {
        $this->app = $app;
        $this->withBootstrap();
    }

    protected function withBootstrap()
    {
        (new DependenciesRepository($this->app))->register();
    }

    public function load($models)
    {
        $this->models = $models;
        foreach ($models as $alias => $model) {
            $this->register($alias);
        }
    }

    public function register($model)
    {
        if (isset($this->registeredModels[$model]))
            return $this->registeredModels[$model];

        $key = $model;
        if (!is_null($this->models[$model]) && !empty($this->models[$model]))
            $model = $this->models[$model];
        elseif (class_exists($this->furnishBaseNamespace($model)))
            $model = $this->furnishBaseNamespace($model);
        else return;

        $this->app->bind('model.' . $key, function ($app) use ($model) {
            return new $model;
        });

        return $this->registeredModels[$key] = 'model.' . $key;
    }

    protected function furnishBaseNamespace($model)
    {
        $extracted_name = implode('\\', array_map(function($val){
            return studly_case($val);
        }, explode('.', $model)));
        return 'MCMIS\Foundation\Base\\' . $extracted_name . '\Model';
    }

    public function has($model)
    {
        return isset($this->models[$model]) && !is_null($this->models[$model]);
    }

    public function isRegistered($model)
    {
        return isset($this->registeredModels[$model]);
    }

}