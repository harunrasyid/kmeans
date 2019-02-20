<?php 
/*
 *-------------------------------------------------------------------------
 * List of Controllers
 *-------------------------------------------------------------------------
 */
$app['controller.import_data'] = function() use ($app) {
    return new Permengandum\Kmeans\Http\Controllers\ImportData(
        $app['model.import_data'],
        $app['view.general']
    );
};

$app['controller.kmeans'] = function() use ($app) {
    return new Permengandum\Kmeans\Http\Controllers\Kmeans(
        $app['model.kmeans'],
        $app['view.general'],
        $app['url_generator']
    );
};

$app['controller.home'] = function() use ($app) {
    return new Permengandum\Kmeans\Http\Controllers\Home(
        $app['view.general']
    );
};

$app['controller.auth'] = function() use ($app) {
    return new Permengandum\Kmeans\Http\Controllers\Auth(
        $app['model.auth'],
        $app['view.general'],
        $app['url_generator']
    );
};
