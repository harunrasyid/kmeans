<?php
namespace Permengandum\Kmeans\Providers;

use Medoo\Medoo;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MysqlServiceProvider implements ServiceProviderInterface
{
    /**
     * Register mysql service
     *
     * @param Container $app
     */
    public function register(Container $app)
    {
        $app['mysql.service'] = function() use ($app) {
            return new Medoo($app['mysql.config']);
        };
    }

    /**
     * @see Silex\ServiceProviderInterface::boot
     */
     public function boot(Application $app)
     {
     } 
}