<?php
namespace Permengandum\Kmeans\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Permengandum\Kmeans\Http\Controllers\Controller;
use Permengandum\Kmeans\Views\General as View;

class Home extends Controller
{
    /** View $view */
    private $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * Data awal
     */
    public function index()
    {
        $response = $this->view->render(
            'Home.html'
        );

        return webResponse($response);
    }
}