<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Permengandum\Kmeans\Exceptions;

/*
 *-------------------------------------------------------------------------
 * Authentication Middleware
 *-------------------------------------------------------------------------
 */
$hasNotLogin = function (Request $request, Application $app) {
    $session = $request->cookies->get('session');
    
    if (!empty($session) && $app['controller.auth']->check($request)) {
        return redirectResponse(
            $app['url_generator']->generate(
                'home',
                getAuthQueryString($request)
            )
        );
    }
};

$hasLogin = function (Request $request, Application $app) {
    $session = $request->cookies->get('session');
    
    if (empty($session)) {
        return redirectResponse(
            $app['url_generator']->generate(
                'login',
                getAuthQueryString($request)
            )
        );
    }

    try {
        $session = $app['controller.auth']->checkAndRenew($request);
    } catch (Exceptions\ForbiddenException $e) {
        return redirectResponse(
            $app['url_generator']->generate(
                'login',
                getAuthQueryString($request)
            )
        );
    }

    $request->cookies->set('session', $session);
};
