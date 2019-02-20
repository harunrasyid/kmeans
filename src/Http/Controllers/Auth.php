<?php
namespace Permengandum\Kmeans\Http\Controllers;

use Permengandum\Kmeans\Http\Controllers\Controller;
use Permengandum\Kmeans\Models\Auth as Model;
use Permengandum\Kmeans\Views\General as View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;

class Auth extends Controller
{
    /** @var Model $model */
    private $model;

    /** @var View $view */
    private $view;

    /** @var UrlGenerator $url */
    private $url;

    public function __construct(
        Model $model, 
        View $view,
        UrlGenerator $url
    ) {
        $this->model = $model;
        $this->view = $view;
        $this->url = $url;
    }

    /**
     * Login page
     * 
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $message = $request->query->get('message');
        $response = $this->view->render(
            'Login.html', [
                'message' => $message
            ]
        );

        return webResponse($response);
    }

    /**
     * Login action
     * 
     * @param Request $request
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function login(Request $request)
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $credential = [
            'username' => $username,
            'password' => $password,
        ];
        $result = $this->model->login($credential);
        $url = $this->url->generate('home');
        $cookies = [
            'session' => json_encode($result)
        ];

        return redirectResponse($url, [], $cookies)->send();
    }

    /**
     * Logout action
     * 
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logout()
    {
        $url = $this->url->generate('login');
        $response = redirectResponse($url);
        $response->headers->clearCookie('session');
        return $response;
    }

    /**
     * Check / validate session
     *
     * @param  Request $request
     * @throws Storykota\AuthServer\Exceptions\ForbiddenException
     * @return boolean
     */
    public function check(Request $request)
    {
        $session = $request->cookies->get('session');
        $params = json_decode(decrypt($session), true);

        return $this->model
            ->check($params);
    }

    /**
     * Check / validate session and renew it when it has been expired
     *
     * @param  Request $request
     * @throws Permengandum\Kmeans\Exceptions\ForbiddenException
     * @return array
     */
    public function checkAndRenew(Request $request)
    {
        $session = $request->cookies->get('session');
        $params = json_decode(decrypt($session), true);

        $result = $this->model
            ->checkAndRenew($params);

        return json_encode($result);
    }

}