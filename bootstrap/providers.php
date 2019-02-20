<?php
/*
 *-------------------------------------------------------------------------
 * Silex Built in Services
 *-------------------------------------------------------------------------
 */
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\RoutingServiceProvider());

/*
 *-------------------------------------------------------------------------
 * Debugger Service
 *-------------------------------------------------------------------------
 */
use WhoopsSilex\WhoopsServiceProvider;

$app['debug'] = config_get('env.debug');
if ($app['debug']) {
    $app->register(new WhoopsServiceProvider());
}

/*
 *-------------------------------------------------------------------------
 * Logger Service
 *-------------------------------------------------------------------------
 */
 use Silex\Provider\MonologServiceProvider;
 $app->register(new MonologServiceProvider(), array(
     'monolog.logfile'   => __DIR__ . '/../log/' . config_env() . '.log',
     'monolog.name'      => 'convalidator',
     'monolog.level'     => 'ERROR'
 ));
 
 /*
  *-------------------------------------------------------------------------
  * Template Service
  *-------------------------------------------------------------------------
  */
$cacheSetting = config_get('env.debug') ? false : __DIR__ . '/../cache';
$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__.'/../src/Templates',
  'twig.options'    => array(
      'cache' => $cacheSetting,
  ),
));

$app['twig']->addFilter(
  new Twig_SimpleFilter('config', function($key) {
      return config_get($key);
  })
);

$app['twig']->addFilter(
    new Twig_SimpleFilter('assets', function($name) {
        return assets_get($name);
    })
);

/*
 *-------------------------------------------------------------------------
 * Mysql Service
 *-------------------------------------------------------------------------
 */
use Permengandum\Kmeans\Providers\MysqlServiceProvider;

$app['mysql.config'] = config_get('mysql'); 
$app->register(new MysqlServiceProvider());


