<?php

use Psr\Container\ContainerInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container as IlluminateContainer; // Update Illuminate\Container

// Make sure $capsule is correctly required and is an instance of Capsule
$capsule = require __DIR__ . '/../config/database.php';

return function (ContainerInterface $container) use ($capsule) {
    $capsule->setEventDispatcher(new Dispatcher(new IlluminateContainer)); // Update Illuminate\Container
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    $container->set('db', function() use ($capsule) {
        return $capsule;
    });
};
