<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DashboardController
{
    private $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        $this->twig = new Environment($loader);
    }

    public function index(): Response
    {
        $content = $this->twig->render('dashboard/index.html.twig', [
            'title' => 'WP-RMM Dashboard'
        ]);
        
        return new Response($content);
    }
}