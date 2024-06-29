<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Define app routes
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Welcome to your Slim API");
    return $response;
});

// Group routes under /api
$app->group('/api', function (RouteCollectorProxy $group) {
    $group->get('/users', function (Request $request, Response $response, $args) {
        $users = \App\Models\User::all();
        $response->getBody()->write($users->toJson());
        return $response->withHeader('Content-Type', 'application/json');
    });

    // Add more routes as needed
});

// Run app
$app->run();
