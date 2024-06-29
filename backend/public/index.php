<?php

use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Middleware\BodyParsingMiddleware;
use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Set up Eloquent ORM
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => $_ENV['DB_CONNECTION'],
    'host'      => $_ENV['DB_HOST'],
    'database'  => $_ENV['DB_DATABASE'],
    'username'  => $_ENV['DB_USERNAME'],
    'password'  => $_ENV['DB_PASSWORD'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Create App
AppFactory::setContainer(new \DI\Container());
$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// Add CORS middleware
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

// Handle OPTIONS requests
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withStatus(204);
});

// Add error middleware with logging
$logger = new Logger('api_logger');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::DEBUG));

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler(function ($request, $exception, $displayErrorDetails, $logErrors, $logErrorDetails) use ($logger) {
    $logger->error($exception->getMessage());
    $response = new \Slim\Psr7\Response();
    $response->getBody()->write(json_encode(['error' => $exception->getMessage()]));
    return $response->withHeader('Content-Type', 'application/json')->withHeader('Access-Control-Allow-Origin', '*');
});

// Define routes
$app->get('/', function ($request, $response, $args) {
    $response->getBody()->write("Welcome to your Slim API");
    return $response->withHeader('Access-Control-Allow-Origin', '*');
});

$app->group('/api', function ($group) use ($logger) {
    $group->get('/users', function ($request, $response, $args) use ($logger) {
        $logger->info("Fetching users");
        $users = \App\Models\User::all();
        $response->getBody()->write($users->toJson());
        return $response->withHeader('Content-Type', 'application/json')->withHeader('Access-Control-Allow-Origin', '*');
    });

    $group->post('/users', function ($request, $response, $args) use ($logger) {
        try {
            $logger->info("Saving user");
            $data = $request->getParsedBody();
            $logger->info("User data: " . json_encode($data));
            if (is_null($data) || !isset($data['name']) || !isset($data['email'])) {
                throw new \Exception("Invalid request data: " . json_encode($data));
            }
            $user = new \App\Models\User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->save();
            $logger->info("User saved: " . $user->toJson());
            $response->getBody()->write($user->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withHeader('Access-Control-Allow-Origin', '*');
        } catch (\Exception $e) {
            $logger->error("Error saving user: " . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json')->withHeader('Access-Control-Allow-Origin', '*');
        }
    });

    $group->put('/users/{id}', function ($request, $response, $args) use ($logger) {
        try {
            $id = $args['id'];
            $logger->info("Updating user with ID: " . $id);
            $data = $request->getParsedBody();
            $logger->info("Update data: " . json_encode($data));
            $user = \App\Models\User::find($id);
            if (!$user) {
                $response->getBody()->write(json_encode(['error' => 'User not found']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json')->withHeader('Access-Control-Allow-Origin', '*');
            }
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->save();
            $logger->info("User updated: " . $user->toJson());
            $response->getBody()->write($user->toJson());
            return $response->withHeader('Content-Type', 'application/json')->withHeader('Access-Control-Allow-Origin', '*');
        } catch (\Exception $e) {
            $logger->error("Error updating user: " . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json')->withHeader('Access-Control-Allow-Origin', '*');
        }
    });

    $group->delete('/users/{id}', function ($request, $response, $args) use ($logger) {
        try {
            $id = $args['id'];
            $logger->info("Deleting user with ID: " . $id);
            $user = \App\Models\User::find($id);
            if (!$user) {
                $response->getBody()->write(json_encode(['error' => 'User not found']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json')->withHeader('Access-Control-Allow-Origin', '*');
            }
            $user->delete();
            $logger->info("User deleted with ID: " . $id);
            $response->getBody()->write(json_encode(['message' => 'User deleted successfully']));
            return $response->withHeader('Content-Type', 'application/json')->withHeader('Access-Control-Allow-Origin', '*');
        } catch (\Exception $e) {
            $logger->error("Error deleting user: " . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json')->withHeader('Access-Control-Allow-Origin', '*');
        }
    });
});

$app->run();
