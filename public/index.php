<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use App\Controller\DashboardController;

$routes = new RouteCollection();
$routes->add('dashboard', new Route('/', [
    '_controller' => [DashboardController::class, 'index']
]));

$context = new RequestContext();
$context->fromRequest(Request::createFromGlobals());

$matcher = new UrlMatcher($routes, $context);
try {
    $parameters = $matcher->match($context->getPathInfo());
    $controller = $parameters['_controller'][0];
    $method = $parameters['_controller'][1];
    
    $controllerInstance = new $controller();
    $response = $controllerInstance->$method();
    $response->send();
} catch (\Exception $e) {
    header('HTTP/1.1 404 Not Found');
    echo 'Page not found';
}