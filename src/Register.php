<?php

namespace MCMIS\Foundation;

use Illuminate\Contracts\Foundation\Application;

class Register
{

    /**
     * Bootstrap script
     *
     * @param Application $app
     * @return void
     */
    public function bootstrap(Application $app){
        $app->singleton('models', function($app){
            return new ModelsRepository($app);
        });

        /* bind */
        $app->bind('MCMIS\Contracts\Report\ModelFiltration', 'MCMIS\Foundation\Containers\ModelFiltration');
    }

}