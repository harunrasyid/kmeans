<?php
/*
 *-------------------------------------------------------------------------
 * Not found routes exception handler
 *-------------------------------------------------------------------------
 */
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
$app->error(function (NotFoundHttpException $e, $code) use ($app) {
    $message = 'Walaah, urlnya ndak ketemu.. Ngapunten, cek lagi mas/mbak..';
    return exceptionResponse($e, 404, $message);
});

/*
 *-------------------------------------------------------------------------
 * App exception handler
 *-------------------------------------------------------------------------
 */
use Permengandum\Kmeans\Exceptions\UnauthorizedException;
$app->error(function (UnauthorizedException $e, $code) use ($app) {
    $redirectTo = $app['url_generator']->generate('login', [
        'message' => $e->getMessage(),
    ]);
    return redirectResponse($redirectTo);
});

use Permengandum\Kmeans\Exceptions\InternalServerErrorException;
$app->error(function (InternalServerErrorException $e, $code) use ($app) {
    $code = $e->getCode();
    $message = 'Nyuwun ngapunten mbak / mas. Kayaknya lagi ada masalah di programnya. Nek panjenengan lagi ndak sibuk, nyuwun tulung dipanggilkan Harun / Andi. Maturnuwuunn..';
    return exceptionResponse($e, $code, $message);
});

use Permengandum\Kmeans\Exceptions\NotFoundException;
$app->error(function (NotFoundException $e, $code) use ($app) {
    $code = $e->getCode();
    $message = 'Urlnya kayaknya salah.. Ngapunten, cek lagi mas/mbak..';
    return exceptionResponse($e, $code, $message);
});


use Permengandum\Kmeans\Exceptions\HttpException;
$app->error(function (HttpException $e, $code) use ($app) {
    $code = $e->getCode();
    $message = $e->getMessage();
    return exceptionResponse($e, $code, $message);
});

/*
 *-------------------------------------------------------------------------
 * Guzzle connect exception
 *-------------------------------------------------------------------------
 */
use GuzzleHttp\Exception\ConnectException;
$app->error(function (ConnectException $e, $code) use ($app) {
    $message = $app['translator']->trans('internal_server_error');
    return exceptionResponse($e, 500, $message);
});

/*
 *-------------------------------------------------------------------------
 * Mongo query exception
 *-------------------------------------------------------------------------
 */
use MongoDB\Exception\Exception as MongoException;
$app->error(function (MongoException $e, $code) use ($app) {
    $message = 'Ngapunten mas, mbak.. Ada masalah di query databasenya. Nyuwun tulung kontak Harun. Bilang aja, query mongonya error gitu. Maturnuwuuun...';
    return exceptionResponse($e, 500, $message);
});