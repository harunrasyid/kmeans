<?php

use Symfony\Component\HttpFoundation\Request;

if (!function_exists('getClientInfoUrl')) {
    /**
     * Get client info for url generator
     * @param  Request $request
     * @return array
     */
    function getAuthQueryString(Request $request)
    {
        $clientId = $request->query->get('client_id');
        $scope = $request->query->get('scope');
        $state = $request->query->get('state');
        $redirectUrl = $request->query->get('redirect_uri');

        $result = [];
        if (!empty($clientId)) {
            $result['client_id'] = $clientId;
        }

        if (!empty($scope)) {
            $result['scope'] = $scope;
        }

        if (!empty($state)) {
            $result['state'] = $state;
        }

        if (!empty($redirectUrl)) {
            $result['redirect_uri'] = $redirectUrl;
        }

        return $result;
    }
}
