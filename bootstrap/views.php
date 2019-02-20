<?php 
/*
 *-------------------------------------------------------------------------
 * List of All Views 
 *-------------------------------------------------------------------------
 */
$app['view.general'] = function($app) {
    return new Permengandum\Kmeans\Views\General(
        $app['twig']
    );
};
