<?php
namespace Permengandum\Kmeans\Views;

use Symfony\Component\HttpFoundation\Response;

class General
{
    /** @var Twig Environment $twig */
    private $twig;

    public function __construct($twig)
    {
        $this->twig = $twig;
    }

    /**
     * Generate detail view
     *
     * @param $view
     * @param array $data
     * @return string
     */
    public function render($view, $data = [])
    {
        return $this->twig->render($view, $data);
    }
}
