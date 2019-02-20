<?php
$api = $app['controllers_factory'];

// Login
$app->post('/login', "controller.auth:login")
    ->before($hasNotLogin);
$app->get('/login', "controller.auth:index")
    ->before($hasNotLogin)
    ->bind('login');
$app->get('/logout', "controller.auth:logout")
    ->bind('logout');

// Import
$app->get('/import', "controller.import_data:index")
    ->before($hasLogin)
    ->bind('import');
$app->post('/import', "controller.import_data:import")
    ->before($hasLogin);


//kmeans
$kmeans = $app['controllers_factory'];
$kmeans->before($hasLogin);
$kmeans->get('/download/{id}', "controller.kmeans:writeToFile")->bind('download');
$kmeans->get('/result/{id}/{selectedCluster}', "controller.kmeans:getResult")->bind('result');
$kmeans->get('/iteration/{id}/{iteration}', "controller.kmeans:iterate")->bind('iteration');
$kmeans->post('/initiate', "controller.kmeans:initiate")->bind('initiate');
$kmeans->get('/prerun', "controller.kmeans:prerun")->bind('prerun');
$kmeans->get('/', "controller.kmeans:index")->bind('kmeans');
$app->mount('/kmeans', $kmeans);

$app->get('/', "controller.home:index")
    ->before($hasLogin)
    ->bind('home');