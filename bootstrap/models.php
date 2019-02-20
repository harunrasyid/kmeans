<?php
/*
 *-------------------------------------------------------------------------
 * All Datasources
 *-------------------------------------------------------------------------
 */
$app['model.import_data'] = new Permengandum\Kmeans\Models\ImportData(
    $app['mysql.service']
);

$app['model.kmeans'] = new Permengandum\Kmeans\Models\Kmeans(
    $app['mysql.service']
);

$app['model.auth'] = new Permengandum\Kmeans\Models\Auth(
    $app['mysql.service'],
    $app['libraries.token']
);
